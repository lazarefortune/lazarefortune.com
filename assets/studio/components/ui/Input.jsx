import clsx from 'clsx';
import { useId } from 'react';

export function Input({
    label,
    id,
    hint,
    error,
    className,
    wrapperClassName,
    ...props
}) {
    const generatedId = useId();
    const inputId = id ?? generatedId;
    const hintId = hint ? `${inputId}-hint` : undefined;
    const errorId = error ? `${inputId}-error` : undefined;

    return (
        <div className={wrapperClassName}>
            {label && (
                <label htmlFor={inputId} className="ds-label">
                    {label}
                </label>
            )}
            <input
                id={inputId}
                className={clsx('ds-input', error && 'border-red-400 focus-visible:ring-red-500', className)}
                aria-describedby={[hintId, errorId].filter(Boolean).join(' ') || undefined}
                aria-invalid={error ? 'true' : undefined}
                {...props}
            />
            {hint && !error && (
                <p id={hintId} className="mt-1 text-xs text-slate-500">
                    {hint}
                </p>
            )}
            {error && (
                <p id={errorId} className="mt-1 text-xs text-red-600" role="alert">
                    {error}
                </p>
            )}
        </div>
    );
}
