import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { THEMES, applyTheme, setReducedMotion } from '@/theme';
import { Sheet, SwitchTrack, m, softSpring } from '@/Components/Ui/Motion';

// Panneau « Apparence » — artboard « Mobile — Choisir son thème » (Claude Design).
export default function AppearanceSheet({ open, onClose, preference = 'nuit' }) {
    const [current, setCurrent] = useState(preference);
    const [reduce, setReduce] = useState(false);

    useEffect(() => setCurrent(preference), [preference]);
    useEffect(() => {
        if (open) setReduce(document.documentElement.dataset.motion === 'reduce');
    }, [open]);

    const auto = current === 'auto';

    function choose(next) {
        const previous = current;
        setCurrent(next);
        applyTheme(next);
        router.patch('/profile/theme', { theme: next }, {
            preserveScroll: true,
            preserveState: true,
            onError: () => { setCurrent(previous); applyTheme(previous); },
        });
    }

    function toggleAuto() {
        choose(auto ? document.documentElement.dataset.uiTheme || 'nuit' : 'auto');
    }

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

            <m.button whileTap={{ scale: 0.98 }} type="button" role="switch" aria-checked={auto} onClick={toggleAuto} className="flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] p-3.5 text-left text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-[11px] bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round"><rect x="5" y="2" width="14" height="20" rx="3" /><path d="M11 18h2" /></svg>
                </span>
                <span className="flex flex-1 flex-col gap-0.5">
                    <span className="text-[15px] font-semibold">Suivre mon téléphone</span>
                    <span className="text-[13px] text-[var(--dr-text-3)]">{auto ? 'Actif : Nuit le soir, Clair le jour.' : 'Nuit le soir, Clair le jour.'}</span>
                </span>
                <SwitchTrack on={auto} />
            </m.button>

            <div role="radiogroup" aria-label="Thème" className="grid grid-cols-3 gap-2.5">
                {THEMES.map((theme, index) => {
                    const active = !auto && current === theme.key;
                    return (
                        <m.button
                            key={theme.key}
                            type="button"
                            role="radio"
                            aria-checked={active}
                            onClick={() => choose(theme.key)}
                            initial={{ opacity: 0, y: 12 }}
                            animate={{ opacity: 1, y: 0, transition: { ...softSpring, delay: 0.06 + index * 0.035 } }}
                            whileTap={{ scale: 0.95 }}
                            className="relative flex flex-col gap-2 rounded-2xl border-2 border-transparent bg-transparent p-1 text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]"
                        >
                            {active && <m.span layoutId="appearance-ring" transition={softSpring} aria-hidden="true" className="pointer-events-none absolute -inset-[2px] rounded-2xl border-2 border-[var(--dr-accent)]" />}
                            <span className="flex h-[74px] flex-col gap-[5px] rounded-[11px] border p-2" style={{ background: theme.bg, borderColor: theme.border }}>
                                <span className="flex h-[22px] items-center gap-1 rounded-md px-1.5" style={{ background: theme.surface }}>
                                    <span className="h-1 w-[22px] rounded-sm" style={{ background: theme.text }} />
                                </span>
                                <span className="h-1 w-[70%] rounded-sm" style={{ background: theme.text2 }} />
                                <span className="mt-auto h-3 w-[26px] self-end rounded bg-[#FF6A00]" />
                            </span>
                            <span className={'flex items-center justify-center gap-[5px] pb-1 text-[13px] ' + (active ? 'font-bold' : 'font-medium')}>
                                {active && <m.svg initial={{ scale: 0 }} animate={{ scale: 1 }} transition={softSpring} width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--dr-accent-text)" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12l5 5L20 7" /></m.svg>}
                                {theme.name}
                            </span>
                        </m.button>
                    );
                })}
            </div>

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
