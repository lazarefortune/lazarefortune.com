<?php

declare( strict_types=1 );

namespace App\Http\Admin\Controller;

use App\Domain\Comment\Entity\Comment;
use App\Infrastructure\Spam\SpamService;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route( path: '/spam/commentaires', name: 'comment_' )]
class CommentController extends CrudController
{
    protected string $templatePath = 'comment';
    protected string $menuItem = 'comment';
    protected string $entity = Comment::class;
    protected string $routePrefix = 'admin_comment';
    protected string $searchField = 'content';

    #[Route( '/', name: 'index' )]
    public function index( SpamService $spamService ) : Response
    {
        $repository = $this->getRepository();
        $query = $repository->querySuspicious($spamService->words());

        return $this->crudIndex( $query );
    }

    public function getRepository() : EntityRepository
    {
        return $this->em->getRepository(Comment::class);
    }
}
