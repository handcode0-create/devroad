import { router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { THEMES, applyTheme, setReducedMotion } from '@/theme';

// Panneau « Apparence » — artboard « Mobile — Choisir son thème » (Claude Design).
export default function AppearanceSheet({ open, onClose, preference = 'nuit' }) {
    const [current, setCurrent] = useState(preference);
    const [reduce, setReduce] = useState(false);
    const closeRef = useRef(null);

    useEffect(() => setCurrent(preference), [preference]);

    useEffect(() => {
        if (!open) return undefined;
        setReduce(document.documentElement.dataset.motion === 'reduce');
        closeRef.current?.focus();
        const onKey = (event) => { if (event.key === 'Escape') onClose(); };
        const overflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        window.addEventListener('keydown', onKey);
        return () => {
            window.removeEventListener('keydown', onKey);
            document.body.style.overflow = overflow;
        };
    }, [open]);

    if (!open) return null;

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
        <div className="fixed inset-0 z-[80] flex items-end justify-center lg:items-center lg:p-6" role="presentation">
            <div aria-hidden="true" onClick={onClose} className="absolute inset-0 bg-[var(--dr-scrim)]" />

            <section
                role="dialog"
                aria-modal="true"
                aria-labelledby="apparence-titre"
                className="relative flex w-full max-w-[430px] flex-col gap-[18px] rounded-t-[28px] border-t border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-5 pb-[calc(28px+env(safe-area-inset-bottom))] pt-[10px] font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] transition-[background-color] duration-[240ms] lg:rounded-[28px] lg:border lg:pb-7"
            >
                <span aria-hidden="true" className="h-[5px] w-10 self-center rounded-full bg-[var(--dr-border-2)]" />

                <div className="flex items-start gap-3">
                    <div className="flex flex-1 flex-col gap-1">
                        <h2 id="apparence-titre" className="m-0 font-['Manrope',sans-serif] text-2xl font-extrabold tracking-[-0.02em]">Apparence</h2>
                        <span className="text-sm leading-[1.45] text-[var(--dr-text-2)]">Le thème s’applique tout de suite et suit ton compte sur tous tes appareils.</span>
                    </div>
                    <button ref={closeRef} type="button" onClick={onClose} aria-label="Fermer" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[var(--dr-field)] text-[var(--dr-text-2)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                    </button>
                </div>

                <button type="button" role="switch" aria-checked={auto} onClick={toggleAuto} className="flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] p-3.5 text-left text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                    <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-[11px] bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round"><rect x="5" y="2" width="14" height="20" rx="3" /><path d="M11 18h2" /></svg>
                    </span>
                    <span className="flex flex-1 flex-col gap-0.5">
                        <span className="text-[15px] font-semibold">Suivre mon téléphone</span>
                        <span className="text-[13px] text-[var(--dr-text-3)]">{auto ? 'Actif : Nuit le soir, Clair le jour.' : 'Nuit le soir, Clair le jour.'}</span>
                    </span>
                    <Track on={auto} />
                </button>

                <div role="radiogroup" aria-label="Thème" className="grid grid-cols-3 gap-2.5">
                    {THEMES.map((theme) => {
                        const active = !auto && current === theme.key;
                        return (
                            <button
                                key={theme.key}
                                type="button"
                                role="radio"
                                aria-checked={active}
                                onClick={() => choose(theme.key)}
                                className={'flex flex-col gap-2 rounded-2xl border-2 bg-transparent p-1 text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)] ' + (active ? 'border-[var(--dr-accent)]' : 'border-transparent')}
                            >
                                <span className="flex h-[74px] flex-col gap-[5px] rounded-[11px] border p-2" style={{ background: theme.bg, borderColor: theme.border }}>
                                    <span className="flex h-[22px] items-center gap-1 rounded-md px-1.5" style={{ background: theme.surface }}>
                                        <span className="h-1 w-[22px] rounded-sm" style={{ background: theme.text }} />
                                    </span>
                                    <span className="h-1 w-[70%] rounded-sm" style={{ background: theme.text2 }} />
                                    <span className="mt-auto h-3 w-[26px] self-end rounded bg-[#FF6A00]" />
                                </span>
                                <span className={'flex items-center justify-center gap-[5px] pb-1 text-[13px] ' + (active ? 'font-bold' : 'font-medium')}>
                                    {active && <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="var(--dr-accent-text)" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12l5 5L20 7" /></svg>}
                                    {theme.name}
                                </span>
                            </button>
                        );
                    })}
                </div>

                <button type="button" role="switch" aria-checked={reduce} onClick={toggleReduce} className="flex items-center gap-3 border-t border-[var(--dr-border)] bg-transparent pt-3.5 text-left text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]">
                    <span className="flex flex-1 flex-col gap-0.5">
                        <span className="text-[15px] font-semibold">Réduire les animations</span>
                        <span className="text-[13px] text-[var(--dr-text-3)]">Moins de mouvement, transitions instantanées.</span>
                    </span>
                    <Track on={reduce} />
                </button>

                <button type="button" onClick={onClose} className="flex h-[52px] items-center justify-center rounded-2xl bg-[var(--dr-accent)] text-base font-bold text-[var(--dr-ink)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--dr-accent)]">
                    Terminé
                </button>
            </section>
        </div>
    );
}

function Track({ on }) {
    return (
        <span aria-hidden="true" className={'flex h-7 w-[46px] shrink-0 items-center rounded-full p-[3px] transition-colors duration-200 ' + (on ? 'justify-end bg-[var(--dr-accent)]' : 'justify-start bg-[var(--dr-border-2)]')}>
            <span className="h-[22px] w-[22px] rounded-full bg-[#FFFFFF] shadow-[0_1px_3px_rgba(0,0,0,0.3)]" />
        </span>
    );
}
