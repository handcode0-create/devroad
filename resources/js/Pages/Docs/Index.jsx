import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { ICON, MotionLink, PageHead, Segmented, Svg, TechTile, ui } from '@/Components/Ui/Design';
import { readRecentDocs } from '@/Components/Docs/recent';
import { followInternalLink } from '@/Components/Docs/links';

// Documentation : recherche classique (gratuite, toujours disponible) et, si l'utilisateur
// a ajouté sa clé, « Demander à l'IA » qui répond à partir des docs avec les sources.
export default function Index({ query = '', source = null, sources = [], results = null, browse = null, ai = {}, locale = 'fr' }) {
    const ready = sources.filter((item) => item.ready);
    const [mode, setMode] = useState(() => (typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('mode') === 'ia' ? 'ai' : 'search'));
    const [term, setTerm] = useState(query);
    const [loading, setLoading] = useState(false);
    const [recent, setRecent] = useState([]);
    const inputRef = useRef(null);
    const timer = useRef(null);

    useEffect(() => setTerm(query), [query]);
    useEffect(() => setRecent(readRecentDocs()), []);

    function visit(params) {
        const input = inputRef.current;
        const hadFocus = document.activeElement === input;
        router.get('/docs', params, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onStart: () => setLoading(true),
            onFinish: () => {
                setLoading(false);
                if (hadFocus && input && document.activeElement !== input) {
                    input.focus({ preventScroll: true });
                    input.setSelectionRange?.(input.value.length, input.value.length);
                }
            },
        });
    }

    function onChange(value) {
        setTerm(value);
        clearTimeout(timer.current);
        const q = value.trim();
        if (q.length >= 2 && q !== query) timer.current = setTimeout(() => visit({ q, source: source ?? undefined }), 300);
        if (q.length === 0 && query) timer.current = setTimeout(() => visit({ source: source ?? undefined }), 200);
    }

    function chooseSource(key) {
        visit({ q: term.trim() || undefined, source: key ?? undefined });
    }

    const chips = [{ key: null, name: 'Toutes' }, ...ready];

    return (
        <AppLayout>
            <Head title="Documentation" />
            <div className="mx-auto flex w-full max-w-4xl flex-col gap-5 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)]">
                <PageHead title="Documentation" subtitle="Les docs officielles de tes technologies, sans quitter DevRoad." />

                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Segmented
                        label="Mode"
                        value={mode}
                        onChange={setMode}
                        className="w-fit"
                        items={[{ key: 'search', label: 'Rechercher' }, { key: 'ai', label: ai.enabled ? 'Demander à l’IA' : 'Demander à l’IA ✦' }]}
                    />
                    <LocaleSwitch locale={locale} />
                </div>

                {ready.length === 0 ? (
                    <NotReady />
                ) : (
                    <>
                        {/* Filtre par documentation */}
                        <div role="group" aria-label="Documentations" className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 dr-scrollbar-none sm:mx-0 sm:flex-wrap sm:px-0">
                            {chips.map((chip) => {
                                const active = (source ?? null) === chip.key;
                                return (
                                    <button key={chip.key ?? 'all'} type="button" aria-pressed={active} onClick={() => chooseSource(chip.key)} className={'inline-flex h-9 shrink-0 items-center gap-2 rounded-full border px-3.5 text-sm transition-colors ' + ui.focus + ' ' + (active ? 'border-transparent bg-[var(--dr-text)] font-semibold text-[var(--dr-bg)]' : 'border-[var(--dr-border)] bg-[var(--dr-surface)] text-[var(--dr-text-2)] hover:text-[var(--dr-text)]')}>
                                        {chip.key && <TechTile technology={chip.technology} title={chip.name} size={20} />}
                                        {chip.name}
                                        {chip.key && chip.french && <span className="rounded-md bg-[var(--dr-accent-soft)] px-1.5 text-[10px] font-bold tracking-wide text-[var(--dr-accent-text)]" title="Version française officielle">FR</span>}
                                    </button>
                                );
                            })}
                        </div>

                        <AnimatePresence mode="wait" initial={false}>
                            {mode === 'search' ? (
                                <m.div key="search" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -6 }} transition={softSpring} className="flex flex-col gap-5">
                                    <form onSubmit={(event) => { event.preventDefault(); clearTimeout(timer.current); if (term.trim().length >= 2) visit({ q: term.trim(), source: source ?? undefined }); }} role="search" className="m-0">
                                        <label className="flex h-[52px] items-center gap-2.5 rounded-[14px] border border-[var(--dr-border)] bg-[var(--dr-field)] px-4 text-[var(--dr-text-3)] transition-colors focus-within:border-[var(--dr-accent)]">
                                            {loading ? <span aria-hidden="true" className="h-[18px] w-[18px] animate-spin rounded-full border-2 border-[var(--dr-border-2)] border-t-[var(--dr-accent)]" /> : <Svg d={ICON.search} size={18} />}
                                            <span className="sr-only">Rechercher dans la documentation</span>
                                            <input ref={inputRef} data-dr-native type="search" value={term} onChange={(event) => onChange(event.target.value)} autoFocus enterKeyHint="search" placeholder={source ? `Chercher dans ${ready.find((item) => item.key === source)?.name ?? 'cette doc'}…` : 'Array.map, flexbox, Route::get, useState…'} className="min-w-0 flex-1 appearance-none border-0 bg-transparent p-0 text-base text-[var(--dr-text)] outline-none placeholder:text-[var(--dr-text-3)] focus:ring-0 [&::-webkit-search-cancel-button]:hidden" />
                                            {term && <button type="button" onClick={() => onChange('')} aria-label="Effacer" className="flex h-8 w-8 items-center justify-center rounded-lg hover:bg-[var(--dr-surface)] hover:text-[var(--dr-text)]"><Svg d="M6 6l12 12M18 6L6 18" size={15} stroke={2.2} /></button>}
                                        </label>
                                    </form>

                                    {results ? <Results results={results} query={query} ai={ai} onAsk={() => setMode('ai')} /> : browse && source ? <Browse browse={browse} source={source} name={ready.find((item) => item.key === source)?.name} /> : <Home sources={ready} recent={recent} onPick={chooseSource} />}
                                </m.div>
                            ) : (
                                <m.div key="ai" initial={{ opacity: 0, y: 8 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -6 }} transition={softSpring}>
                                    {ai.enabled ? <AskPanel source={source} initialQuestion={query} provider={ai.provider} /> : <AiTeaser />}
                                </m.div>
                            )}
                        </AnimatePresence>
                    </>
                )}

                <p className="m-0 pt-2 text-xs leading-5 text-[var(--dr-text-3)]">
                    Pages en français : traductions officielles de <a href="https://developer.mozilla.org/fr/" target="_blank" rel="noopener noreferrer" className="underline">MDN</a>, <a href="https://fr.react.dev" target="_blank" rel="noopener noreferrer" className="underline">fr.react.dev</a> et du <a href="https://www.php.net/manual/fr/" target="_blank" rel="noopener noreferrer" className="underline">manuel PHP</a>. Pages en anglais : <a href="https://devdocs.io" target="_blank" rel="noopener noreferrer" className="underline">DevDocs</a> et la <a href="https://github.com/laravel/docs" target="_blank" rel="noopener noreferrer" className="underline">documentation officielle de Laravel</a>. Chaque page indique sa licence.
                </p>
            </div>
        </AppLayout>
    );
}

