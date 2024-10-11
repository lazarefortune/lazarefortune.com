<?php

namespace App\Http\Admin\Controller;

use App\Domain\Account\Service\UserService;
use App\Domain\Course\CourseService;
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

        $monthlyUsersLastYear = $cache->get( 'admin.users-last-year-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->userService->getMonthlyUsersLastYear();
        } );

        $usersCount = $cache->get( 'admin.users-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->userService->getNbUsers();
        } );

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

        $coursesOnlineCount = $cache->get( 'admin.courses-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->courseService->getNbCoursesOnline();
        } );

        # Chart monthly users
        $chart = $this->createMonthlyUsersChart( $monthlyUsersLastYear );

        $usersLastYear = [
            [
                'month' => 'Janvier',
                'amount' => $monthlyUsersLastYear[1] ?? 0,
            ],
            [
                'month' => 'Février',
                'amount' => $monthlyUsersLastYear[2] ?? 0,
            ],
            [
                'month' => 'Mars',
                'amount' => $monthlyUsersLastYear[3] ?? 0,
            ],
            [
                'month' => 'Avril',
                'amount' => $monthlyUsersLastYear[4] ?? 0,
            ],
            [
                'month' => 'Mai',
                'amount' => $monthlyUsersLastYear[5] ?? 0,
            ],
            [
                'month' => 'Juin',
                'amount' => $monthlyUsersLastYear[6] ?? 0,
            ],
            [
                'month' => 'Juillet',
                'amount' => $monthlyUsersLastYear[7] ?? 0,
            ],
            [
                'month' => 'Août',
                'amount' => $monthlyUsersLastYear[8] ?? 0,
            ],
            [
                'month' => 'Septembre',
                'amount' => $monthlyUsersLastYear[9] ?? 0,
            ],
            [
                'month' => 'Octobre',
                'amount' => $monthlyUsersLastYear[10] ?? 0,
            ],
            [
                'month' => 'Novembre',
                'amount' => $monthlyUsersLastYear[11] ?? 0,
            ],
            [
                'month' => 'Décembre',
                'amount' => $monthlyUsersLastYear[12] ?? 0,
            ],
        ];

        return $this->render( 'admin/index.html.twig', [
            'nbUsers' => $usersCount,
            'youtubeSubscribersCount' => $youtubeSubscribersCount,
            'coursesOnlineCount' => $coursesOnlineCount,
            'chart' => $chart,
            'usersLastYear' => $usersLastYear,
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
