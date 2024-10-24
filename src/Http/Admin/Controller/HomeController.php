<?php

namespace App\Http\Admin\Controller;

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
class HomeController extends AbstractController
{

    public function __construct(
        private readonly CourseService         $courseService,
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly YoutubeService        $youtubeService
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

        $coursesOnlineCount = $cache->get( 'admin.courses-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->courseService->getNbCoursesOnline();
        } );

        return $this->render( 'pages/admin/index.html.twig', [
            'youtubeSubscribersCount' => $youtubeSubscribersCount,
            'coursesOnlineCount' => $coursesOnlineCount,
            'usersLastYear' => [
                [
                    'month' => 'Janvier',
                    'users' => 0,
                ],
                [
                    'month' => 'Février',
                    'users' => 10,
                ],
                [
                    'month' => 'Mars',
                    'users' => 20,
                ],
                [
                    'month' => 'Avril',
                    'users' => 4,
                ],
                [
                    'month' => 'Mai',
                    'users' => 6,
                ],
                [
                    'month' => 'Juin',
                    'users' => 9,
                ],
                [
                    'month' => 'Juillet',
                    'users' => 1,
                ],
                [
                    'month' => 'Août',
                    'users' => 0,
                ],
                [
                    'month' => 'Septembre',
                    'users' => 10,
                ],
                [
                    'month' => 'Octobre',
                    'users' => 0,
                ],
                [
                    'month' => 'Novembre',
                    'users' => 2,
                ],
                [
                    'month' => 'Décembre',
                    'users' => 0,
                ]
            ]
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

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'pages/admin/maintenance.html.twig' );
    }
}
