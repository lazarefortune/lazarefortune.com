<?php

namespace App\Http\Studio\Controller;

use App\Domain\Application\Event\ContentCreatedEvent;
use App\Domain\Application\Event\ContentDeletedEvent;
use App\Domain\Application\Event\ContentUpdatedEvent;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Http\Admin\Controller\CrudController;
use App\Http\Admin\Data\Crud\CourseCrudData;
use App\Http\Admin\Data\Crud\CourseNewCrudData;
use App\Http\Admin\Form\Course\CourseEditForm;
use App\Http\Security\ContentVoter;
use App\Infrastructure\Youtube\YoutubeScopes;
use App\Infrastructure\Youtube\YoutubeService;
use App\Infrastructure\Youtube\YoutubeUploaderService;
use Google_Service_Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\UploadHandler;
use Vich\UploaderBundle\Storage\StorageInterface;

#[IsGranted('ROLE_AUTHOR')]
#[Route(path: '/videos', name: 'course_')]
class CourseController extends CrudController
{
    private const SESSION_COURSE_ID = 'session_course_id';

    protected string $templateDirectory = 'pages/studio';
    protected string $templatePath = 'course';
    protected string $menuItem = 'course';
    protected string $entity = Course::class;
    protected bool   $indexOnSave = false;
    protected string $routePrefix = 'studio_course';
    protected array $events = [
        'update' => ContentUpdatedEvent::class,
        'delete' => ContentDeletedEvent::class,
        'create' => ContentCreatedEvent::class,
    ];

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
        $data = new CourseNewCrudData($entity);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function edit(
        Request $request,
        Course $course,
        EventDispatcherInterface $dispatcher,
        SessionInterface $session
    ): Response {
        $this->denyAccessUnlessGranted(ContentVoter::EDIT, $course);

        if ($request->request->get('fetchVideoDuration')) {
            $session->set(self::SESSION_COURSE_ID, $course->getId());
            return $this->redirectToRoute('studio_course_update_duration');
        }

        if ($request->request->get('uploadVideoDetails')) {
            $session->set(self::SESSION_COURSE_ID, $course->getId());
            return $this->redirectToRoute('studio_course_upload');
        }

        $oldCourse = clone $course;
        $form = $this->createForm(CourseEditForm::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newCourse = $form->getData();

            $this->em->flush();
            $this->addFlash('success', 'La vidéo a bien été modifié');

            $dispatcher->dispatch(new ContentUpdatedEvent($oldCourse, $newCourse));

            if ($request->request->get('synchronize')) {
                $session->set(self::SESSION_COURSE_ID, $course->getId());
                if (!$course->getYoutubeId()) {
                    $this->addFlash('danger', 'Veuillez d\'abord téléverser la vidéo sur Youtube ou saisir un Youtube ID');
                    return $this->redirectToRoute('studio_course_edit', ['id' => $course->getId()]);
                }
                return $this->redirectToRoute('studio_course_upload');
            }

            return $this->redirectToRoute('studio_course_edit', ['id' => $course->getId()]);
        }

        return $this->render("{$this->templateDirectory}/{$this->templatePath}/edit.html.twig", [
            'form' => $form->createView(),
            'entity' => $course,
        ]);
    }

