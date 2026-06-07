import clsx from 'clsx';
import { useEffect, useId, useRef, useState } from 'react';
import { Icon } from './Icon';

export function Dropdown({ label, items, className }) {
    const [open, setOpen] = useState(false);
    const menuId = useId();
    const containerRef = useRef(null);

    useEffect(() => {
        function handleClickOutside(event) {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setOpen(false);
            }
        }

        document.addEventListener('mousedown', handleClickOutside);

        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div ref={containerRef} className={clsx('relative inline-block text-left', className)}>
            <button
                type="button"
                className="ds-btn ds-btn-md ds-btn-secondary"
                aria-haspopup="menu"
                aria-expanded={open}
                aria-controls={menuId}
                onClick={() => setOpen((value) => !value)}
            >
                {label}
                <Icon name="chevron-down" size={16} className={clsx('transition-transform', open && 'rotate-180')} />
            </button>
            {open && (
                <ul
                    id={menuId}
                    role="menu"
                    className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                >
                    {items.map((item) => (
                        <li key={item.id} role="none">
                            <button
                                type="button"
                                role="menuitem"
                                className="ds-focus block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50"
                                onClick={() => {
                                    item.onSelect?.();
                                    setOpen(false);
                                }}
                            >
                                {item.label}
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
