import { Badge } from '../../components/ui/Badge';
import { Button } from '../../components/ui/Button';
import { Icon } from '../../components/ui/Icon';
import { getVisibilityLabel } from './visibilityLabels';

const PROVIDER_META = {
    youtube: { label: 'YouTube', icon: 'youtube', accentClass: 'studio-source-provider-card__icon--youtube' },
};

export function ProviderCard({ source, onEdit, showSuccess }) {
    const meta = PROVIDER_META[source.provider] ?? { label: source.provider, icon: 'video', accentClass: '' };

    return (
        <article
            className="studio-source-provider-card studio-source-fade-in"
            data-testid="studio-video-source-current"
        >
            <div className="studio-source-provider-card__header">
                <div className="studio-source-provider-card__brand">
                    <span className={`studio-source-provider-card__icon ${meta.accentClass}`} aria-hidden="true">
                        <Icon name={meta.icon} size={22} />
                    </span>
                    <div>
                        <h3 className="studio-source-provider-card__title">{meta.label}</h3>
                        <Badge variant="primary" size="sm">Source principale</Badge>
                    </div>
                </div>
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    icon="pencil"
                    onClick={onEdit}
                    data-testid="studio-video-source-edit"
                >
                    Modifier
                </Button>
            </div>

            <dl className="studio-source-provider-card__details">
                <div className="studio-source-provider-card__detail">
                    <dt>Identifiant externe</dt>
                    <dd data-testid="studio-video-source-external-id" className="font-mono text-sm">
                        {source.externalId}
                    </dd>
                </div>
                <div className="studio-source-provider-card__detail">
                    <dt>Visibilite YouTube</dt>
                    <dd data-testid="studio-video-source-visibility-current">
                        {getVisibilityLabel(source.visibility)}
                    </dd>
                </div>
                {source.url ? (
                    <div className="studio-source-provider-card__detail studio-source-provider-card__detail--full">
                        <dt>URL</dt>
                        <dd data-testid="studio-video-source-url" className="break-all text-sm">
                            {source.url}
                        </dd>
                    </div>
                ) : null}
            </dl>

            {showSuccess ? (
                <div className="studio-source-success-inline" data-testid="studio-video-source-save-success">
                    <span className="studio-create-success__check" aria-hidden="true" />
                    <span>Source enregistree</span>
                </div>
            ) : null}
        </article>
    );
}

export function EmptySourceState({ availableProviders, onAddSource }) {
    return (
        <div className="studio-source-empty studio-source-fade-in" data-testid="studio-video-source-empty">
            <div className="studio-source-empty__hero">
                <span className="studio-source-empty__icon" aria-hidden="true">
                    <Icon name="video" size={28} />
                </span>
                <h3 className="studio-source-empty__title">Aucune source video</h3>
                <p className="studio-source-empty__text">
                    Associe une video hebergee pour la lire sur le site. Commence par YouTube.
                </p>
            </div>

            <div className="studio-source-providers-grid">
                {availableProviders.map((provider, index) => (
                    <div
                        key={provider.id}
                        className={`studio-source-provider-option ${provider.available ? 'studio-source-provider-option--available' : 'studio-source-provider-option--soon'}`}
                        style={{ '--studio-source-delay': `${index * 60}ms` }}
                    >
                        <span className="studio-source-provider-option__icon" aria-hidden="true">
                            <Icon name={provider.id === 'youtube' ? 'youtube' : 'cloud'} size={20} />
                        </span>
                        <span className="studio-source-provider-option__label">{provider.label}</span>
                        {!provider.available ? (
                            <span className="studio-source-provider-option__badge">Bientot</span>
                        ) : null}
                    </div>
                ))}
            </div>

            <Button
                type="button"
                variant="primary"
                icon="plus"
                onClick={onAddSource}
                data-testid="studio-video-source-add"
            >
                Ajouter une source
            </Button>
        </div>
    );
}
