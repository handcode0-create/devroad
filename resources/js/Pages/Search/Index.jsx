import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import Pagination from '@/Components/Ui/Pagination';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { ICON, PageHead, Segmented, Svg, TechTile, monogram, ui } from '@/Components/Ui/Design';

const STATUS_LABELS = {
    draft: 'Brouillon', active: 'En cours', completed: 'Terminée', archived: 'Archivée',
    todo: 'À faire', in_progress: 'En cours', blocked: 'Bloquée',
};
const RECENT_KEY = 'devroad:search:recent';

function readRecent() {
    try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]').slice(0, 6); } catch { return []; }
}
function rememberSearch(term) {
    try {
        const next = [term, ...readRecent().filter((item) => item.toLowerCase() !== term.toLowerCase())].slice(0, 6);
        localStorage.setItem(RECENT_KEY, JSON.stringify(next));
    } catch {}
}

/** Met en évidence les mots recherchés dans un texte. */
function Highlight({ text, query }) {
    const value = String(text ?? '');
    const words = String(query ?? '').trim().split(/\s+/).filter((word) => word.length >= 2).map((word) => word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    if (!words.length) return value;
    const parts = value.split(new RegExp('(' + words.join('|') + ')', 'gi'));
    return parts.map((part, index) => index % 2 === 1
        ? <mark key={index} className="rounded-[4px] bg-[var(--dr-accent-soft)] px-0.5 text-[var(--dr-accent-text)]">{part}</mark>
        : part);
}

// Recherche : saisie instantanée (dès 2 caractères), filtres par type, résultats surlignés,
// recherches récentes mémorisées sur l'appareil.
export default function Index({ query = '', type = 'all', results = null, counts = null }) {
    const [term, setTerm] = useState(query ?? '');
    const [loading, setLoading] = useState(false);
    const [recent, setRecent] = useState([]);
    const inputRef = useRef(null);
    const timer = useRef(null);

    useEffect(() => setRecent(readRecent()), []);
    useEffect(() => setTerm(query ?? ''), [query]);

    function run(value, nextType = type) {
        const q = value.trim();
        if (q.length < 2) return;
        const input = inputRef.current;
        const hadFocus = document.activeElement === input;
        router.get('/search', { q, type: nextType }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setLoading(true),
            onFinish: () => {
                setLoading(false);
                // Garde le clavier ouvert et le curseur en fin de saisie pendant la recherche instantanée.
                if (hadFocus && input && document.activeElement !== input) {
                    input.focus({ preventScroll: true });
                    const end = input.value.length;
                    input.setSelectionRange?.(end, end);
                }
            },
        });
    }

    // Saisie instantanée, sans attendre « Entrée ».
    function onChange(value) {
        setTerm(value);
        clearTimeout(timer.current);
        if (value.trim().length >= 2 && value.trim() !== query) timer.current = setTimeout(() => run(value), 350);
    }

    function submit(event) {
        event.preventDefault();
        clearTimeout(timer.current);
        if (term.trim().length >= 2) rememberSearch(term.trim());
        run(term);
    }

    function clear() {
        setTerm('');
        clearTimeout(timer.current);
        router.get('/search', {}, { preserveState: true, replace: true });
        inputRef.current?.focus();
    }

    function forgetRecent() {
        try { localStorage.removeItem(RECENT_KEY); } catch {}
        setRecent([]);
    }

    const searched = results !== null;
    const total = counts ? counts.roadmaps + counts.memos + counts.steps : 0;
    const tabs = [
        { key: 'all', label: 'Tout', count: total },
        { key: 'roadmaps', label: 'Parcours', count: counts?.roadmaps },
        { key: 'memos', label: 'Fiches', count: counts?.memos },
        { key: 'steps', label: 'Étapes', count: counts?.steps },
    ].map((tab) => ({ ...tab, href: `/search?q=${encodeURIComponent(query)}&type=${tab.key}` }));

    return (
        <AppLayout>
            <Head title="Recherche" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-5 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)]">
                <PageHead title="Rechercher" subtitle="Dans tes parcours, tes fiches mémo et tes étapes." />

                <form onSubmit={submit} role="search" className="m-0">
                    <label className="flex h-[52px] items-center gap-2.5 rounded-[14px] border border-[var(--dr-border)] bg-[var(--dr-field)] px-4 text-[var(--dr-text-3)] transition-colors focus-within:border-[var(--dr-accent)]">
                        {loading
                            ? <span aria-hidden="true" className="h-[18px] w-[18px] animate-spin rounded-full border-2 border-[var(--dr-border-2)] border-t-[var(--dr-accent)]" />
                            : <Svg d={ICON.search} size={18} />}
                        <span className="sr-only">Rechercher</span>
                        <input
                            ref={inputRef}
                            data-dr-native
                            type="search"
                            value={term}
                            onChange={(event) => onChange(event.target.value)}
                            placeholder="Laravel, routage, git stash…"
                            autoFocus
                            enterKeyHint="search"
                            className="min-w-0 flex-1 appearance-none border-0 bg-transparent p-0 text-base text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0 [&::-webkit-search-cancel-button]:hidden"
                        />
                        {term && <button type="button" onClick={clear} aria-label="Effacer la recherche" className="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-[var(--dr-surface)] hover:text-[var(--dr-text)]"><Svg d="M6 6l12 12M18 6L6 18" size={15} stroke={2.2} /></button>}
                    </label>
                    {term.trim().length === 1 && <p className="m-0 mt-2 text-sm text-[var(--dr-text-3)]">Encore un caractère…</p>}
                </form>

                {searched && <Segmented label="Type de résultat" items={tabs} value={type} className="w-fit max-w-full" />}

                {!searched && (
                    recent.length > 0 ? (
                        <section aria-labelledby="recentes" className="flex flex-col gap-3">
                            <div className="flex items-center justify-between">
                                <h2 id="recentes" className={ui.eyebrow + ' m-0'}>Recherches récentes</h2>
                                <button type="button" onClick={forgetRecent} className="text-[13px] font-semibold text-[var(--dr-text-3)] hover:text-[var(--dr-text)]">Effacer</button>
                            </div>
                            <ul className="m-0 flex list-none flex-wrap gap-2 p-0">
                                {recent.map((item) => (
                                    <li key={item}>
                                        <button type="button" onClick={() => { setTerm(item); run(item); }} className={'inline-flex h-9 items-center gap-2 rounded-full border border-[var(--dr-border)] bg-[var(--dr-surface)] px-3.5 text-sm text-[var(--dr-text)] hover:border-[var(--dr-border-2)] ' + ui.focus}>
                                            <Svg d={ICON.clock} size={14} className="text-[var(--dr-text-3)]" />{item}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </section>
                    ) : (
                        <p className="m-0 rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-5 py-8 text-center text-sm text-[var(--dr-text-2)]">Tape au moins 2 caractères : les résultats s’affichent pendant que tu écris.</p>
                    )
                )}

                {searched && total === 0 && (
                    <div className="rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-5 py-10 text-center">
                        <h2 className="m-0 text-lg font-semibold">Aucun résultat pour « {query} »</h2>
                        <p className="mx-auto mb-0 mt-1 max-w-sm text-sm leading-6 text-[var(--dr-text-2)]">Essaie un autre mot, une orthographe plus courte, ou cherche dans tes fiches avec un tag.</p>
                    </div>
                )}

                <AnimatePresence initial={false}>
                    {searched && results.roadmaps?.data.length > 0 && (
                        <Section key="r" title="Parcours" count={counts.roadmaps}>
                            {results.roadmaps.data.map((roadmap) => (
                                <Result key={roadmap.id} href={`/roadmaps/${roadmap.id}`} lead={<TechTile technology={roadmap.technology} title={roadmap.title} size={40} />} title={<Highlight text={roadmap.title} query={query} />} meta={`${STATUS_LABELS[roadmap.status] ?? roadmap.status} · ${roadmap.progress} % · ${roadmap.steps_count} étapes`} />
                            ))}
                            <Pagination links={results.roadmaps.links} />
                        </Section>
                    )}
                    {searched && results.memos?.data.length > 0 && (
                        <Section key="m" title="Fiches mémo" count={counts.memos}>
                            {results.memos.data.map((memo) => (
                                <Result
                                    key={memo.id}
                                    href={`/memos/${memo.id}`}
                                    lead={<span aria-hidden="true" className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--dr-field)] font-['JetBrains_Mono',ui-monospace,monospace] text-[15px] text-[var(--dr-accent-text)]">{monogram(memo.title)}</span>}
                                    title={<Highlight text={memo.title} query={query} />}
                                    body={memo.excerpt && <Highlight text={memo.excerpt} query={query} />}
                                    meta={memo.tags?.length ? memo.tags.map((tag) => '#' + tag.name).join('  ') : null}
                                />
                            ))}
                            <Pagination links={results.memos.links} />
                        </Section>
                    )}
                    {searched && results.steps?.data.length > 0 && (
                        <Section key="s" title="Étapes" count={counts.steps}>
                            {results.steps.data.map((step) => (
                                <Result key={step.id} href={`/steps/${step.id}`} lead={<span aria-hidden="true" className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--dr-field)] text-[var(--dr-accent-text)]"><Svg d={ICON.book} size={18} /></span>} title={<Highlight text={step.title} query={query} />} meta={`${step.roadmap_title} · ${STATUS_LABELS[step.status] ?? step.status}`} />
                            ))}
                            <Pagination links={results.steps.links} />
                        </Section>
                    )}
                </AnimatePresence>
            </div>
        </AppLayout>
    );
}

