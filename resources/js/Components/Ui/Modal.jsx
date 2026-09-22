import { Dialog, DialogPanel, Transition, TransitionChild } from '@headlessui/react';
import { X } from 'lucide-react';

export default function Modal({
    show,
    title,
    description,
    onClose,
    children,
    footer = null,
    maxWidth = 'max-w-md',
}) {
    return (
        <Transition show={show} leave="duration-150">
            <Dialog as="div" className="fixed inset-0 z-[80] flex items-center justify-center px-4 py-6" onClose={onClose}>
                <TransitionChild enter="ease-out duration-200" enterFrom="opacity-0" enterTo="opacity-100" leave="ease-in duration-150" leaveFrom="opacity-100" leaveTo="opacity-0">
                    <div className="absolute inset-0 bg-[#02060D]/80 backdrop-blur-sm theme-modal-overlay" />
                </TransitionChild>
                <TransitionChild enter="ease-out duration-200" enterFrom="opacity-0 translate-y-3 scale-95" enterTo="opacity-100 translate-y-0 scale-100" leave="ease-in duration-150" leaveFrom="opacity-100 translate-y-0 scale-100" leaveTo="opacity-0 translate-y-3 scale-95">
                    <DialogPanel className={'relative w-full ' + maxWidth + ' overflow-hidden rounded-3xl border border-white/[0.08] bg-[#0D1725] shadow-[0_28px_80px_rgba(0,0,0,0.55)]'}>
                        <div className="flex items-start justify-between gap-4 border-b border-white/[0.06] px-5 py-5">
                            <div className="min-w-0">
                                <h2 className="text-base font-bold text-white">{title}</h2>
                                {description && <p className="mt-1 text-xs leading-5 text-slate-500">{description}</p>}
                            </div>
                            <button type="button" onClick={onClose} className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-slate-500 transition hover:bg-white/[0.04] hover:text-white" aria-label="Fermer">
                                <X size={18} />
                            </button>
                        </div>
                        <div className="px-5 py-5">{children}</div>
                        {footer && <div className="border-t border-white/[0.06] px-5 py-4">{footer}</div>}
                    </DialogPanel>
                </TransitionChild>
            </Dialog>
        </Transition>
    );
}
