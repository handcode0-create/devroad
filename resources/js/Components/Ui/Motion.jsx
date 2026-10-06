import { AnimatePresence, LazyMotion, MotionConfig, m, useDragControls } from 'framer-motion';
import { useEffect, useRef, useState } from 'react';
import { reducedMotionPreferred } from '@/theme';

// Le moteur complet (glisser, layout partagé) est chargé APRÈS le premier
// affichage : le bundle initial reste léger pour les connexions mobiles.
const loadFeatures = () => import('./motionFeatures').then((module) => module.default);

export const spring = { type: 'spring', stiffness: 520, damping: 40, mass: 0.9 };
export const softSpring = { type: 'spring', stiffness: 380, damping: 34 };

export function MotionProvider({ children }) {
    const [reduce, setReduce] = useState(() => reducedMotionPreferred());

    // Suit le réglage « Réduire les animations » du panneau Apparence.
    useEffect(() => {
        const observer = new MutationObserver(() => setReduce(reducedMotionPreferred()));
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-motion'] });
        return () => observer.disconnect();
    }, []);

    return (
        <LazyMotion features={loadFeatures} strict>
            <MotionConfig reducedMotion={reduce ? 'always' : 'user'} transition={spring}>
                {children}
            </MotionConfig>
        </LazyMotion>
    );
}

/**
 * Panneau du bas (bottom sheet) des maquettes : fond qui s'assombrit, panneau
 * qui monte sur un ressort, fermeture par la poignée glissée vers le bas,
 * Échap ou un tap sur le fond.
 */
export function Sheet({ open, onClose, label, labelledBy, className = '', desktopCentered = false, children }) {
    const dragControls = useDragControls();
    const panelRef = useRef(null);

    useEffect(() => {
        if (!open) return undefined;
        const previous = document.activeElement;
        const onKey = (event) => { if (event.key === 'Escape') onClose(); };
        const overflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', onKey);
        requestAnimationFrame(() => panelRef.current?.querySelector('[data-autofocus], button, a, input')?.focus());
        return () => {
            window.removeEventListener('keydown', onKey);
            document.body.style.overflow = overflow;
            previous?.focus?.();
        };
    }, [open]);

    return (
        <AnimatePresence>
            {open && (
                <div className={'fixed inset-0 z-[80] flex items-end justify-center ' + (desktopCentered ? 'lg:items-center lg:p-6' : '')} role="presentation">
                    <m.div
                        aria-hidden="true"
                        onClick={onClose}
                        className="absolute inset-0 bg-[var(--dr-scrim)]"
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.2 }}
                    />
                    <m.section
                        ref={panelRef}
                        role="dialog"
                        aria-modal="true"
                        aria-label={labelledBy ? undefined : label}
                        aria-labelledby={labelledBy}
                        initial={{ y: '100%' }}
                        animate={{ y: 0 }}
                        exit={{ y: '100%', transition: { duration: 0.22, ease: [0.4, 0, 1, 1] } }}
                        transition={spring}
                        drag="y"
                        dragListener={false}
                        dragControls={dragControls}
                        dragConstraints={{ top: 0, bottom: 0 }}
                        dragElastic={{ top: 0, bottom: 0.6 }}
                        onDragEnd={(_, info) => { if (info.offset.y > 110 || info.velocity.y > 600) onClose(); }}
                        className={'relative flex w-full max-w-[430px] flex-col rounded-t-[28px] border-t border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-5 pt-[10px] font-[\'Figtree\',system-ui,sans-serif] text-[var(--dr-text)] ' + className}
                    >
                        <span
                            onPointerDown={(event) => dragControls.start(event)}
                            aria-hidden="true"
                            className="-mx-5 -mt-[10px] flex h-[25px] shrink-0 cursor-grab touch-none items-center justify-center active:cursor-grabbing"
                        >
                            <span className="h-[5px] w-10 rounded-full bg-[var(--dr-border-2)]" />
                        </span>
                        {children}
                    </m.section>
                </div>
            )}
        </AnimatePresence>
    );
}

/** Interrupteur des maquettes : le bouton glisse sur un ressort. */
export function SwitchTrack({ on }) {
    return (
        <span aria-hidden="true" className={'flex h-7 w-[46px] shrink-0 items-center rounded-full p-[3px] transition-colors duration-200 ' + (on ? 'justify-end bg-[var(--dr-accent)]' : 'justify-start bg-[var(--dr-border-2)]')}>
            <m.span layout transition={softSpring} className="h-[22px] w-[22px] rounded-full bg-[#FFFFFF] shadow-[0_1px_3px_rgba(0,0,0,0.3)]" />
        </span>
    );
}

export { AnimatePresence, m };
