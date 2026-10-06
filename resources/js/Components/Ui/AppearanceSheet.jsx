import { useEffect, useState } from 'react';
import { setReducedMotion } from '@/theme';
import ThemePicker from '@/Components/Ui/ThemePicker';
import { Sheet, SwitchTrack, m } from '@/Components/Ui/Motion';

// Panneau « Apparence » — artboard « Mobile — Choisir son thème » (Claude Design).
export default function AppearanceSheet({ open, onClose, preference = 'nuit' }) {
    const [reduce, setReduce] = useState(false);

    useEffect(() => {
        if (open) setReduce(document.documentElement.dataset.motion === 'reduce');
    }, [open]);

    function toggleReduce() {
        const next = !reduce;
        setReduce(next);
        setReducedMotion(next);
    }

    return (
        <Sheet open={open} onClose={onClose} labelledBy="apparence-titre" desktopCentered className="gap-[18px] pb-[calc(28px+env(safe-area-inset-bottom))] lg:rounded-[28px] lg:border lg:pb-7">
            <div className="flex items-start gap-3">
                <div className="flex flex-1 flex-col gap-1">
                    <h2 id="apparence-titre" className="m-0 font-['Manrope',sans-serif] text-2xl font-extrabold tracking-[-0.02em]">Apparence</h2>
                    <span className="text-sm leading-[1.45] text-[var(--dr-text-2)]">Le thème s’applique tout de suite et suit ton compte sur tous tes appareils.</span>
                </div>
                <m.button data-autofocus whileTap={{ scale: 0.9 }} type="button" onClick={onClose} aria-label="Fermer" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--dr-field)] text-[var(--dr-text-2)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </m.button>
            </div>

            <ThemePicker preference={preference} />

            <m.button whileTap={{ scale: 0.98 }} type="button" role="switch" aria-checked={reduce} onClick={toggleReduce} className="flex items-center gap-3 border-t border-[var(--dr-border)] bg-transparent pt-3.5 text-left text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                <span className="flex flex-1 flex-col gap-0.5">
                    <span className="text-[15px] font-semibold">Réduire les animations</span>
                    <span className="text-[13px] text-[var(--dr-text-3)]">Moins de mouvement, transitions instantanées.</span>
                </span>
                <SwitchTrack on={reduce} />
            </m.button>

            <m.button whileTap={{ scale: 0.97 }} type="button" onClick={onClose} className="flex h-[52px] items-center justify-center rounded-2xl bg-[var(--dr-accent)] text-base font-bold text-[var(--dr-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                Terminé
            </m.button>
        </Sheet>
    );
}
