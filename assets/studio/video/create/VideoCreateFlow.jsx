import { useState } from 'react';
import clsx from 'clsx';
import { CreationModeCard } from './CreationModeCard';
import { IdeaStep } from './IdeaStep';
import { UploadSoonStep } from './UploadSoonStep';
import { YoutubeSourceStep } from './YoutubeSourceStep';
import { createStudioVideo } from './videoCreateApi';

const STEPS = {
    CHOOSE: 'choose',
    YOUTUBE: 'youtube',
    UPLOAD: 'upload',
    IDEA: 'idea',
    SUCCESS: 'success',
};

export function VideoCreateFlow({ config }) {
    const [step, setStep] = useState(STEPS.CHOOSE);
    const [isSubmitting, setIsSubmitting] = useState(false);
    const [error, setError] = useState('');
    const [successMessage, setSuccessMessage] = useState('');

    const handleCreate = async (mode, payload) => {
        setIsSubmitting(true);
        setError('');

        try {
            const result = await createStudioVideo(config, { mode, ...payload });
            setSuccessMessage(mode === 'idea' ? 'Idee creee. Redirection...' : 'Video creee. Redirection...');
            setStep(STEPS.SUCCESS);
            window.setTimeout(() => {
                window.location.assign(result.redirectUrl);
            }, 500);
        } catch (submissionError) {
            setError(submissionError instanceof Error ? submissionError.message : 'Une erreur est survenue.');
            setIsSubmitting(false);
        }
    };

    if (step === STEPS.SUCCESS) {
        return (
            <div
                className="studio-create-success studio-create-step-enter"
                data-testid="studio-video-create-success"
            >
                <div className="studio-create-success__icon" aria-hidden="true">
                    <span className="studio-create-success__check" />
                </div>
                <p className="studio-create-success__text">{successMessage}</p>
            </div>
        );
    }

    if (step === STEPS.YOUTUBE) {
        return (
            <YoutubeSourceStep
                onBack={() => {
                    setStep(STEPS.CHOOSE);
                    setError('');
                }}
                onSubmit={(payload) => handleCreate('youtube_existing', payload)}
                isSubmitting={isSubmitting}
                error={error}
            />
        );
    }

    if (step === STEPS.UPLOAD) {
        return (
            <UploadSoonStep
                onBack={() => setStep(STEPS.CHOOSE)}
            />
        );
    }

    if (step === STEPS.IDEA) {
        return (
            <IdeaStep
                onBack={() => {
                    setStep(STEPS.CHOOSE);
                    setError('');
                }}
                onSubmit={(payload) => handleCreate('idea', payload)}
                isSubmitting={isSubmitting}
                error={error}
            />
        );
    }

    return (
        <div className="studio-create-flow" data-testid="studio-video-create-flow">
            <p className="studio-create-flow__intro">
                Choisis comment tu veux commencer.
            </p>

            <div className={clsx('studio-create-mode-grid')}>
                <CreationModeCard
                    title="Lier une video YouTube existante"
                    description="Colle une URL ou un ID pour demarrer avec une source deja en ligne."
                    icon="youtube"
                    onClick={() => setStep(STEPS.YOUTUBE)}
                    testId="studio-video-create-card-youtube"
                    delay={0}
                />
                <CreationModeCard
                    title="Uploader une nouvelle video vers YouTube"
                    description="Envoie un fichier directement depuis le Studio."
                    icon="upload-cloud"
                    onClick={() => setStep(STEPS.UPLOAD)}
                    badge="Bientot"
                    testId="studio-video-create-card-upload"
                    delay={80}
                />
                <CreationModeCard
                    title="Creer une idee de video"
                    description="Commence par un titre et structure le contenu ensuite."
                    icon="lightbulb"
                    onClick={() => setStep(STEPS.IDEA)}
                    testId="studio-video-create-card-idea"
                    delay={160}
                />
            </div>
        </div>
    );
}
