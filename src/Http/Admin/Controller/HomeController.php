<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Course\CourseService;
use App\Domain\Course\Service\FormationService;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Youtube\YoutubeService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;

#[IsGranted( 'ROLE_ADMIN' )]
class HomeController extends AbstractController
{

    public function __construct(
        private readonly CourseService         $courseService,
        private readonly FormationService      $formationService,
        private readonly YoutubeService        $youtubeService,
        private readonly UserRepository        $userRepository,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route( '/', name: 'home', methods: ['GET'] )]
    public function index() : Response
    {
        $cache = new FilesystemAdapter();

        $youtubeSubscribersCount = $cache->get( 'admin.youtube-subscribers-count',
            function ( ItemInterface $item ) {
                $item->expiresAfter( 3600 );

                try {
                    $countSubscribers = $this->youtubeService->getSubscribersCount();
                } catch ( \Exception $e ) {
                    $this->addFlash( 'error', "Impossible de charger les données depuis YouTube" );
                    $countSubscribers = 0;
                }

                return $countSubscribers;
            } );

        $countUsers = $this->userRepository->countUsers();

        $countOnlineCourses = $cache->get( 'admin.courses-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->courseService->countOnlineCourses();
        } );

        $countOnlineFormations = $cache->get( 'admin.formations-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->formationService->countOnlineFormations();
        });

        $dailyUsersLast30Days = $this->userRepository->countDailyUsersLast30Days();
        $monthlyUsersLast24Months = $this->userRepository->countMonthlyUsersLast24Months();

        return $this->render( 'pages/admin/index.html.twig', [
            'youtubeSubscribersCount' => $youtubeSubscribersCount,
            'countUsers'         => $countUsers,
            'countOnlineCourses' => $countOnlineCourses,
            'countOnlineFormations' => $countOnlineFormations,
            'dailyUsersLast30Days' => $dailyUsersLast30Days,
            'monthlyUsersLast24Months' => $monthlyUsersLast24Months
        ] );
    }

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'pages/admin/maintenance.html.twig' );
    }
}
