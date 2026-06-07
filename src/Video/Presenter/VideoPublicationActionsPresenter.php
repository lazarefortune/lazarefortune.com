<?php

declare(strict_types=1);

namespace App\Video\Presenter;

use App\Content\Enum\PublicationStatus;
use App\Video\Entity\Video;

final class VideoPublicationActionsPresenter
{
    public const FORM_PUBLISH = 'studio-video-publish-form';
    public const FORM_DRAFT = 'studio-video-draft-form';
    public const FORM_ARCHIVE = 'studio-video-archive-form';
    public const FORM_CONTENT = 'studio-video-content-form';

    public function present(Video $video): VideoPublicationActionsViewModel
    {
        return match ($video->getStatus()) {
            PublicationStatus::DRAFT => $this->presentDraft(),
            PublicationStatus::SCHEDULED => $this->presentScheduled($video),
            PublicationStatus::PUBLISHED => $this->presentPublished($video),
            PublicationStatus::ARCHIVED => $this->presentArchived(),
        };
    }

    private function presentDraft(): VideoPublicationActionsViewModel
    {
        return new VideoPublicationActionsViewModel(
            statusLabel: 'Brouillon',
            statusHelp: 'Cette video est un brouillon et n est pas visible sur le site public.',
            panelIntro: 'Completez le contenu puis publiez ou programmez la mise en ligne.',
            isDraft: true,
            isScheduled: false,
            isPublished: false,
            isArchived: false,
            showScheduleForm: true,
            scheduleFormTitle: 'Programmer la publication',
            scheduleSubmitLabel: 'Programmer',
            showStandaloneSave: true,
            primaryAction: new VideoPublicationActionItem(
                label: 'Publier maintenant',
                form: self::FORM_PUBLISH,
                icon: 'upload',
                testid: 'studio-video-publish-now',
            ),
            secondaryActions: [
                new VideoPublicationActionItem(
                    label: 'Programmer',
                    href: '#publication',
                    icon: 'calendar',
                    testid: 'studio-video-schedule-link',
                ),
                new VideoPublicationActionItem(
                    label: 'Archiver',
                    form: self::FORM_ARCHIVE,
                    icon: 'archive',
                    testid: 'studio-video-archive-action',
                ),
            ],
        );
    }

    private function presentScheduled(Video $video): VideoPublicationActionsViewModel
    {
        $scheduledAt = $this->formatDateTime($video->getScheduledAt());

        return new VideoPublicationActionsViewModel(
            statusLabel: 'Planifie',
            statusHelp: $scheduledAt !== null
                ? sprintf('Publication prevue le %s.', $scheduledAt)
                : 'Cette video est programmee pour une publication automatique.',
            panelIntro: 'La mise en ligne sera declenchee par la commande app:content:publish-scheduled a la date prevue.',
            isDraft: false,
            isScheduled: true,
            isPublished: false,
            isArchived: false,
            showScheduleForm: true,
            scheduleFormTitle: 'Modifier la programmation',
            scheduleSubmitLabel: 'Mettre a jour la programmation',
            showStandaloneSave: true,
            primaryAction: new VideoPublicationActionItem(
                label: 'Publier maintenant',
                form: self::FORM_PUBLISH,
                icon: 'upload',
                testid: 'studio-video-publish-now',
            ),
            secondaryActions: [
                new VideoPublicationActionItem(
                    label: 'Modifier la programmation',
                    href: '#publication',
                    icon: 'calendar',
                    testid: 'studio-video-schedule-link',
                ),
                new VideoPublicationActionItem(
                    label: 'Remettre en brouillon',
                    form: self::FORM_DRAFT,
                    icon: 'undo-2',
                    testid: 'studio-video-draft-action',
                ),
                new VideoPublicationActionItem(
                    label: 'Archiver',
                    form: self::FORM_ARCHIVE,
                    icon: 'archive',
                    testid: 'studio-video-archive-action',
                ),
            ],
        );
    }

    private function presentPublished(Video $video): VideoPublicationActionsViewModel
    {
        $publishedAt = $this->formatDateTime($video->getPublishedAt());

        return new VideoPublicationActionsViewModel(
            statusLabel: 'Publie',
            statusHelp: $publishedAt !== null
                ? sprintf('Publiee le %s. Visible selon la regle de visibilite.', $publishedAt)
                : 'Cette video est publiee et visible selon la regle de visibilite.',
            panelIntro: 'Mettez a jour le contenu ou changez le statut si vous devez retirer la video du catalogue public.',
            isDraft: false,
            isScheduled: false,
            isPublished: true,
            isArchived: false,
            showScheduleForm: false,
            scheduleFormTitle: '',
            scheduleSubmitLabel: '',
            showStandaloneSave: false,
            primaryAction: new VideoPublicationActionItem(
                label: 'Mettre a jour',
                form: self::FORM_CONTENT,
                icon: 'save',
                testid: 'studio-video-update-content',
            ),
            secondaryActions: [
                new VideoPublicationActionItem(
                    label: 'Remettre en brouillon',
                    form: self::FORM_DRAFT,
                    icon: 'undo-2',
                    testid: 'studio-video-draft-action',
                ),
                new VideoPublicationActionItem(
                    label: 'Archiver',
                    form: self::FORM_ARCHIVE,
                    icon: 'archive',
                    testid: 'studio-video-archive-action',
                ),
            ],
        );
    }

    private function presentArchived(): VideoPublicationActionsViewModel
    {
        return new VideoPublicationActionsViewModel(
            statusLabel: 'Archive',
            statusHelp: 'Cette video est archivee et n est plus visible publiquement.',
            panelIntro: 'Restaurez la video en brouillon pour la preparer a une nouvelle publication.',
            isDraft: false,
            isScheduled: false,
            isPublished: false,
            isArchived: true,
            showScheduleForm: true,
            scheduleFormTitle: 'Programmer la publication',
            scheduleSubmitLabel: 'Programmer',
            showStandaloneSave: true,
            primaryAction: new VideoPublicationActionItem(
                label: 'Restaurer en brouillon',
                form: self::FORM_DRAFT,
                icon: 'undo-2',
                testid: 'studio-video-restore-draft',
            ),
            secondaryActions: [
                new VideoPublicationActionItem(
                    label: 'Publier maintenant',
                    form: self::FORM_PUBLISH,
                    icon: 'upload',
                    testid: 'studio-video-publish-now',
                ),
                new VideoPublicationActionItem(
                    label: 'Programmer',
                    href: '#publication',
                    icon: 'calendar',
                    testid: 'studio-video-schedule-link',
                ),
            ],
            panelAlertVariant: 'warning',
            panelAlertMessage: 'Cette video est archivee et n est plus visible publiquement.',
        );
    }

    private function formatDateTime(?\DateTimeImmutable $date): ?string
    {
        return $date?->format('d/m/Y H:i');
    }
}
