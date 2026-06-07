import clsx from 'clsx';
import { Icon } from './Icon';

const variants = {
    primary: 'ds-btn-primary',
    secondary: 'ds-btn-secondary',
    ghost: 'ds-btn-ghost',
    danger: 'ds-btn-danger',
};

const sizes = {
    sm: 'ds-btn-sm',
    md: 'ds-btn-md',
    lg: 'ds-btn-lg',
};

export function Button({
    children,
    variant = 'primary',
    size = 'md',
    className,
    type = 'button',
    icon,
    iconPosition = 'left',
    ...props
}) {
    return (
        <button
            type={type}
            className={clsx('ds-btn', variants[variant], sizes[size], className)}
            {...props}
        >
            {icon && iconPosition === 'left' ? <Icon name={icon} size={16} /> : null}
            {children}
            {icon && iconPosition === 'right' ? <Icon name={icon} size={16} /> : null}
        </button>
    );
}