function Section({ title, count, children }) {
    return (
        <m.section initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0 }} transition={softSpring} aria-label={title} className="flex flex-col gap-2.5">
            <h2 className="m-0 flex items-center gap-2 text-base font-semibold">{title}<span className="text-sm font-normal tabular-nums text-[var(--dr-text-3)]">{count}</span></h2>
            {children}
        </m.section>
    );
}

function Result({ href, lead, title, body, meta }) {
    return (
        <Link href={href} onClick={() => { const q = new URLSearchParams(window.location.search).get('q'); if (q) rememberSearch(q); }} className={'group flex items-center gap-3.5 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3.5 transition-colors hover:border-[var(--dr-border-2)] ' + ui.focus}>
            {lead}
            <span className="flex min-w-0 flex-1 flex-col gap-0.5">
                <span className="truncate text-[15px] font-semibold text-[var(--dr-text)]">{title}</span>
                {body && <span className="line-clamp-2 text-sm leading-5 text-[var(--dr-text-2)]">{body}</span>}
                {meta && <span className="truncate text-xs text-[var(--dr-text-3)]">{meta}</span>}
            </span>
            <Svg d={ICON.chevronRight} size={17} className="shrink-0 text-[var(--dr-text-3)] transition-transform group-hover:translate-x-0.5" />
        </Link>
    );
}
