<?php

namespace App\Http\Admin\Controller;

use App\Domain\Application\Form\EmailTestForm;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Course\CourseService;
use App\Domain\Course\Service\FormationService;
use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Domain\Youtube\Exception\NotFoundYoutubeAccount;
use App\Domain\Youtube\Repository\YoutubeSettingRepository;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Mailing\MailService;
use App\Infrastructure\Youtube\YoutubeService;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Infrastructure\Queue\FailedJobsService;
use App\Infrastructure\Queue\ScheduledJobsService;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

#[IsGranted( 'ROLE_ADMIN' )]
class HomeController extends AbstractController
{

    public function __construct(
        private readonly CourseService    $courseService,
        private readonly FormationService $formationService,
        private readonly YoutubeService   $youtubeService,
        private readonly UserRepository   $userRepository,
        private readonly FailedJobsService $failedJobsService,
        private readonly ScheduledJobsService $scheduledJobsService,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route( '/', name: 'home', methods: ['GET', 'POST'] )]
    public function home( Request $request, MailService $mailService, CacheItemPoolInterface $cache , YoutubeSettingRepository $youtubeSettingRepository) : Response
    {
        // if the user is only author
        if ( in_array('ROLE_AUTHOR', $this->getUser()->getRoles()) ) {
            return $this->authorHome();
            #$this->redirectToRoute('admin_home_author');
        }

        $youtubeSubscribersCount = $cache->get('admin.youtube-subscribers-count', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            try {
                return $this->youtubeService->getSubscribersCount();
            } catch (NotFoundYoutubeAccount $exception) {
                $this->addFlash('error', $exception->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', "Impossible de charger les données depuis YouTube");
            }
            return 0;
        });

        $countUsers = $cache->get( 'admin.users-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->userRepository->countUsers();
        } );

        $countOnlineCourses = $cache->get( 'admin.courses-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->courseService->countOnlineCourses();
        } );

        $countOnlineFormations = $cache->get( 'admin.formations-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->formationService->countOnlineFormations();
        } );

        $dailyUsersLast30Days = $this->userRepository->countDailyUsersLast30Days();
        $monthlyUsersLast24Months = $this->userRepository->countMonthlyUsersLast24Months();

        $formTestEmail = $this->createForm( EmailTestForm::class );
        $formTestEmail->handleRequest( $request );

        if ( $formTestEmail->isSubmitted() && $formTestEmail->isValid() ) {
            $data = $formTestEmail->getData();

            $emailTo = $data['email'];

            try {
                $data = $formTestEmail->getData();
                $emailTo = $data['email'];

                $email = $mailService->createEmail('mails/test.twig', [])
                    ->to($emailTo)
                    ->subject('Email de test');

                $mailService->send($email);

                $this->addFlash('success', 'Message en cours d\'envoi');
            } catch ( LoaderError|RuntimeError|SyntaxError $e ) {
                $this->addFlash( 'error', $e->getMessage() );
            }

        }

        /** @var YoutubeSetting $youtubeAccount */
        $youtubeAccount = $youtubeSettingRepository->findOneBy( [] );
        $isYoutubeAccountExist = (bool)$youtubeAccount->getGoogleId();

        return $this->render( 'pages/admin/index.html.twig', [
            'isYoutubeAccountExist' => $isYoutubeAccountExist,
            'youtubeSubscribersCount' => $youtubeSubscribersCount,
            'countUsers' => $countUsers,
            'countOnlineCourses' => $countOnlineCourses,
            'countOnlineFormations' => $countOnlineFormations,
            'dailyUsersLast30Days' => $dailyUsersLast30Days,
            'monthlyUsersLast24Months' => $monthlyUsersLast24Months,
            'formTestEmail' => $formTestEmail->createView(),
            'lastCourses' => $this->courseService->getLastCourses( 4 ),
            'scheduled_jobs' => $this->scheduledJobsService->getJobs(),
            'failed_jobs' => $this->failedJobsService->getJobs(),
        ] );
    }

    public function authorHome() : Response
    {
        return $this->render( 'pages/admin/index_author.html.twig', [
            'countAuthorCourses' => $this->courseService->countOnlineCourses( $this->getUser() ),
            'countOnlineCourses' => $this->courseService->countOnlineCourses(),
            'lastCourses' => $this->courseService->getLastCourses( 4 ),
        ] );
    }

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'pages/admin/maintenance.html.twig' );
    }
}
