import { useEffect, useState } from 'react';
import clsx from 'clsx';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Icon } from '../../components/ui/Icon';
import { extractYoutubeId } from './extractYoutubeId';

const EXTRACTION_DELAY_MS = 450;

export function YoutubeSourceStep({ onBack, onSubmit, isSubmitting, error }) {
    const [sourceRef, setSourceRef] = useState('');
    const [title, setTitle] = useState('');
    const [detectedId, setDetectedId] = useState(null);
    const [isExtracting, setIsExtracting] = useState(false);
    const [showDetected, setShowDetected] = useState(false);

    useEffect(() => {
        const value = sourceRef.trim();
        if (value === '') {
            setDetectedId(null);
            setIsExtracting(false);
            setShowDetected(false);

            return undefined;
        }

        setIsExtracting(true);
        setShowDetected(false);

        const timeoutId = window.setTimeout(() => {
            const id = extractYoutubeId(value);
            setDetectedId(id);
            setIsExtracting(false);
            setShowDetected(id !== null);
        }, EXTRACTION_DELAY_MS);

        return () => window.clearTimeout(timeoutId);
    }, [sourceRef]);

    const handleSubmit = (event) => {
        event.preventDefault();

        if (!detectedId) {
            return;
        }

        onSubmit({
            sourceRef: sourceRef.trim(),
            title: title.trim(),
            visibility: 'unlisted',
        });
    };

    return (
        <div className="studio-create-step studio-create-step-enter" data-testid="studio-video-create-youtube-step">
            <button type="button" className="studio-create-back" onClick={onBack} disabled={isSubmitting}>
                <Icon name="arrow-left" size={16} />
                Retour aux choix
            </button>

            <form className="studio-create-form" onSubmit={handleSubmit}>
                <div className="studio-create-form__intro">
                    <h2 className="studio-create-form__title">Lier une video YouTube existante</h2>
                    <p className="studio-create-form__description">
                        Colle une URL ou un identifiant YouTube. La detection est instantanee cote navigateur,
                        la validation finale reste cote serveur.
                    </p>
                </div>

                <Input
                    label="URL ou ID YouTube"
                    value={sourceRef}
                    onChange={(event) => setSourceRef(event.target.value)}
                    placeholder="https://youtu.be/... ou dQw4w9WgXcQ"
                    autoFocus
                    required
                    disabled={isSubmitting}
                    data-testid="studio-video-create-youtube-ref"
                />

                <div
                    className={clsx(
                        'studio-create-youtube-preview',
                        isExtracting && 'studio-create-youtube-preview--loading',
                        showDetected && 'studio-create-youtube-preview--detected',
                    )}
                    data-testid="studio-video-create-youtube-preview"
                    aria-live="polite"
                >
                    {isExtracting ? (
                        <>
                            <span className="studio-create-youtube-preview__spinner" aria-hidden="true" />
                            <span>Analyse du lien YouTube...</span>
                        </>
                    ) : showDetected ? (
                        <>
                            <span className="studio-create-youtube-preview__brand" aria-hidden="true">
                                <Icon name="youtube" size={20} />
                            </span>
                            <span>
                                <strong>YouTube detecte</strong>
                                <span className="studio-create-youtube-preview__id">{detectedId}</span>
                            </span>
                        </>
                    ) : sourceRef.trim() !== '' ? (
                        <span className="studio-create-youtube-preview__error">
                            Lien ou identifiant YouTube non reconnu.
                        </span>
                    ) : (
                        <span className="studio-create-youtube-preview__placeholder">
                            La preview apparaitra ici des que le lien sera reconnu.
                        </span>
                    )}
                </div>

                <Input
                    label="Titre (optionnel)"
                    value={title}
                    onChange={(event) => setTitle(event.target.value)}
                    placeholder="Laisse vide pour un titre automatique"
                    hint="Si vide, un titre provisoire sera genere a partir de l'identifiant YouTube."
                    disabled={isSubmitting}
                    data-testid="studio-video-create-youtube-title"
                />

                {error ? (
                    <p className="studio-create-form__error" role="alert" data-testid="studio-video-create-error">
                        {error}
                    </p>
                ) : null}

                <div className="studio-create-form__actions">
                    <Button
                        type="submit"
                        icon="plus"
                        disabled={isSubmitting || !detectedId}
                        data-testid="studio-video-create-youtube-submit"
                    >
                        {isSubmitting ? 'Creation en cours...' : 'Creer la video'}
                    </Button>
                </div>
            </form>
        </div>
    );
}