/** Langue des pages : le français d'abord, l'anglais en option (préférence enregistrée sur le compte). */
function LocaleSwitch({ locale }) {
    return (
        <Segmented
            label="Langue des pages"
            value={locale}
            onChange={(next) => next !== locale && router.patch('/docs/locale', { locale: next }, { preserveScroll: true, preserveState: true })}
            className="w-fit"
            items={[{ key: 'fr', label: 'Français' }, { key: 'en', label: 'English' }]}
        />
    );
}

/** Contenu d'une doc à parcourir : catégories (types) à gauche, entrées de la catégorie choisie à droite. */
function Browse({ browse, source, name }) {
    const { types, current, entries, total } = browse;

    function pick(type) {
        router.get('/docs', { source, type }, { preserveState: true, preserveScroll: true, replace: true, only: ['browse', 'source'] });
    }

    if (types.length === 0) {
        return <p className="m-0 text-sm text-[var(--dr-text-2)]">Cette documentation ne contient pas encore d’entrées.</p>;
    }

    const currentType = types.find((type) => type.key === current);

    return (
        <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
            <nav aria-label={`Catégories ${name ?? ''}`} className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 dr-scrollbar-none sm:mx-0 sm:max-h-[60vh] sm:w-60 sm:shrink-0 sm:flex-col sm:gap-1 sm:overflow-y-auto sm:overflow-x-visible sm:px-0 sm:pb-0">
                <h2 className={ui.eyebrow + ' m-0 hidden sm:block sm:px-2 sm:pb-1'}>Catégories</h2>
                {types.map((type) => {
                    const active = type.key === current;
                    return (
                        <button key={type.key} type="button" aria-current={active ? 'true' : undefined} onClick={() => pick(type.key)} className={'flex shrink-0 items-center justify-between gap-3 rounded-xl px-3 py-2 text-left text-sm transition-colors ' + ui.focus + ' ' + (active ? 'bg-[var(--dr-accent-soft)] font-semibold text-[var(--dr-accent-text)]' : 'text-[var(--dr-text-2)] hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)]')}>
                            <span className="truncate">{type.name}</span>
                            <span className="text-xs text-[var(--dr-text-3)]">{type.count}</span>
                        </button>
                    );
                })}
            </nav>

            <section aria-label={currentType?.name} className="flex min-w-0 flex-1 flex-col gap-2">
                <h2 className={ui.eyebrow + ' m-0'}>{currentType?.name} · {total.toLocaleString('fr-FR')} entrée{total > 1 ? 's' : ''}</h2>
                <ul className="m-0 flex list-none flex-col gap-2 p-0">
                    {entries.map((entry) => (
                        <li key={entry.id}>
                            <Link href={entry.url} className={'group flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 transition-colors hover:border-[var(--dr-border-2)] ' + ui.focus}>
                                <span className="min-w-0 flex-1 truncate font-['JetBrains_Mono',ui-monospace,monospace] text-[14px] font-medium text-[var(--dr-text)]">{entry.name}</span>
                                <Svg d={ICON.chevronRight} size={16} className="shrink-0 text-[var(--dr-text-3)] transition-transform group-hover:translate-x-0.5" />
                            </Link>
                        </li>
                    ))}
                </ul>
                {total > entries.length && <p className="m-0 text-xs text-[var(--dr-text-3)]">Les {entries.length} premières entrées sur {total.toLocaleString('fr-FR')} : utilise la recherche pour trouver la suivante.</p>}
            </section>
        </div>
    );
}

