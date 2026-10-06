import { AlertTriangle, X } from 'lucide-react';
import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';

export default function ConfirmModal({
    show,
    title,
    description,
    confirmLabel = 'Confirmer',
    cancelLabel = 'Annuler',
    onConfirm,
    onClose,
    processing = false,
    tone = 'danger',
    children = null,
}) {
    const accent =
        tone === 'danger'
            ? {
                  icon: 'bg-red-500/10 text-[var(--dr-danger)]',
                  button: 'bg-red-600 text-white hover:bg-red-500',
              }
            : {
                  icon: 'bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]',
                  button: 'bg-[#FF6A00] text-[var(--dr-ink)] hover:bg-[#ff781a]',
              };

    return (
        <Transition show={show} leave="duration-150">
            <Dialog
                as="div"
                className="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6"
                onClose={onClose}
            >
                <TransitionChild
                    enter="ease-out duration-200"
                    enterFrom="opacity-0"
                    enterTo="opacity-100"
                    leave="ease-in duration-150"
                    leaveFrom="opacity-100"
                    leaveTo="opacity-0"
                >
                    <div className="absolute inset-0 bg-[#02060D]/80 backdrop-blur-sm theme-modal-overlay" />
                </TransitionChild>

                <TransitionChild
                    enter="ease-out duration-200"
                    enterFrom="opacity-0 translate-y-3 scale-95"
                    enterTo="opacity-100 translate-y-0 scale-100"
                    leave="ease-in duration-150"
                    leaveFrom="opacity-100 translate-y-0 scale-100"
                    leaveTo="opacity-0 translate-y-3 scale-95"
                >
                    <DialogPanel className="relative w-full max-w-md overflow-hidden rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] theme-modal shadow-[0_28px_80px_rgba(0,0,0,0.55)]">
                        <div className="flex items-start justify-between gap-4 border-b border-[var(--dr-border)] px-5 py-5 theme-modal-header">
                            <div className="flex items-start gap-3">
                                <div
                                    className={[
                                        'flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl',
                                        accent.icon,
                                    ].join(' ')}
                                >
                                    <AlertTriangle size={20} />
                                </div>

                                <div>
                                    <h2 className="text-base font-bold text-[var(--dr-text)] theme-modal-title">
                                        {title}
                                    </h2>

                                    <p className="mt-1 text-xs leading-5 text-[var(--dr-text-3)] theme-modal-muted">
                                        {description}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={onClose}
                                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-[var(--dr-text-3)] theme-modal-close transition hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]"
                                aria-label="Fermer"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {children && <div className="border-t border-[var(--dr-border)] px-5 py-4 theme-modal-section">{children}</div>}

                        <div className="flex justify-end gap-2 px-5 py-4">
                            <button
                                type="button"
                                onClick={onClose}
                                disabled={processing}
                                className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-hover)] theme-modal-action px-4 py-2.5 text-sm font-semibold text-[var(--dr-text-2)] transition hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)] disabled:opacity-50"
                            >
                                {cancelLabel}
                            </button>

                            <button
                                type="button"
                                onClick={onConfirm}
                                disabled={processing}
                                className={[
                                    'rounded-xl px-4 py-2.5 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-50',
                                    accent.button,
                                ].join(' ')}
                            >
                                {processing ? 'Traitement...' : confirmLabel}
                            </button>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </Dialog>
        </Transition>
    );
}
