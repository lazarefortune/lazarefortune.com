<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Core\Repository\UserRepository;
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

        $coursesOnlineCount = $cache->get( 'admin.courses-count', function ( ItemInterface $item ) {
            $item->expiresAfter( 3600 );
            return $this->courseService->getNbCoursesOnline();
        } );

        $usersLastYear = $this->userRepository->countMonthlyUsersLastYearFormatted();


        return $this->render( 'pages/admin/index.html.twig', [
            'youtubeSubscribersCount' => $youtubeSubscribersCount,
            'coursesOnlineCount' => $coursesOnlineCount,
            'usersLastYear' => $usersLastYear
        ] );
    }

    #[Route( '/maintenance', name: 'maintenance', methods: ['GET'] )]
    public function maintenance() : Response
    {
        return $this->render( 'pages/admin/maintenance.html.twig' );
    }
}
