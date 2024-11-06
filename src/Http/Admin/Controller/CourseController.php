<?php

namespace App\Http\Admin\Controller;

use App\Domain\Course\Entity\Course;
use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Http\Admin\Data\Crud\CourseCrudData;
use App\Http\Requirements;
use App\Http\Security\ContentVoter;
use App\Infrastructure\Youtube\YoutubeScopes;
use App\Infrastructure\Youtube\YoutubeService;
use Google_Service_Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/videos', name: 'course_')]
class CourseController extends CrudController
{
    private const SESSION_COURSE_ID = 'session_course_id';

    protected string $templatePath = 'course';
    protected string $menuItem = 'course';
    protected string $entity = Course::class;
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'admin_course';
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
            ->setMaxResults(10);

        if ($request->query->has('technology')) {
            $query->andWhere('t.slug = :technology')
                ->setParameter('technology', $request->query->get('technology'));
        }

        return $this->crudIndex($query);
    }

    #[Route(path: '/nouveau', name: 'new', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function new(): Response
    {
        $entity = (new Course())->setAuthor($this->getUser());
        $data = new CourseCrudData($entity);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit(
        Request $request,
        Course $course,
        UploadHandler $uploaderHelper,
        SessionInterface $session
    ): Response {
        $this->denyAccessUnlessGranted(ContentVoter::EDIT, $course);

        $data = (new CourseCrudData($course, $uploaderHelper))->setEntityManager($this->em);
        $response = $this->crudEdit($data);

        if ($request->request->get('uploadVideoDetails')) {
            $session->set(self::SESSION_COURSE_ID, $course->getId());
            return $this->redirectToRoute('admin_course_upload');
        }

        if ($request->request->get('fetchVideoDuration')) {
            $session->set(self::SESSION_COURSE_ID, $course->getId());
            return $this->redirectToRoute('admin_course_update_duration');
        }

        return $response;
    }

    #[Route(path: '/{id<\d+>}', methods: ['DELETE'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function delete(Course $course, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::DELETE, $course);

        $course->setOnline(false);
        $course->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $this->addFlash('success', 'Le tutoriel a bien été mis hors ligne');

        if ($this->events['delete'] ?? null) {
            $dispatcher->dispatch(new $this->events['delete']($course));
        }

        return $this->redirectBack(($this->routePrefix . '_index'));
    }

    #[Route(path: '/upload', name: 'upload', methods: ['GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function upload(
        Request $request,
        SessionInterface $session,
        \Google_Client $googleClient,
        YoutubeService $uploader
    ): Response {
        $courseId = $session->get(self::SESSION_COURSE_ID);
        if (null === $courseId) {
            $this->addFlash('danger', "Impossible d'uploader la vidéo, id manquante dans la session");
            return $this->redirectToRoute('admin_course_index');
        }

        $settings = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);
        if (!$settings || !$settings->getAccessToken()) {
            $this->addFlash('danger', "Aucun compte YouTube n'est lié pour effectuer l'upload.");
            return $this->redirectToRoute('admin_youtube_config_index');
        }

        $redirectUri = $this->generateUrl('admin_course_upload', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $googleClient->setRedirectUri($redirectUri);

        $accessToken = $this->getAccessToken($googleClient, $settings, $request);
        if (!$accessToken) {
            return $this->redirect($googleClient->createAuthUrl(YoutubeScopes::UPLOAD));
        }

        try {
            $videoId = $uploader->uploadVideo($courseId, $accessToken);
            $this->addFlash('success', "La vidéo est en cours d'envoi sur YouTube");
        } catch (Google_Service_Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('admin_course_edit', ['id' => $courseId]);
        }

        $session->remove(self::SESSION_COURSE_ID);
        return $this->redirectToRoute('admin_course_edit', ['id' => $courseId]);
    }

    #[Route(path: '/update-duration', name: 'update_duration', methods: ['GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function updateDuration(
        Request $request,
        SessionInterface $session,
        \Google_Client $googleClient,
        YoutubeService $uploader
    ): Response {
        $courseId = $session->get(self::SESSION_COURSE_ID);
        if (null === $courseId) {
            $this->addFlash('danger', "Id manquante dans la session");
            return $this->redirectToRoute('admin_course_index');
        }

        $settings = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);
        if (!$settings || !$settings->getAccessToken()) {
            $this->addFlash('danger', "Aucun compte YouTube n'est lié pour récupérer la durée de la vidéo.");
            return $this->redirectToRoute('admin_youtube_config_index');
        }

        $redirectUri = $this->generateUrl('admin_course_update_duration', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $googleClient->setRedirectUri($redirectUri);

        $accessToken = $this->getAccessToken($googleClient, $settings, $request);
        if (!$accessToken) {
            return $this->redirect($googleClient->createAuthUrl(YoutubeScopes::UPLOAD));
        }

        try {
            $duration = $uploader->getVideoDuration($courseId, $accessToken);
            $course = $this->em->getRepository(Course::class)->find($courseId);
            $course->setDuration($duration);
            $this->em->flush();

            $this->addFlash('success', "La durée de la vidéo a bien été mise à jour");
            return $this->redirectToRoute('admin_course_edit', ['id' => $courseId]);
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('admin_course_edit', ['id' => $courseId]);
        }
    }

    private function getAccessToken(\Google_Client $googleClient, YoutubeSetting $settings, Request $request): ?array
    {
        if ($settings->getAccessToken()) {
            $googleClient->setAccessToken($settings->getAccessToken());
            if ($googleClient->isAccessTokenExpired() && $settings->getRefreshToken()) {
                return $this->fetchAccessTokenWithRefreshToken($googleClient, $settings);
            }
            return json_decode($settings->getAccessToken(), true);
        }

        if ($code = $request->get('code')) {
            return $this->fetchAccessTokenWithCode($googleClient, $settings, $code);
        }

        return null;
    }

    private function fetchAccessTokenWithCode(\Google_Client $googleClient, YoutubeSetting $settings, string $code): ?array
    {
        $accessToken = $googleClient->fetchAccessTokenWithAuthCode($code);
        if (!isset($accessToken['error'])) {
            $this->saveAccessToken($accessToken, $settings);
            return $accessToken;
        }
        return null;
    }

    private function fetchAccessTokenWithRefreshToken(\Google_Client $googleClient, YoutubeSetting $settings): ?array
    {
        if ($settings->getRefreshToken()) {
            $googleClient->refreshToken($settings->getRefreshToken());
            $accessToken = $googleClient->getAccessToken();
            $this->saveAccessToken($accessToken, $settings);
            return $accessToken;
        }
        return null;
    }

    private function saveAccessToken(array $accessTokenData, YoutubeSetting $settings): void
    {
        $settings->setAccessToken(json_encode($accessTokenData));
        if (isset($accessTokenData['refresh_token'])) {
            $settings->setRefreshToken($accessTokenData['refresh_token']);
        }
        $this->em->flush();
    }
}
