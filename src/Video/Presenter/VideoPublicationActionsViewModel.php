<?php

declare(strict_types=1);

namespace App\Video\Presenter;

final readonly class VideoPublicationActionsViewModel
{
    /**
     * @param list<VideoPublicationActionItem> $secondaryActions
     */
    public function __construct(
        public string $statusLabel,
        public string $statusHelp,
        public string $panelIntro,
        public bool $isDraft,
        public bool $isScheduled,
        public bool $isPublished,
        public bool $isArchived,
        public bool $showScheduleForm,
        public string $scheduleFormTitle,
        public string $scheduleSubmitLabel,
        public bool $showStandaloneSave,
        public VideoPublicationActionItem $primaryAction,
        public array $secondaryActions,
        public ?string $panelAlertVariant = null,
        public ?string $panelAlertMessage = null,
    ) {
    }
}
