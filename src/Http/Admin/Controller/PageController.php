<?php

namespace App\Http\Admin\Controller;

use App\Domain\Account\Service\UserService;
use App\Domain\Appointment\Service\AppointmentService;
use App\Domain\Course\CourseService;
use App\Domain\Realisation\Service\RealisationService;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Youtube\YoutubeService;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted( 'ROLE_ADMIN' )]
class PageController extends AbstractController
{

    public function __construct(
        private readonly UserService           $userService,
        private readonly CourseService         $courseService,
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly YoutubeService        $youtubeService
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    #[Route( '/dashboard', name: 'home', methods: ['GET'] )]
    public function index() : Response
    {
        $cache = new FilesystemAdapter();

        $monthlyUsersLastYear = $cache->get('admin.users-last-year-count', function( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->userService->getMonthlyUsersLastYear();
        });

        $usersCount = $cache->get('admin.users-count', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->userService->getNbUsers();
        });

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

        $coursesOnlineCount = $cache->get('admin.courses-count', function (ItemInterface $item) {
           $item->expiresAfter(3600);
           return $this->courseService->getNbCoursesOnline();
        });

        # Chart monthly users
        $chart = $this->createMonthlyUsersChart( $monthlyUsersLastYear );


        return $this->render( 'admin/index.html.twig', [
            'nbUsers' => $usersCount,
            'youtubeSubscribersCount' => $youtubeSubscribersCount,
            'coursesOnlineCount' => $coursesOnlineCount,
            'chart' => $chart,
        ] );
    }

    private function createMonthlyUsersChart( array $monthlyUsersLastYear ) : Chart
    {
        $chart = $this->chartBuilder->createChart( Chart::TYPE_LINE );

        $chart->setData( [
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            'datasets' => [
                [
                    'label' => 'Nouveaux utilisateurs',
                    'backgroundColor' => 'rgb(75, 5, 173)',
                    'borderColor' => 'rgb(75, 5, 173)',
                    'data' => array_values( $monthlyUsersLastYear ),
                ],
            ],
        ] );

        $chart->setOptions( [
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 5,
                ],
            ],
        ] );

        return $chart;
    }

    #[Route( '/test', name: 'test', methods: ['GET'] )]
    public function testViewAdmin() : Response
    {
        $paymentUrl = "";
        return $this->render( 'admin/test.html.twig', [
                'paymentUrl' => $paymentUrl,
            ]
        );
    }

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'admin/layouts/maintenance.html.twig' );
    }
}
