import clsx from 'clsx';
import * as LucideIcons from 'lucide-react';

function toPascalCase(name) {
    return name
        .split('-')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join('');
}

export function Icon({ name, size = 20, className, label, ...props }) {
    const Component = LucideIcons[toPascalCase(name)];

    if (!Component) {
        return null;
    }

    return (
        <Component
            size={size}
            className={clsx('shrink-0', className)}
            aria-hidden={label ? undefined : true}
            aria-label={label}
            {...props}
        />
    );
}