    #[Route( path: '/{id<\d+>}', name: 'delete', methods: ['DELETE'] )]
    #[IsGranted('ROLE_AUTHOR')]
    public function delete(Course $course): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::DELETE, $course);

        return $this->crudAjaxDelete($course);
    }

    /**
     * Trouve tous les cours qui ont des sources manquantes.
     */
    #[Route( path: '/missing', name: 'missing', methods: ['GET'] )]
    public function missing(
        CourseRepository $courseRepository,
        StorageInterface $storage,
    ): Response {
        $rows = $this->paginator->paginate($courseRepository
            ->queryAll()
            ->where('c.source IS NOT NULL')
            ->setMaxResults(2500)
            ->getQuery()
        );

        $filteredRows = array_filter(
            [...$rows->getItems()],
            fn (Course $c) => !file_exists($storage->resolvePath($c, 'sourceFile') ?? '')
        );

        return $this->render("{$this->templateDirectory}/{$this->templatePath}/missing.html.twig", [
            'rows' => $rows,
            'filtered_rows' => $filteredRows,
            'storage' => $storage,
            'prefix' => $this->routePrefix,
        ]);
    }

    #[Route( path: '/{id<\d+>}', name: 'put_offline', methods: ['POST'] )]
    #[IsGranted('ROLE_AUTHOR')]
    public function putOffline(Course $course, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted(ContentVoter::DELETE, $course);

        $course->setOnline(false);
        $course->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $this->addFlash('success', 'Le tutoriel a bien été mis hors ligne');

        return $this->redirectBack(($this->routePrefix . '_index'));
    }


    /**
     * Lance l'upload (ou la mise à jour) d'une video sur youtube.
     */
    #[Route( path: '/upload', name: 'upload', methods: ['GET'] )]
    public function upload(
        SessionInterface $session,
        MessageBusInterface $messageBus,
        YoutubeService $youtubeService
    ): Response {
        // Si on n'a pas d'id dans la session, on redirige
        $courseId = $session->get(self::SESSION_COURSE_ID);
        $session->remove(self::SESSION_COURSE_ID);
        if (null === $courseId) {
            $this->addFlash('danger', "Impossible d'uploader la vidéo, id manquante dans la session");

            return $this->redirectToRoute('studio_course_index');
        }

        $youtubeSetting = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);
        if (!$youtubeSetting || !$youtubeSetting->getAccessToken()) {
            $this->addFlash('danger', "Aucun compte YouTube n'est lié, veuillez contactez un administrateur.");
            return $this->redirectToRoute('studio_course_index');
        }

        $youtubeService->authenticateGoogleClient($youtubeSetting);

        $tokenArray = json_decode($youtubeSetting->getAccessToken(), true);

        $this->dispatchMethod(
            $messageBus,
            YoutubeUploaderService::class,
            'upload',
            [(int) $courseId, $tokenArray]
        );

        $this->addFlash('success', "La vidéo est en cours d'envoi sur Youtube");

        return $this->redirectToRoute('studio_course_edit', ['id' => $courseId]);
    }

    #[Route(path: '/upload/custom', name: 'upload_custom', methods: ['GET'])]
    #[IsGranted('ROLE_AUTHOR')]
    public function uploadCustom(
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
            return $this->redirectToRoute('studio_course_edit', ['id' => $courseId]);
        }

        $session->remove(self::SESSION_COURSE_ID);
        return $this->redirectToRoute('studio_course_edit', ['id' => $courseId]);
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
        $session->remove(self::SESSION_COURSE_ID);
        if (null === $courseId) {
            $this->addFlash('danger', "Id manquante dans la session");
            return $this->redirectToRoute('admin_course_index');
        }

        $settings = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);
        if (!$settings || !$settings->getAccessToken()) {
            $this->addFlash('danger', "Aucun compte YouTube n'est lié pour récupérer la durée de la vidéo.");
            return $this->redirectToRoute('admin_youtube_config_index');
        }

        $redirectUri = $this->generateUrl('studio_course_update_duration', [], UrlGeneratorInterface::ABSOLUTE_URL);
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
            return $this->redirectToRoute('studio_course_edit', ['id' => $courseId]);
        } catch (\Exception $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirectToRoute('studio_course_edit', ['id' => $courseId]);
        }
    }

    #[Route('/youtube/metadata', name: 'youtube_metadata', methods: ['GET'])]
    public function getYoutubeMetadata(Request $request, YoutubeService $youtubeService): JsonResponse
    {
        // 1. Récupérer le courseId si besoin
        $courseId = $request->query->get('courseId');

        if (!$courseId) {
            return $this->json(['error' => 'courseId is required'], 400);
        }

        $course = $this->em->getRepository(Course::class)->find($courseId);

        if (!$course) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        $youtubeSetting = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);

        if (!$youtubeSetting) {
            return $this->json(['error' => 'No YoutubeSetting found'], 404);
        }

        if (!$youtubeSetting->getAccessToken()) {
            return $this->json(['error' => 'No access token found'], 404);
        }

        if ($course->getYoutubeId()) {
            return $this->json(['error' => 'This course already has a youtubeId'], 400);
        }

        $privacy = 'public';
        $publishedAt = $course->getPublishedAt();
        if ($course->isPremium()) {
            $privacy = 'unlisted';
        } elseif ($course->getPublishedAt() && $course->getPublishedAt() > new \DateTime()) {
            $privacy = 'private';
        } else {
            $privacy = 'public';
        }

        $metadata = [
            'title' => $course->getTitle(),
            'description' => $course->getContent(),
            'privacy' => $privacy,
            'publishedAt' => $publishedAt ? $publishedAt->format('Y-m-d\TH:i:s\Z') : null,
        ];

        $youtubeService->authenticateGoogleClient($youtubeSetting);

        $tokenArr = json_decode($youtubeSetting->getAccessToken(), true);
        $accessToken = $tokenArr['access_token'] ?? null;

        return $this->json([
            'accessToken' => $accessToken,
            'metadata' => $metadata
        ]);
    }

    #[Route('/youtube/save-youtube-id', name: 'save_youtube_id', methods: ['POST'])]
    public function saveYoutubeId(Request $request, MessageBusInterface $messageBus, YoutubeService $youtubeService) : JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $courseId = $data['courseId'] ?? null;
        $youtubeId = $data['youtubeId'] ?? null;

        if (!$courseId || !$youtubeId) {
            return $this->json(['error' => 'courseId and youtubeId are required'], 400);
        }

        $course = $this->em->getRepository(Course::class)->find($courseId);

        if (!$course) {
            return $this->json(['error' => 'Course not found'], 404);
        }

        $course->setYoutubeId($youtubeId);
        $this->em->flush();

        $youtubeSetting = $this->em->getRepository(YoutubeSetting::class)->findOneBy([]);

        if (!$youtubeSetting) {
            return $this->json(['error' => 'No YoutubeSetting found'], 404);
        }

        if (!$youtubeSetting->getAccessToken()) {
            return $this->json(['error' => 'No access token found'], 404);
        }

        $youtubeService->authenticateGoogleClient($youtubeSetting);

        $tokenArray = json_decode($youtubeSetting->getAccessToken(), true);

        $this->dispatchMethod(
            $messageBus,
            YoutubeUploaderService::class,
            'upload',
            [(int) $courseId, $tokenArray]
        );

        return $this->json(['message' => 'YoutubeId saved']);
    }

    /*
    * Upload resumable
    */
    #[Route('/init-upload', name: 'init_upload', methods: ['GET', 'POST'])]
    public function initUpload(Request $request, YoutubeService $youtubeService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $courseId = $data['courseId'] ?? null;

        if (!$courseId) {
            return $this->json(['error' => 'courseId is required'], 400);
        }

        try {
            $uploadUrl = $youtubeService->initiateResumableUpload($courseId);
            return $this->json(['uploadUrl' => $uploadUrl]);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    // --- PRIVATE --- //
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
