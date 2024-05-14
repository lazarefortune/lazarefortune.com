<?php

namespace App\Http\Admin\Controller;


use App\Domain\Course\Entity\Course;
use App\Http\Admin\Data\Crud\CourseCrudData;
use App\Infrastructure\Youtube\YoutubeScopes;
use App\Infrastructure\Youtube\YoutubeUploaderService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\UploadHandler;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/tutoriels', name: 'course_')]
class CourseController extends CrudController
{
    private const UPLOAD_SESSION_KEY = 'course_upload_id';

    protected string $templatePath = 'course';
    protected string $menuItem = 'course';
    protected string $entity = Course::class;
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'app_admin_course';
    protected array $events = [];

    #[Route(path: '/', name: 'index')]
    public function index(Request $request): Response
    {
        $this->paginator->allowSort('row.id', 'row.online');
        $query = $this->getRepository()
            ->createQueryBuilder('row')
            ->addSelect('tu', 't')
            ->leftJoin('row.technologyUsages', 'tu')
            ->leftJoin('tu.technology', 't')
            ->orderBy('row.createdAt', 'DESC')
            ->setMaxResults(10)
        ;
        if ($request->query->has('technology')) {
            $query
                ->andWhere('t.slug = :technology')
                ->setParameter('technology', $request->query->get('technology'));
        }

        return $this->crudIndex($query);
    }

    #[Route(path: '/nouveau', name: 'new', methods: ['POST', 'GET'])]
    public function new(): Response
    {
        $entity = (new Course())->setAuthor($this->getUser());
        $data = new CourseCrudData($entity);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(
        Request          $request,
        Course           $course,
        UploadHandler    $uploaderHelper,
        SessionInterface $session
    ): Response {
        $data = (new CourseCrudData($course, $uploaderHelper))->setEntityManager($this->em);
        $response = $this->crudEdit($data);
        if ($request->request->get('upload')) {
            $session->set(self::UPLOAD_SESSION_KEY, $course->getId());

            return $this->redirectToRoute('app_admin_course_upload');
        }

        if ($request->request->get('updateDuration')) {
            $session->set(self::UPLOAD_SESSION_KEY, $course->getId());

            return $this->redirectToRoute('app_admin_course_update_duration');
        }

        return $response;
    }

    #[Route(path: '/{id<\d+>}', methods: ['DELETE'])]
    public function delete(Course $course, EventDispatcherInterface $dispatcher): Response
    {
        $course->setOnline(false);
        $course->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $this->addFlash('success', 'Le tutoriel a bien été mis hors ligne');

        if ($this->events['delete'] ?? null) {
            $dispatcher->dispatch(new $this->events['delete']($course));
        }

        return $this->redirectBack(($this->routePrefix.'_index'));
    }

    #[Route( path: '/upload', name: 'upload', methods: ['GET'] )]
    public function upload(
        Request             $request,
        SessionInterface    $session,
        \Google_Client      $googleClient,
        YoutubeUploaderService $uploader,
    ): Response {
        // Si on n'a pas d'id dans la session, on redirige
        $courseId = $session->get(self::UPLOAD_SESSION_KEY);
        if (null === $courseId) {
            $this->addFlash('danger', "Impossible d'uploader la vidéo, id manquante dans la session");

            return $this->redirectToRoute('app_admin_course_index');
        }

        // On génère récupère le code d'auth
        $redirectUri = $this->generateUrl('app_admin_course_upload', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $code = $request->get('code');

        $googleClient->setRedirectUri($redirectUri);
        if (null === $code) {
            return $this->redirect($googleClient->createAuthUrl(YoutubeScopes::UPLOAD));
        }

        $accessToken = $googleClient->fetchAccessTokenWithAuthCode($code);

        if (isset($accessToken['error'])) {
            return $this->redirect($googleClient->createAuthUrl(YoutubeScopes::UPLOAD));
        }

        $videoId = $uploader->upload($courseId, $accessToken);

        $this->addFlash('success', "La vidéo est en cours d'envoi sur Youtube");
        $session->remove(self::UPLOAD_SESSION_KEY);

        return $this->redirectToRoute('app_admin_course_edit', ['id' => $courseId]);
    }

    #[Route( path: '/update-duration', name: 'update_duration', methods: ['GET'] )]
    public function updateDuration(
        Request             $request,
        SessionInterface    $session,
        \Google_Client      $googleClient,
        YoutubeUploaderService $uploader,
    ) : Response
    {
//        dd('update duration');
        // Si on n'a pas d'id dans la session, on redirige
        $courseId = $session->get(self::UPLOAD_SESSION_KEY);
        if (null === $courseId) {
            $this->addFlash('danger', "Impossible d'uploader la vidéo, id manquante dans la session");

            return $this->redirectToRoute('app_admin_course_index');
        }

        // On génère récupère le code d'auth
        $redirectUri = $this->generateUrl('app_admin_course_update_duration', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $code = $request->get('code');

        $googleClient->setRedirectUri($redirectUri);
        if (null === $code) {
            return $this->redirect($googleClient->createAuthUrl(YoutubeScopes::UPLOAD));
        }

        $accessToken = $googleClient->fetchAccessTokenWithAuthCode($code);

        if (isset($accessToken['error'])) {
            return $this->redirect($googleClient->createAuthUrl(YoutubeScopes::UPLOAD));
        }

        $duration = $uploader->getVideoDuration($courseId, $accessToken);
//        dd($duration);

        $course = $this->em->getRepository(Course::class)->find($courseId);
        $course->setDuration($duration);

        $this->em->flush();

        return $this->redirectToRoute('app_admin_course_edit', ['id' => $courseId]);
    }
}