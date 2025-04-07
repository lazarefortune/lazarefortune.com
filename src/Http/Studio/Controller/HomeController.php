<?php

namespace App\Http\Studio\Controller;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Comment\Repository\CommentRepository;
use App\Domain\Course\CourseService;
use App\Http\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted( 'ROLE_AUTHOR' )]
class HomeController extends AbstractController
{
    public function __construct(
        private readonly CourseService    $courseService,
        private readonly CommentRepository $commentRepository,
    )
    {
    }

    #[Route( '/', name: 'home', methods: ['GET', 'POST'] )]
    public function home() : Response
    {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        return $this->render( 'pages/studio/home.html.twig' , [
            'countAuthorCourses' => $this->courseService->countOnlineCourses( $this->getUser() ),
            'countOnlineCourses' => $this->courseService->countOnlineCourses(),
            'lastCourses' => $this->courseService->getLastCourses( 4 ),
            'lastComments' => $this->commentRepository->findLastOnUserCourses($currentUser, 5),
        ] );
    }
}