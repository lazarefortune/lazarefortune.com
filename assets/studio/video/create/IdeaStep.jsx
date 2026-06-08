import { useState } from 'react';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Icon } from '../../components/ui/Icon';

export function IdeaStep({ onBack, onSubmit, isSubmitting, error }) {
    const [title, setTitle] = useState('');

    const handleSubmit = (event) => {
        event.preventDefault();
        onSubmit({ title: title.trim() });
    };

    return (
        <div className="studio-create-step studio-create-step-enter" data-testid="studio-video-create-idea-step">
            <button type="button" className="studio-create-back" onClick={onBack} disabled={isSubmitting}>
                <Icon name="arrow-left" size={16} />
                Retour aux choix
            </button>

            <form className="studio-create-form" onSubmit={handleSubmit}>
                <div className="studio-create-form__intro">
                    <h2 className="studio-create-form__title">Creer une idee de video</h2>
                    <p className="studio-create-form__description">
                        Donne un titre provisoire. Tu pourras enrichir le contenu juste apres.
                    </p>
                </div>

                <Input
                    label="Titre de l'idee"
                    value={title}
                    onChange={(event) => setTitle(event.target.value)}
                    placeholder="Ex. Introduction a Symfony Messenger"
                    autoFocus
                    required
                    disabled={isSubmitting}
                    error={error}
                    data-testid="studio-video-create-idea-title"
                />

                <div className="studio-create-form__actions">
                    <Button
                        type="submit"
                        icon="sparkles"
                        disabled={isSubmitting || title.trim() === ''}
                        data-testid="studio-video-create-idea-submit"
                    >
                        {isSubmitting ? 'Creation en cours...' : 'Creer l\'idee'}
                    </Button>
                </div>
            </form>
        </div>
    );
}
