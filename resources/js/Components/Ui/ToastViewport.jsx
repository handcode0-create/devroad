import { router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';

// Notifications : succès, erreur, information. Une notification peut porter une
// action « Annuler » (flash « undo » côté serveur) pour réparer une mauvaise manipulation.
const ICONS = {
    success: 'M5 12l5 5L20 7',
    error: 'M12 8v5M12 16h.01M12 3a9 9 0 100 18 9 9 0 000-18z',
    info: 'M12 11v5M12 8h.01M12 3a9 9 0 100 18 9 9 0 000-18z',
};

export default function ToastViewport() {
    const { flash = {}, errors = {} } = usePage().props;
    const [toasts, setToasts] = useState([]);
    const seq = useRef(0);

    const errorMessage = useMemo(() => {
        const first = Object.values(errors ?? {})[0];
        if (!first) return null;
        return Array.isArray(first) ? first[0] : first;
    }, [errors]);

    useEffect(() => {
        const incoming = [];
        const next = (type, message, extra = {}) => incoming.push({ id: type + '-' + (++seq.current), type, message, ...extra });

        if (flash?.success) next('success', flash.success, flash.undo ? { undo: flash.undo } : {});
        if (flash?.error) next('error', flash.error);
        if (errorMessage) next('error', errorMessage);

        if (incoming.length > 0) {
            setToasts((current) => {
                const messages = new Set(current.map((toast) => toast.type + toast.message));
                return [...current, ...incoming.filter((toast) => toast.undo || !messages.has(toast.type + toast.message))].slice(-3);
            });
        }
    }, [flash, errorMessage]);

    function dismiss(id) {
        setToasts((current) => current.filter((toast) => toast.id !== id));
    }

    return (
        <div className="pointer-events-none fixed inset-x-3 bottom-[calc(96px+env(safe-area-inset-bottom))] z-[90] flex flex-col items-center gap-2.5 lg:inset-x-auto lg:bottom-auto lg:right-4 lg:top-4 lg:w-[380px] lg:items-stretch" aria-live="polite" aria-atomic="false">
            <AnimatePresence initial={false}>
                {toasts.map((toast) => <Toast key={toast.id} {...toast} onDismiss={() => dismiss(toast.id)} />)}
            </AnimatePresence>
        </div>
    );
}

function Toast({ type, message, undo, onDismiss }) {
    const [busy, setBusy] = useState(false);
    const [paused, setPaused] = useState(false);
    const duration = undo ? 9000 : 5000;

    useEffect(() => {
        if (paused) return undefined;
        const timer = window.setTimeout(onDismiss, duration);
        return () => window.clearTimeout(timer);
    }, [paused]);

    function runUndo() {
        if (busy) return;
        setBusy(true);
        router.visit(undo.url, {
            method: undo.method ?? 'post',
            data: undo.data ?? {},
            preserveScroll: true,
            onFinish: onDismiss,
        });
    }

    const tone = type === 'error' ? 'text-[var(--dr-danger)]' : 'text-[var(--dr-accent-text)]';

    return (
        <m.div
            layout
            role={type === 'error' ? 'alert' : 'status'}
            initial={{ opacity: 0, y: 14, scale: 0.97 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, scale: 0.96, transition: { duration: 0.15 } }}
            transition={softSpring}
            onPointerEnter={() => setPaused(true)}
            onPointerLeave={() => setPaused(false)}
            onFocus={() => setPaused(true)}
            onBlur={() => setPaused(false)}
            className="pointer-events-auto relative flex w-full max-w-[420px] items-center gap-3 overflow-hidden rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] py-2.5 pl-3.5 pr-2 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] shadow-[0_24px_60px_-20px_rgba(0,0,0,0.55)]"
        >
            <span aria-hidden="true" className={'flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[var(--dr-field)] ' + tone}>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"><path d={ICONS[type] ?? ICONS.info} /></svg>
            </span>
            <p className="m-0 min-w-0 flex-1 py-1 text-sm font-medium leading-5">{message}</p>
            {undo && (
                <button type="button" onClick={runUndo} disabled={busy} className="h-9 shrink-0 rounded-[10px] px-3 text-sm font-bold text-[var(--dr-accent-text)] hover:bg-[var(--dr-accent-soft)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)] disabled:opacity-60">
                    {busy ? '…' : undo.label ?? 'Annuler'}
                </button>
            )}
            <button type="button" onClick={onDismiss} aria-label="Fermer la notification" className="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] text-[var(--dr-text-3)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
            {!paused && (
                <m.span aria-hidden="true" className="absolute inset-x-0 bottom-0 h-0.5 origin-left bg-[var(--dr-accent)] opacity-60" initial={{ scaleX: 1 }} animate={{ scaleX: 0 }} transition={{ duration: duration / 1000, ease: 'linear' }} />
            )}
        </m.div>
    );
}