function Home({ sources, recent, onPick }) {
    return (
        <div className="flex flex-col gap-6">
            {recent.length > 0 && (
                <section aria-labelledby="lus-recemment" className="flex flex-col gap-2.5">
                    <h2 id="lus-recemment" className={ui.eyebrow + ' m-0'}>Lus récemment</h2>
                    <ul className="m-0 flex list-none flex-col gap-2 p-0">
                        {recent.slice(0, 5).map((item) => (
                            <li key={item.url}>
                                <Link href={item.url} className={'flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 hover:border-[var(--dr-border-2)] ' + ui.focus}>
                                    <TechTile technology={item.technology} title={item.source} size={32} />
                                    <span className="flex min-w-0 flex-1 flex-col"><span className="truncate text-[15px] font-semibold">{item.title}</span><span className="text-xs text-[var(--dr-text-3)]">{item.source}</span></span>
                                    <Svg d={ICON.chevronRight} size={16} className="text-[var(--dr-text-3)]" />
                                </Link>
                            </li>
                        ))}
                    </ul>
                </section>
            )}
            <section aria-labelledby="docs-dispo" className="flex flex-col gap-2.5">
                <h2 id="docs-dispo" className={ui.eyebrow + ' m-0'}>Documentations disponibles</h2>
                <ul className="m-0 grid list-none gap-2.5 p-0 [grid-template-columns:repeat(auto-fill,minmax(min(100%,200px),1fr))]">
                    {sources.map((item, index) => (
                        <m.li key={item.key} initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0, transition: { ...softSpring, delay: index * 0.03 } }}>
                            <button type="button" onClick={() => onPick(item.key)} className={'flex w-full items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-3.5 text-left transition-colors hover:border-[var(--dr-border-2)] ' + ui.focus}>
                                <TechTile technology={item.technology} title={item.name} size={40} />
                                <span className="flex min-w-0 flex-col">
                                    <span className="truncate text-[15px] font-semibold">{item.name}</span>
                                    <span className="truncate text-xs text-[var(--dr-text-3)]">{item.french ? 'En français' : 'En anglais · traduction IA'} · {item.entries.toLocaleString('fr-FR')} entrées</span>
                                </span>
                            </button>
                        </m.li>
                    ))}
                </ul>
            </section>
        </div>
    );
}

