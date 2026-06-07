import clsx from 'clsx';
import { createContext, useCallback, useContext, useMemo, useState } from 'react';
import { Icon } from './Icon';

const ToastContext = createContext(null);

const variantClasses = {
    info: 'border-sky-200 bg-sky-50 text-sky-900',
    success: 'border-green-200 bg-green-50 text-green-900',
    warning: 'border-amber-200 bg-amber-50 text-amber-900',
    danger: 'border-red-200 bg-red-50 text-red-900',
};

const variantIcons = {
    info: 'info',
    success: 'check-circle',
    warning: 'alert-triangle',
    danger: 'alert-circle',
};

export function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const dismiss = useCallback((id) => {
        setToasts((current) => current.filter((toast) => toast.id !== id));
    }, []);

    const toast = useCallback(({ message, variant = 'info', duration = 4000 }) => {
        const id = crypto.randomUUID();
        setToasts((current) => [...current, { id, message, variant }]);

        if (duration > 0) {
            window.setTimeout(() => dismiss(id), duration);
        }
    }, [dismiss]);

    const value = useMemo(() => ({ toast, dismiss }), [toast, dismiss]);

    return (
        <ToastContext.Provider value={value}>
            {children}
            <div
                aria-live="polite"
                aria-relevant="additions"
                className="pointer-events-none fixed bottom-4 right-4 z-50 flex w-full max-w-sm flex-col gap-2"
            >
                {toasts.map((item) => (
                    <div
                        key={item.id}
                        role="status"
                        className={clsx('pointer-events-auto rounded-lg border px-4 py-3 text-sm shadow-lg', variantClasses[item.variant])}
                    >
                        <div className="flex items-start gap-3">
                            <Icon name={variantIcons[item.variant]} size={18} className="mt-0.5" />
                            <div className="flex-1">
                                <p>{item.message}</p>
                            </div>
                            <button
                                type="button"
                                className="ds-focus rounded p-1 opacity-70 hover:opacity-100"
                                aria-label="Fermer la notification"
                                onClick={() => dismiss(item.id)}
                            >
                                <Icon name="x" size={14} />
                            </button>
                        </div>
                    </div>
                ))}
            </div>
        </ToastContext.Provider>
    );
}

export function useToast() {
    const context = useContext(ToastContext);

    if (!context) {
        throw new Error('useToast must be used within ToastProvider');
    }

    return context;
}
