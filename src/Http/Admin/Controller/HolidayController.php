<?php

namespace App\Http\Admin\Controller;

use App\Domain\Holiday\Entity\Holiday;
use App\Domain\Holiday\Form\HolidayForm;
use App\Domain\Holiday\HolidayService;
use App\Domain\Holiday\Repository\HolidayRepository;
use App\Domain\Holiday\Voter\HolidayVoter;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/vacances', name: 'holidays_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class HolidayController extends AbstractController
{

    public function __construct( private readonly HolidayService $holidayService )
    {
    }

    #[Route( '/', name: 'index' )]
    public function list( HolidayService $holidayService ) : Response
    {

        $holidays = $holidayService->getAll();

        return $this->render( 'admin/holidays/index.html.twig', [
            'holidays' => $holidays,
        ] );
    }

    #[Route( '/new', name: 'new' )]
    public function addHolidays( Request $request, HolidayService $holidayService ) : Response
    {

        $holiday = new Holiday();
        $formHoliday = $this->createForm( HolidayForm::class, $holiday );

        $formHoliday->handleRequest( $request );

        if ( $formHoliday->isSubmitted() && $formHoliday->isValid() ) {
            try {
                $holidayService->addHoliday( $holiday );
                $this->addFlash( 'success', 'Période de fermeture ajoutée' );

                return $this->redirectToRoute( 'app_admin_holidays_index' );
            } catch ( \Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
            }
        }

        return $this->render( 'admin/holidays/new.html.twig', [
            'form' => $formHoliday->createView(),
        ] );
    }

    #[Route( '/{id<\d+>}/edit', name: 'edit', methods: ['GET', 'POST'] )]
    #[IsGranted( HolidayVoter::EDIT, subject: 'holiday' )]
    public function editHolidays( Request $request, Holiday $holiday ) : Response
    {

        $formHoliday = $this->createForm( HolidayForm::class, $holiday );

        $formHoliday->handleRequest( $request );

        if ( $formHoliday->isSubmitted() && $formHoliday->isValid() ) {
            $this->holidayService->updateHoliday( $holiday );
            $this->addFlash( 'success', 'Période de fermeture modifiée' );

            return $this->redirectToRoute( 'app_admin_holidays_index' );
        }

        return $this->render( 'admin/holidays/edit.html.twig', [
            'form' => $formHoliday->createView(),
        ] );
    }

    #[Route( '/{id<\d+>}', name: 'delete', methods: ['POST'] )]
    #[IsGranted( HolidayVoter::DELETE, subject: 'holiday' )]
    public function delete( Request $request, Holiday $holiday ) : Response
    {
        if ( $this->isCsrfTokenValid( 'delete' . $holiday->getId(), $request->request->get( '_token' ) ) ) {
            $this->holidayService->delete( $holiday, true );

            $this->addFlash( 'success', 'Période de fermeture supprimée' );
        }

        return $this->redirectToRoute( 'app_admin_holidays_index', [], Response::HTTP_SEE_OTHER );
    }
}