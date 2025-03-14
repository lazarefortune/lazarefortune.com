<?php

namespace App\Http\Admin\Controller;

use App\Infrastructure\Queue\FailedJobsService;
use App\Infrastructure\Queue\ScheduledJobsService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class JobsController extends BaseController
{
    public function __construct(
        private readonly FailedJobsService $failedJobsService,
        private readonly ScheduledJobsService $scheduledJobsService,
    ) {
    }

    #[Route( path: '/{id<\d+>}', name: 'job_delete', methods: ['DELETE'] )]
    public function delete(int $id, Request $request): RedirectResponse
    {
        if ($request->get('delayed')) {
            $this->scheduledJobsService->deleteJob($id);
        } else {
            $this->failedJobsService->deleteJob($id);
        }
        $this->addFlash('success', 'La tâche a bien été supprimée');

        return $this->redirectToRoute('admin_home');
    }

    #[Route( path: '/{id<\d+>}/retry', name: 'job_retry', methods: ['POST'] )]
    public function retry(int $id): RedirectResponse
    {
        $this->failedJobsService->retryJob($id);
        $this->addFlash('success', 'La tâche a bien été relancée');

        return $this->redirectToRoute('admin_home');
    }
}