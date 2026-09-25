import { Loader2 } from 'lucide-react';

export default function LoadingSpinner({ size = 18, label = 'Chargement...', className = '' }) {
    return (
        <span className={`inline-flex items-center gap-2 ${className}`} role="status" aria-live="polite">
            <Loader2
                size={size}
                className="shrink-0 animate-spin motion-reduce:animate-none"
                aria-hidden="true"
            />
            {label && <span>{label}</span>}
        </span>
    );
}
