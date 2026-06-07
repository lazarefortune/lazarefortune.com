import clsx from 'clsx';

const variants = {
    default: 'ds-badge-default',
    primary: 'ds-badge-primary',
    success: 'ds-badge-success',
    warning: 'ds-badge-warning',
    danger: 'ds-badge-danger',
    info: 'ds-badge-info',
};

const sizes = {
    sm: 'ds-badge-sm',
    md: 'ds-badge-md',
    lg: 'ds-badge-lg',
};

export function Badge({ children, variant = 'default', size = 'md', className }) {
    return (
        <span className={clsx('ds-badge', sizes[size], variants[variant], className)}>
            {children}
        </span>
    );
}
