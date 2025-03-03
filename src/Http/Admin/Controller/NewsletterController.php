<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Newsletter\Entity\Newsletter;
use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use App\Domain\Newsletter\Service\NewsletterSendingService;
use App\Http\Admin\Data\Crud\NewsletterCrudData;
use App\Http\Security\NewsletterVoter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/newsletter', name: 'newsletter_')]
class NewsletterController extends CrudController
{
    protected string $templatePath = 'newsletter';
    protected string $menuItem = 'newsletter';
    protected string $searchField = 'subject';
    protected string $entity = Newsletter::class;
    protected string $routePrefix = 'admin_newsletter';
    protected bool $indexOnSave = false;
    protected array $events = [];

    #[Route('/', name: 'index')]
    public function index() : Response
    {
        return parent::crudIndex($this->getRepository()->createQueryBuilder('row'));
    }

    #[Route(path: '/new', name: 'new', methods: ['POST', 'GET'])]
    public function new(): Response
    {
        $this->denyAccessUnlessGranted(NewsletterVoter::CREATE);

        $newsletter = new Newsletter();
        $data = new NewsletterCrudData($newsletter);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(Newsletter $newsletter): Response
    {
        $this->denyAccessUnlessGranted(NewsletterVoter::EDIT, $newsletter);


        $data = new NewsletterCrudData($newsletter);

        return $this->crudEdit($data);
    }

    #[Route(path: '/{id<\d+>}', name:'delete', methods: ['DELETE'])]
    public function delete(Newsletter $plan): Response
    {
        $this->denyAccessUnlessGranted(NewsletterVoter::DELETE, $plan);

        return $this->crudAjaxDelete($plan);
    }

    #[Route(path: '/previsualisation/{id<\d+>}', name: 'preview', methods: ['GET'])]
    public function preview(Newsletter $newsletter): Response
    {
        return $this->render('pages/admin/newsletter/_preview.html.twig', [
            'newsletter' => $newsletter
        ]);
    }

    #[Route(path: '/inscriptions', name: 'subscribers', methods: ['GET'])]
    public function stats(
        UserRepository $userRepository,
        NewsletterSubscriberRepository $subscriberRepository
    ): Response {
        // Nombre d'utilisateurs abonnés
        $usersSubscribed = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isNewsletterSubscribed = true')
            ->getQuery()
            ->getSingleScalarResult();

        // Nombre d'utilisateurs désabonnés (optionnel, si vous gérez ce cas)
        $usersUnsubscribed = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isNewsletterSubscribed = false')
            ->getQuery()
            ->getSingleScalarResult();

        // Nombre d'abonnés visiteurs inscrits
        $subscribersSubscribed = $subscriberRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.isSubscribed = true')
            ->getQuery()
            ->getSingleScalarResult();

        // Nombre d'abonnés visiteurs désabonnés
        $subscribersUnsubscribed = $subscriberRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where('s.isSubscribed = false')
            ->getQuery()
            ->getSingleScalarResult();

        $users = $userRepository->createQueryBuilder('u')
            ->where('u.isNewsletterSubscribed = true')
            ->getQuery()
            ->getResult();

        $subscribers = $subscriberRepository->createQueryBuilder('s')
            ->where('s.isSubscribed = true')
            ->getQuery()
            ->getResult();

        return $this->render('pages/admin/newsletter/subscribers.html.twig', [
            'usersSubscribed'        => $usersSubscribed,
            'usersUnsubscribed'      => $usersUnsubscribed,
            'subscribersSubscribed'  => $subscribersSubscribed,
            'subscribersUnsubscribed'=> $subscribersUnsubscribed,
            'users'                  => $users,
            'subscribers'            => $subscribers
        ]);
    }
}