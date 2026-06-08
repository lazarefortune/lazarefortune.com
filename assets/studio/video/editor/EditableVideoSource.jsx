import { useEffect, useState } from 'react';
import clsx from 'clsx';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Icon } from '../../components/ui/Icon';
import { extractYoutubeId } from '../create/extractYoutubeId';
import { VISIBILITY_OPTIONS } from './visibilityLabels';

const EXTRACTION_DELAY_MS = 450;

export function EditableVideoSource({
    initialSourceRef = '',
    initialVisibility = 'unlisted',
    onSave,
    onCancel,
    isSubmitting,
    error,
}) {
    const [sourceRef, setSourceRef] = useState(initialSourceRef);
    const [visibility, setVisibility] = useState(initialVisibility);
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

        onSave({
            sourceRef: sourceRef.trim(),
            visibility,
        });
    };

    return (
        <form
            className="studio-source-editor studio-source-editor-enter"
            onSubmit={handleSubmit}
            data-testid="studio-video-source-form"
        >
            <div className="studio-source-editor__header">
                <h3 className="studio-source-editor__title">
                    {initialSourceRef ? 'Modifier la source YouTube' : 'Ajouter une source YouTube'}
                </h3>
                <p className="studio-source-editor__description">
                    Colle une URL ou un identifiant YouTube. La detection est instantanee cote navigateur.
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
                data-testid="studio-video-source-ref-input"
            />

            <div
                className={clsx(
                    'studio-create-youtube-preview',
                    isExtracting && 'studio-create-youtube-preview--loading',
                    showDetected && 'studio-create-youtube-preview--detected',
                )}
                data-testid="studio-video-source-preview"
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

            <div className="flex flex-col gap-1.5">
                <label htmlFor="studio-video-source-visibility" className="ds-label">
                    Visibilite YouTube
                </label>
                <select
                    id="studio-video-source-visibility"
                    className="ds-input min-h-11"
                    value={visibility}
                    onChange={(event) => setVisibility(event.target.value)}
                    disabled={isSubmitting}
                    data-testid="studio-video-source-visibility-input"
                >
                    {VISIBILITY_OPTIONS.map((option) => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
            </div>

            {error ? (
                <p className="studio-create-form__error" role="alert" data-testid="studio-video-source-error">
                    {error}
                </p>
            ) : null}

            <div className="studio-source-editor__actions">
                <Button
                    type="submit"
                    icon="save"
                    disabled={isSubmitting || !detectedId}
                    data-testid="studio-video-source-submit"
                >
                    {isSubmitting ? 'Enregistrement...' : 'Enregistrer'}
                </Button>
                <Button
                    type="button"
                    variant="secondary"
                    onClick={onCancel}
                    disabled={isSubmitting}
                    data-testid="studio-video-source-cancel"
                >
                    Annuler
                </Button>
            </div>
        </form>
    );
}