function Results({ results, query, ai, onAsk }) {
    const total = results.entries.length + results.pages.length;
    if (total === 0) {
        return (
            <div className="flex flex-col items-center gap-3 rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-5 py-10 text-center">
                <h2 className="m-0 text-lg font-semibold">Rien trouvé pour « {query} »</h2>
                <p className="m-0 max-w-sm text-sm leading-6 text-[var(--dr-text-2)]">Essaie le nom exact d’une fonction, d’une balise ou d’une propriété (ex. « map », « grid », « hasMany »), ou retire le filtre.</p>
                <button type="button" onClick={onAsk} className={ui.secondary}><Svg d="M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5l-1.9-4.6L5.5 9l4.6-1.4z" size={16} />{ai.enabled ? 'Poser la question à l’IA' : 'Découvrir l’assistant IA'}</button>
            </div>
        );
    }

    return (
        <div className="flex flex-col gap-6">
            {results.entries.length > 0 && (
                <section aria-label="Entrées de la documentation" className="flex flex-col gap-2">
                    {results.entries.map((entry, index) => (
                        <m.div key={entry.id} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0, transition: { ...softSpring, delay: Math.min(index, 10) * 0.02 } }}>
                            <Link href={entry.url} className={'group flex items-center gap-3 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 transition-colors hover:border-[var(--dr-border-2)] ' + ui.focus}>
                                <span className="flex min-w-0 flex-1 flex-col gap-0.5">
                                    <span className="truncate font-['JetBrains_Mono',ui-monospace,monospace] text-[14px] font-medium text-[var(--dr-text)]"><Highlight text={entry.name} query={query} /></span>
                                    <span className="truncate text-xs text-[var(--dr-text-3)]">{[entry.type, entry.source_name].filter(Boolean).join(' · ')}</span>
                                </span>
                                <Svg d={ICON.chevronRight} size={16} className="shrink-0 text-[var(--dr-text-3)] transition-transform group-hover:translate-x-0.5" />
                            </Link>
                        </m.div>
                    ))}
                </section>
            )}
            {results.pages.length > 0 && (
                <section aria-labelledby="dans-le-texte" className="flex flex-col gap-2">
                    <h2 id="dans-le-texte" className={ui.eyebrow + ' m-0'}>Dans le texte des pages</h2>
                    {results.pages.map((page) => (
                        <Link key={page.url} href={page.url} className={'flex flex-col gap-1 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 hover:border-[var(--dr-border-2)] ' + ui.focus}>
                            <span className="text-[15px] font-semibold">{page.title} <span className="font-normal text-[var(--dr-text-3)]">· {page.source_name}{page.locale === 'fr' ? ' · FR' : ' · EN'}</span></span>
                            <span className="line-clamp-2 text-sm leading-5 text-[var(--dr-text-2)]"><Highlight text={page.snippet} query={query} /></span>
                        </Link>
                    ))}
                </section>
            )}
        </div>
    );
}

