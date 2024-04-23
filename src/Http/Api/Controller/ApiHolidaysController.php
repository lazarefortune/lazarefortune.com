<?php

namespace App\Http\Api\Controller;

use App\Domain\Holiday\HolidayService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route( '/holidays', name: 'holidays_' )]
class ApiHolidaysController extends AbstractController
{
    public function __construct( private readonly HolidayService $holidayService )
    {
    }

    #[Route( '/', name: 'index', methods: ['GET'] )]
    public function index() : JsonResponse
    {
        return new JsonResponse( $this->holidayService->getHolidaysForApi() );
    }
}