<?php

namespace App\Http\Admin\Controller;

use App\Domain\Appointment\Service\AppointmentService;
use App\Domain\Client\Service\UserService;
use App\Domain\Realisation\Service\RealisationService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted( 'ROLE_ADMIN' )]
class PageController extends AbstractController
{

    #[Route( '/dashboard', name: 'home', methods: ['GET'] )]
    public function index(
        UserService           $clientService,
        RealisationService    $realisationService,
        AppointmentService    $appointmentService,
        ChartBuilderInterface $chartBuilder
    ) : Response
    {

        $clientsByMonth = $clientService->getClientsByMonth();

        $chart = $chartBuilder->createChart( Chart::TYPE_LINE );

        $chart->setData( [
            'labels' => ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            'datasets' => [
                [
                    'label' => 'Nouveaux clients',
                    'backgroundColor' => 'rgb(75, 5, 173)',
                    'borderColor' => 'rgb(75, 5, 173)',
                    'data' => array_values( $clientsByMonth ),
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


        return $this->render( 'admin/index.html.twig', [
            'nbClients' => $clientService->getCountClients(),
            'nbRealisations' => $realisationService->getCountRealisations(),
            'nbAppointments' => $appointmentService->getCountAppointments(),
            'chart' => $chart,
        ] );
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