function Highlight({ text, query }) {
    const value = String(text ?? '');
    const words = String(query ?? '').trim().split(/\s+/).filter((word) => word.length >= 2).map((word) => word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
    if (!words.length) return value;
    return value.split(new RegExp('(' + words.join('|') + ')', 'gi')).map((part, index) => index % 2 === 1
        ? <mark key={index} className="rounded-[4px] bg-[var(--dr-accent-soft)] px-0.5 text-[var(--dr-accent-text)]">{part}</mark>
        : part);
}

function NotReady() {
    return (
        <div className="flex flex-col items-center gap-2 rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-6 py-12 text-center">
            <span className="flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]"><Svg d={ICON.book} size={26} /></span>
            <h2 className="m-0 mt-2 text-lg font-semibold">La documentation se prépare</h2>
            <p className="m-0 max-w-sm text-sm leading-6 text-[var(--dr-text-2)]">Les index sont téléchargés au prochain déploiement. Reviens dans quelques minutes.</p>
        </div>
    );
}

function AiTeaser() {
    const perks = [
        ['Pose ta question en français', 'Plus besoin de deviner le bon mot-clé.'],
        ['Des réponses sourcées', 'L’IA lit les vraies pages de doc et cite chaque source.'],
        ['À ton niveau', 'Les explications s’adaptent à ton niveau d’apprentissage.'],
    ];
    return (
        <section className={ui.card + ' flex flex-col gap-5 p-5 sm:p-7'} aria-labelledby="ia-titre">
            <div className="flex items-start gap-3.5">
                <span aria-hidden="true" className="flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] bg-[var(--dr-accent)] text-[var(--dr-ink)]"><Svg d="M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5l-1.9-4.6L5.5 9l4.6-1.4zM19 15l.8 2 2 .8-2 .8L19 21l-.8-2.4-2-.8 2-.8z" size={20} stroke={2} /></span>
                <div className="flex flex-col gap-1">
                    <h2 id="ia-titre" className="m-0 font-['Manrope',sans-serif] text-xl font-extrabold tracking-[-0.02em]">Active ton assistant IA</h2>
                    <p className="m-0 text-sm leading-6 text-[var(--dr-text-2)]">Ajoute ta propre clé Anthropic, OpenAI ou Google Gemini. Elle est chiffrée et n’est jamais affichée ni partagée.</p>
                </div>
            </div>
            <ul className="m-0 grid list-none gap-2.5 p-0 sm:grid-cols-3">
                {perks.map(([title, text]) => (
                    <li key={title} className="flex flex-col gap-1 rounded-[14px] bg-[var(--dr-field)] p-3.5">
                        <span className="text-sm font-semibold">{title}</span>
                        <span className="text-[13px] leading-5 text-[var(--dr-text-2)]">{text}</span>
                    </li>
                ))}
            </ul>
            <div className="flex flex-wrap items-center gap-3">
                <MotionLink whileTap={{ scale: 0.97 }} href="/profile#assistant-ia" className={ui.primary}>Ajouter ma clé</MotionLink>
                <span className="text-[13px] text-[var(--dr-text-3)]">La recherche classique reste gratuite et sans clé.</span>
            </div>
        </section>
    );
}

const STEPS = ['Je cherche dans la documentation…', 'Je lis les pages trouvées…', 'Je rédige la réponse…'];

function AskPanel({ source, initialQuestion, provider }) {
    const [question, setQuestion] = useState(initialQuestion || '');
    const [pending, setPending] = useState(false);
    const [step, setStep] = useState(0);
    const [error, setError] = useState(null);
    const [thread, setThread] = useState([]);
    const [saved, setSaved] = useState({});
    const textRef = useRef(null);

    useEffect(() => {
        if (!pending) return undefined;
        setStep(0);
        const id = setInterval(() => setStep((current) => Math.min(current + 1, STEPS.length - 1)), 2200);
        return () => clearInterval(id);
    }, [pending]);

    async function ask(event) {
        event?.preventDefault();
        const q = question.trim();
        if (q.length < 4 || pending) return;
        setPending(true);
        setError(null);
        try {
            const response = await fetch('/docs/ask', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                body: JSON.stringify({ question: q, sources: source ? [source] : [] }),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const message = response.status === 429 ? 'Trop de questions d’un coup : patiente une minute.' : data.message ?? Object.values(data.errors ?? {})[0]?.[0] ?? 'L’assistant n’a pas pu répondre.';
                throw new Error(message);
            }
            setThread((current) => [{ id: Date.now(), question: q, ...data }, ...current]);
            setQuestion('');
        } catch (exception) {
            setError(exception.message);
        } finally {
            setPending(false);
        }
    }

    function keep(item) {
        const sources = item.sources.map((source) => `<li><a href="${source.url}">[${source.n}] ${escapeHtml(source.title)}</a> — ${escapeHtml(source.source)}</li>`).join('');
        router.post('/docs/memo', { title: item.question.slice(0, 250), html: item.html + `<h3>Sources</h3><ul>${sources}</ul>` }, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => setSaved((current) => ({ ...current, [item.id]: true })),
        });
    }

    return (
        <div className="flex flex-col gap-5">
            <form onSubmit={ask} className={ui.card + ' flex flex-col gap-3 p-4'}>
                <label htmlFor="docs-question" className="text-sm font-semibold">Ta question</label>
                <textarea
                    id="docs-question"
                    ref={textRef}
                    data-dr-native
                    rows={3}
                    value={question}
                    maxLength={600}
                    onChange={(event) => setQuestion(event.target.value)}
                    onKeyDown={(event) => { if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) ask(event); }}
                    placeholder="Ex. Comment faire une relation plusieurs-à-plusieurs avec Eloquent ?"
                    className={ui.field + ' h-auto resize-y py-3 leading-6'}
                />
                <div className="flex flex-wrap items-center gap-3">
                    <button type="submit" disabled={pending || question.trim().length < 4} className={ui.primary}>
                        {pending ? <span aria-hidden="true" className="h-4 w-4 animate-spin rounded-full border-2 border-[var(--dr-ink)] border-t-transparent" /> : <Svg d="M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5l-1.9-4.6L5.5 9l4.6-1.4z" size={16} stroke={2.2} />}
                        {pending ? 'Recherche…' : 'Demander'}
                    </button>
                    <span className="text-xs text-[var(--dr-text-3)]">Via {provider} · {source ? 'dans la doc filtrée' : 'dans toutes les docs'}</span>
                </div>
            </form>

            <div aria-live="polite" className="flex flex-col gap-4">
                {pending && (
                    <div className={ui.card + ' flex flex-col gap-3 p-5'}>
                        <AnimatePresence mode="wait"><m.span key={step} initial={{ opacity: 0, y: 4 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -4 }} className="text-sm font-semibold text-[var(--dr-accent-text)]">{STEPS[step]}</m.span></AnimatePresence>
                        {[90, 75, 82].map((width, index) => <span key={index} className="h-3 animate-pulse rounded-full bg-[var(--dr-field)]" style={{ width: width + '%' }} />)}
                    </div>
                )}
                {error && (
                    <div role="alert" className="flex flex-col gap-3 rounded-[18px] border border-[var(--dr-border-2)] bg-[var(--dr-surface)] p-4 sm:flex-row sm:items-center">
                        <span className="flex-1 text-sm text-[var(--dr-text)]">{error}</span>
                        <div className="flex gap-2">
                            {/Paramètres|clé/i.test(error) && <Link href="/profile#assistant-ia" className={ui.secondary + ' h-10'}>Paramètres</Link>}
                            <button type="button" onClick={ask} disabled={question.trim().length < 4} className={ui.secondary + ' h-10'}>Réessayer</button>
                        </div>
                    </div>
                )}
                <AnimatePresence initial={false}>
                    {thread.map((item) => (
                        <m.article key={item.id} layout initial={{ opacity: 0, y: 14 }} animate={{ opacity: 1, y: 0 }} transition={softSpring} className={ui.card + ' flex flex-col gap-4 p-5 sm:p-6'}>
                            <h2 className="m-0 text-base font-semibold text-[var(--dr-text-2)]">« {item.question} »</h2>
                            <div className="dr-doc" dangerouslySetInnerHTML={{ __html: item.html }} onClick={followInternalLink} />
                            <div className="flex flex-col gap-2 border-t border-[var(--dr-border)] pt-4">
                                <h3 className={ui.eyebrow + ' m-0'}>Sources</h3>
                                <ol className="m-0 flex list-none flex-col gap-1.5 p-0">
                                    {item.sources.map((source) => (
                                        <li key={source.n}>
                                            <Link href={source.url} className="inline-flex items-baseline gap-2 text-sm hover:text-[var(--dr-accent-text)]">
                                                <span className="font-semibold tabular-nums text-[var(--dr-accent-text)]">[{source.n}]</span>
                                                <span className="font-medium">{source.title}</span>
                                                <span className="text-[var(--dr-text-3)]">· {source.source}</span>
                                            </Link>
                                        </li>
                                    ))}
                                </ol>
                            </div>
                            <div className="flex flex-wrap gap-2">
                                <button type="button" onClick={() => keep(item)} disabled={saved[item.id]} className={saved[item.id] ? ui.secondary : ui.primary}>
                                    <Svg d={saved[item.id] ? ICON.check : ICON.bookmark} size={16} stroke={2.2} />{saved[item.id] ? 'Gardée en fiche' : 'Garder en fiche'}
                                </button>
                                <CopyAnswer text={item.answer} />
                                <button type="button" onClick={() => { setQuestion(item.question); textRef.current?.focus(); }} className={ui.ghost}>Reformuler</button>
                            </div>
                        </m.article>
                    ))}
                </AnimatePresence>
                {thread.length === 0 && !pending && !error && (
                    <p className="m-0 text-sm leading-6 text-[var(--dr-text-3)]">L’IA ne répond qu’à partir des pages de documentation trouvées, et cite chacune d’elles. Les réponses ne sont pas enregistrées : garde celles qui te servent en fiche mémo.</p>
                )}
            </div>
        </div>
    );
}

function CopyAnswer({ text }) {
    const [copied, setCopied] = useState(false);
    return (
        <button type="button" onClick={async () => { try { await navigator.clipboard.writeText(text); setCopied(true); setTimeout(() => setCopied(false), 1600); } catch {} }} className={ui.secondary}>
            <Svg d={copied ? ICON.check : ICON.copy} size={16} />{copied ? 'Copiée' : 'Copier'}
        </button>
    );
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
}
