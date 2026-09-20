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
                  icon: 'bg-red-500/10 text-red-400',
                  button: 'bg-red-500 text-white hover:bg-red-400',
              }
            : {
                  icon: 'bg-[#FF6A00]/10 text-[#FF8A3D]',
                  button: 'bg-[#FF6A00] text-[#08111F] hover:bg-[#ff781a]',
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
                    <DialogPanel className="relative w-full max-w-md overflow-hidden rounded-3xl border border-white/[0.08] bg-[#0D1725] theme-modal shadow-[0_28px_80px_rgba(0,0,0,0.55)]">
                        <div className="flex items-start justify-between gap-4 border-b border-white/[0.06] px-5 py-5 theme-modal-header">
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
                                    <h2 className="text-base font-bold text-white theme-modal-title">
                                        {title}
                                    </h2>

                                    <p className="mt-1 text-xs leading-5 text-slate-500 theme-modal-muted">
                                        {description}
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={onClose}
                                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 theme-modal-close transition hover:bg-white/[0.04] hover:text-white"
                                aria-label="Fermer"
                            >
                                <X size={18} />
                            </button>
                        </div>

                        {children && <div className="border-t border-white/[0.06] px-5 py-4 theme-modal-section">{children}</div>}

                        <div className="flex justify-end gap-2 px-5 py-4">
                            <button
                                type="button"
                                onClick={onClose}
                                disabled={processing}
                                className="rounded-xl border border-white/[0.08] bg-white/[0.03] theme-modal-action px-4 py-2.5 text-sm font-semibold text-slate-300 transition hover:bg-white/[0.06] hover:text-white disabled:opacity-50"
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
