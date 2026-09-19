import { CheckCircle2, CircleAlert, Info, X } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';

const TYPES = {
    success: {
        icon: CheckCircle2,
        label: 'Succès',
        className: 'border-emerald-400/15 bg-emerald-400/[0.08] text-emerald-300',
    },
    error: {
        icon: CircleAlert,
        label: 'Erreur',
        className: 'border-red-400/15 bg-red-400/[0.08] text-red-300',
    },
    info: {
        icon: Info,
        label: 'Information',
        className: 'border-sky-400/15 bg-sky-400/[0.08] text-sky-300',
    },
};

export default function ToastViewport() {
    const { flash = {}, errors = {} } = usePage().props;
    const [toasts, setToasts] = useState([]);

    const errorMessage = useMemo(() => {
        const first = Object.values(errors ?? {})[0];

        if (!first) return null;

        return Array.isArray(first) ? first[0] : first;
    }, [errors]);

    useEffect(() => {
        const incoming = [];

        if (flash?.success) {
            incoming.push({
                id: 'success-' + flash.success,
                type: 'success',
                message: flash.success,
            });
        }

        if (flash?.error) {
            incoming.push({
                id: 'error-' + flash.error,
                type: 'error',
                message: flash.error,
            });
        }

        if (errorMessage) {
            incoming.push({
                id: 'validation-' + errorMessage,
                type: 'error',
                message: errorMessage,
            });
        }

        if (incoming.length > 0) {
            setToasts((current) => {
                const existing = new Set(current.map((toast) => toast.id));
                return [
                    ...current,
                    ...incoming.filter((toast) => !existing.has(toast.id)),
                ].slice(-3);
            });
        }
    }, [flash?.success, flash?.error, errorMessage]);

    useEffect(() => {
        if (toasts.length === 0) return undefined;

        const timers = toasts.map((toast) =>
            window.setTimeout(() => {
                setToasts((current) =>
                    current.filter((item) => item.id !== toast.id),
                );
            }, 5000),
        );

        return () => timers.forEach((timer) => window.clearTimeout(timer));
    }, [toasts]);

    function dismiss(id) {
        setToasts((current) => current.filter((toast) => toast.id !== id));
    }

    return (
        <div
            className="pointer-events-none fixed right-4 top-4 z-[90] flex w-[min(92vw,380px)] flex-col gap-3"
            aria-live="polite"
            aria-atomic="true"
        >
            {toasts.map((toast) => (
                <Toast
                    key={toast.id}
                    {...toast}
                    onDismiss={() => dismiss(toast.id)}
                />
            ))}
        </div>
    );
}

function Toast({ type, message, onDismiss }) {
    const config = TYPES[type] ?? TYPES.info;
    const Icon = config.icon;

    return (
        <div
            className={[
                'pointer-events-auto flex items-start gap-3 rounded-2xl border p-4 shadow-[0_18px_45px_rgba(0,0,0,0.35)] backdrop-blur-xl',
                config.className,
            ].join(' ')}
        >
            <Icon size={18} className="mt-0.5 shrink-0" />

            <div className="min-w-0 flex-1">
                <p className="text-[10px] font-bold uppercase tracking-[0.14em] opacity-70">
                    {config.label}
                </p>

                <p className="mt-1 text-sm font-medium leading-5">
                    {message}
                </p>
            </div>

            <button
                type="button"
                onClick={onDismiss}
                className="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg opacity-60 transition hover:bg-white/[0.08] hover:opacity-100"
                aria-label="Fermer la notification"
            >
                <X size={15} />
            </button>
        </div>
    );
}
