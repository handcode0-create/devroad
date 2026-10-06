import { router, usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import { AnimatePresence, SwitchTrack, m, softSpring } from '@/Components/Ui/Motion';
import { THEMES, chooseTheme } from '@/theme';

// Barre du haut desktop — artboard « Desktop — Fiches » (Claude Design) :
// recherche globale (Ctrl/Cmd+K) et menu de thème.
export default function DesktopTopBar({ preference, resolved }) {
    const { url } = usePage();
    const isSearchPage = url.startsWith('/search');
    const initialQuery = isSearchPage ? new URLSearchParams(url.split('?')[1] ?? '').get('q') ?? '' : '';
    const [query, setQuery] = useState(initialQuery);
    const inputRef = useRef(null);
    const isMac = typeof navigator !== 'undefined' && /Mac|iPhone|iPad/.test(navigator.platform);

    useEffect(() => setQuery(initialQuery), [url]);

    // Ctrl+K / Cmd+K : place le curseur dans la recherche depuis n'importe quelle page.
    useEffect(() => {
        const onKey = (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                inputRef.current?.focus();
                inputRef.current?.select();
            }
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, []);

    function submit(event) {
        event.preventDefault();
        const q = query.trim();
        router.get('/search', q ? { q, type: 'all' } : {});
    }

    return (
        <header className="sticky top-0 z-30 hidden border-b border-[var(--dr-border)] [background-color:color-mix(in_srgb,var(--dr-bg)_90%,transparent)] font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] backdrop-blur-xl lg:block">
            <div className="flex h-[76px] items-center gap-3 px-10">
                <form onSubmit={submit} role="search" className="m-0 flex max-w-[560px] flex-1">
                    <label className="flex h-11 w-full items-center gap-2.5 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-3.5 text-[var(--dr-text-3)] focus-within:border-[var(--dr-accent)]">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5" /></svg>
                        <input
                            ref={inputRef}
                            data-dr-native
                            type="search"
                            value={query}
                            onChange={(event) => setQuery(event.target.value)}
                            aria-label="Rechercher"
                            placeholder="Rechercher fiches, parcours, étapes…"
                            className="min-w-0 flex-1 appearance-none border-0 bg-transparent p-0 text-sm text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0 [&::-webkit-search-cancel-button]:hidden"
                        />
                        <kbd className="rounded-md border border-[var(--dr-border-2)] px-[7px] py-[3px] font-['JetBrains_Mono',ui-monospace,monospace] text-[11px] font-normal text-[var(--dr-text-3)]">{isMac ? '⌘ K' : 'Ctrl K'}</kbd>
                    </label>
                </form>
                <div className="ml-auto">
                    <ThemeMenu preference={preference} resolved={resolved} />
                </div>
            </div>
        </header>
    );
}

function ThemeMenu({ preference, resolved }) {
    const [open, setOpen] = useState(false);
    const [current, setCurrent] = useState(preference);
    const ref = useRef(null);
    const auto = current === 'auto';
    const shown = THEMES.find((theme) => theme.key === resolved) ?? THEMES[0];

    useEffect(() => setCurrent(preference), [preference]);

    useEffect(() => {
        if (!open) return undefined;
        const close = (event) => { if (!ref.current?.contains(event.target)) setOpen(false); };
        const onKey = (event) => { if (event.key === 'Escape') setOpen(false); };
        document.addEventListener('mousedown', close);
        window.addEventListener('keydown', onKey);
        return () => { document.removeEventListener('mousedown', close); window.removeEventListener('keydown', onKey); };
    }, [open]);

    function choose(next) {
        const previous = current;
        setCurrent(next);
        chooseTheme(next, previous, setCurrent);
    }

    return (
        <div ref={ref} className="relative">
            <m.button
                whileTap={{ scale: 0.97 }}
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-haspopup="menu"
                aria-expanded={open}
                aria-label={'Thème : ' + (auto ? 'Automatique' : shown.name)}
                className="flex h-11 items-center gap-2.5 rounded-xl border border-[var(--dr-border-2)] bg-[var(--dr-surface)] px-3.5 text-sm font-semibold text-[var(--dr-text)] focus-visible:outline focus-visible:outline-2 focus-visible:outline-[var(--dr-accent)]"
            >
                <span aria-hidden="true" className="h-4 w-4 rounded-full" style={{ background: shown.bg, boxShadow: 'inset 0 0 0 1px var(--dr-border-2), 6px 0 0 -2px #FF6A00' }} />
                {auto ? 'Automatique' : shown.name}
                <m.svg animate={{ rotate: open ? 180 : 0 }} transition={softSpring} width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M6 9l6 6 6-6" /></m.svg>
            </m.button>

            <AnimatePresence>
                {open && (
                    <m.div
                        role="menu"
                        aria-label="Thème"
                        initial={{ opacity: 0, y: -8, scale: 0.97 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: -8, scale: 0.97, transition: { duration: 0.12 } }}
                        transition={softSpring}
                        style={{ transformOrigin: 'top right' }}
                        className="absolute right-0 top-[52px] z-50 flex w-[300px] flex-col gap-0.5 rounded-2xl border border-[var(--dr-border-2)] bg-[var(--dr-surface-2)] p-2 shadow-[0_24px_60px_-20px_rgba(0,0,0,0.55)]"
                    >
                        <span className="px-2.5 pb-1.5 pt-2 text-[11px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">Apparence</span>
                        {THEMES.map((theme) => {
                            const active = !auto && current === theme.key;
                            return (
                                <button
                                    key={theme.key}
                                    type="button"
                                    role="menuitemradio"
                                    aria-checked={active}
                                    onClick={() => choose(theme.key)}
                                    className="relative flex h-12 items-center gap-3 rounded-[10px] px-2.5 text-left text-sm font-medium text-[var(--dr-text)] hover:bg-[var(--dr-field)]"
                                >
                                    {active && <m.span layoutId="theme-menu-active" transition={softSpring} aria-hidden="true" className="absolute inset-0 rounded-[10px] bg-[var(--dr-accent-soft)]" />}
                                    <span aria-hidden="true" className="relative flex h-6 w-[34px] items-end justify-end rounded-[7px] border p-[3px]" style={{ background: theme.bg, borderColor: theme.border }}>
                                        <span className="h-1.5 w-2.5 rounded-sm bg-[#FF6A00]" />
                                    </span>
                                    <span className="relative flex flex-1 flex-col">
                                        <span>{theme.name}</span>
                                        <span className="text-xs text-[var(--dr-text-3)]">{theme.hint}</span>
                                    </span>
                                    {active && <svg className="relative" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--dr-accent-text)" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true"><path d="M5 12l5 5L20 7" /></svg>}
                                </button>
                            );
                        })}
                        <div className="mx-1 my-1.5 h-px bg-[var(--dr-border)]" />
                        <button
                            type="button"
                            role="menuitemcheckbox"
                            aria-checked={auto}
                            onClick={() => choose(auto ? resolved : 'auto')}
                            className="flex h-11 items-center gap-3 rounded-[10px] px-2.5 text-left text-sm font-medium text-[var(--dr-text)] hover:bg-[var(--dr-field)]"
                        >
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="2" /><path d="M8 20h8M12 16v4" /></svg>
                            <span className="flex-1">Suivre le système</span>
                            <SwitchTrack on={auto} />
                        </button>
                    </m.div>
                )}
            </AnimatePresence>
        </div>
    );
}
