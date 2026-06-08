import clsx from 'clsx';
import { Icon } from '../../components/ui/Icon';

export function CreationModeCard({
    title,
    description,
    icon,
    onClick,
    disabled = false,
    badge,
    testId,
    delay = 0,
}) {
    return (
        <button
            type="button"
            className={clsx(
                'studio-create-mode-card group text-left',
                disabled && 'studio-create-mode-card--disabled',
            )}
            onClick={onClick}
            disabled={disabled}
            data-testid={testId}
            style={{ '--studio-create-delay': `${delay}ms` }}
        >
            <span className="studio-create-mode-card__icon" aria-hidden="true">
                <Icon name={icon} size={22} />
            </span>
            <span className="studio-create-mode-card__content">
                <span className="studio-create-mode-card__title-row">
                    <span className="studio-create-mode-card__title">{title}</span>
                    {badge ? (
                        <span className="studio-create-mode-card__badge">{badge}</span>
                    ) : null}
                </span>
                <span className="studio-create-mode-card__description">{description}</span>
            </span>
            <span className="studio-create-mode-card__arrow" aria-hidden="true">
                <Icon name="arrow-right" size={18} />
            </span>
        </button>
    );
}
