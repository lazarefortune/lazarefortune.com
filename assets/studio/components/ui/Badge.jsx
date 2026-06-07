import clsx from 'clsx';

const variants = {
    default: 'bg-slate-100 text-slate-700',
    primary: 'bg-primary-50 text-primary-700',
    success: 'bg-green-50 text-green-700',
    warning: 'bg-amber-50 text-amber-800',
    danger: 'bg-red-50 text-red-700',
    info: 'bg-sky-50 text-sky-700',
};

export function Badge({ children, variant = 'default', className }) {
    return (
        <span className={clsx('inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium', variants[variant], className)}>
            {children}
        </span>
    );
}
