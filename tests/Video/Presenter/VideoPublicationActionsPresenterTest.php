<?php

declare(strict_types=1);

namespace App\Tests\Video\Presenter;

use App\Auth\Entity\User;
use App\Content\Enum\PublicationStatus;
use App\Video\Entity\Video;
use App\Video\Presenter\VideoPublicationActionsPresenter;
use PHPUnit\Framework\TestCase;

final class VideoPublicationActionsPresenterTest extends TestCase
{
    private VideoPublicationActionsPresenter $presenter;

    protected function setUp(): void
    {
        $this->presenter = new VideoPublicationActionsPresenter();
    }

    public function testDraftActions(): void
    {
        $video = $this->createVideo(PublicationStatus::DRAFT);
        $viewModel = $this->presenter->present($video);

        $this->assertTrue($viewModel->isDraft);
        $this->assertFalse($viewModel->isScheduled);
        $this->assertFalse($viewModel->isPublished);
        $this->assertFalse($viewModel->isArchived);
        $this->assertTrue($viewModel->showScheduleForm);
        $this->assertSame('Publier maintenant', $viewModel->primaryAction->label);
        $this->assertSame(VideoPublicationActionsPresenter::FORM_PUBLISH, $viewModel->primaryAction->form);
        $this->assertSame(['Programmer', 'Archiver'], $this->secondaryLabels($viewModel));
    }

    public function testScheduledActions(): void
    {
        $scheduledAt = new \DateTimeImmutable('+2 days');
        $video = $this->createVideo(PublicationStatus::SCHEDULED)
            ->setScheduledAt($scheduledAt);
        $viewModel = $this->presenter->present($video);

        $this->assertTrue($viewModel->isScheduled);
        $this->assertSame('Publier maintenant', $viewModel->primaryAction->label);
        $this->assertSame(
            ['Modifier la programmation', 'Remettre en brouillon', 'Archiver'],
            $this->secondaryLabels($viewModel),
        );
        $this->assertSame('Modifier la programmation', $viewModel->scheduleFormTitle);
        $this->assertStringContainsString($scheduledAt->format('d/m/Y H:i'), $viewModel->statusHelp);
    }

    public function testPublishedActions(): void
    {
        $publishedAt = new \DateTimeImmutable('-1 day');
        $video = $this->createVideo(PublicationStatus::PUBLISHED)
            ->setPublishedAt($publishedAt);
        $viewModel = $this->presenter->present($video);

        $this->assertTrue($viewModel->isPublished);
        $this->assertFalse($viewModel->showScheduleForm);
        $this->assertFalse($viewModel->showStandaloneSave);
        $this->assertSame('Mettre a jour', $viewModel->primaryAction->label);
        $this->assertSame(VideoPublicationActionsPresenter::FORM_CONTENT, $viewModel->primaryAction->form);
        $this->assertSame(['Remettre en brouillon', 'Archiver'], $this->secondaryLabels($viewModel));
        $this->assertStringContainsString($publishedAt->format('d/m/Y H:i'), $viewModel->statusHelp);
    }

    public function testArchivedActions(): void
    {
        $video = $this->createVideo(PublicationStatus::ARCHIVED);
        $viewModel = $this->presenter->present($video);

        $this->assertTrue($viewModel->isArchived);
        $this->assertSame('Restaurer en brouillon', $viewModel->primaryAction->label);
        $this->assertSame(VideoPublicationActionsPresenter::FORM_DRAFT, $viewModel->primaryAction->form);
        $this->assertSame(['Publier maintenant', 'Programmer'], $this->secondaryLabels($viewModel));
        $this->assertSame('warning', $viewModel->panelAlertVariant);
        $this->assertStringContainsString('archivee', $viewModel->panelAlertMessage ?? '');
    }

    /**
     * @return list<string>
     */
    private function secondaryLabels(object $viewModel): array
    {
        return array_map(
            static fn (object $item): string => $item->label,
            $viewModel->secondaryActions,
        );
    }

    private function createVideo(PublicationStatus $status): Video
    {
        $author = new User();
        $author->setEmail(sprintf('presenter-%s@example.com', $status->value));

        return (new Video($author))->setStatus($status);
    }
}
