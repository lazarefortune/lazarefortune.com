<?php

namespace App\Http\Admin\Controller;

use App\Domain\Newsletter\Entity\Newsletter;
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
        $this->denyAccessUnlessGranted(NewsletterVoter::EDIT, $newsletter);

        return $this->render('pages/admin/newsletter/_preview.html.twig', [
            'newsletter' => $newsletter
        ]);
    }

    #[Route(path: '/{id<\d+>}/send', name: 'send', methods: ['GET', 'POST'])]
    public function testSend(Newsletter $newsletter, NewsletterSendingService $newsletterSendingService): Response
    {
        $newsletterSendingService->sendNewsletter($newsletter);

        return $this->redirectToRoute('admin_newsletter_index');
    }
}