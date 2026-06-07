import clsx from 'clsx';

export function Card({ title, footer, children, className }) {
    return (
        <article className={clsx('ds-card', className)}>
            {title && (
                <header className="border-b border-slate-100 px-5 py-4">
                    <h3 className="text-base font-semibold text-slate-900">{title}</h3>
                </header>
            )}
            <div className="px-5 py-4 text-sm text-slate-600">{children}</div>
            {footer && (
                <footer className="border-t border-slate-100 px-5 py-3 text-sm text-slate-500">
                    {footer}
                </footer>
            )}
        </article>
    );
}
