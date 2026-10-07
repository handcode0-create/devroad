import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { ICON, MotionLink, Segmented, Svg, TechTile, ui } from '@/Components/Ui/Design';
import { rememberDoc } from '@/Components/Docs/recent';
import { followInternalLink } from '@/Components/Docs/links';

const SPARK = 'M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5l-1.9-4.6L5.5 9l4.6-1.4z';

// Lecture d'une page : le français d'abord (traduction officielle, ou traduction par l'IA),
// l'anglais en option. Sommaire, liens internes sans rechargement, licence et lien d'origine.
export default function Show({ source, page, path, error, want = 'fr', french = 'none', translation = null, originalUrl, originalFrUrl, ai = {} }) {
    const [activeId, setActiveId] = useState(null);
    const headings = page?.headings ?? [];
    const locale = page?.locale ?? 'en';

    useEffect(() => {
        if (page) rememberDoc({ url: `/docs/${source.key}/${page.path}`, title: page.title, source: source.name, technology: source.technology });
    }, [page?.path, page?.locale]);

    useEffect(() => {
        const id = decodeURIComponent(window.location.hash.slice(1));
        if (!id) { window.scrollTo({ top: 0 }); return; }
        requestAnimationFrame(() => document.getElementById(id)?.scrollIntoView({ block: 'start' }));
    }, [page?.path, page?.locale]);

    useEffect(() => {
        if (!headings.length || typeof IntersectionObserver === 'undefined') return undefined;
        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
            if (visible) setActiveId(visible.target.id);
        }, { rootMargin: '-80px 0px -70% 0px' });
        headings.forEach((heading) => { const node = document.getElementById(heading.id); if (node) observer.observe(node); });
        return () => observer.disconnect();
    }, [page?.path, page?.locale]);

    function switchTo(lang) {
        if (lang === want) return;
        router.get(`/docs/${source.key}/${path}`, { lang }, { preserveScroll: false });
    }

    const original = locale === 'fr' && french === 'official' && originalFrUrl ? originalFrUrl : originalUrl;

    return (
        <AppLayout>
            <Head title={page?.title ? `${page.title} · ${source.name}` : source.name} />
            <div className="mx-auto flex w-full max-w-6xl flex-col gap-5 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)]">
                <nav aria-label="Fil d'Ariane" className="flex min-w-0 items-center gap-1.5 text-sm text-[var(--dr-text-2)]">
                    <Link href="/docs" className={'-ml-1 inline-flex min-h-9 items-center gap-1.5 rounded-lg px-1 font-medium hover:text-[var(--dr-text)] ' + ui.focus}>
                        <Svg d="M19 12H5M11 6l-6 6 6 6" size={16} />Documentation
                    </Link>
                    <span aria-hidden="true" className="text-[var(--dr-text-3)]">/</span>
                    <Link href={`/docs?source=${source.key}`} className="inline-flex items-center gap-1.5 truncate rounded px-0.5 hover:text-[var(--dr-accent-text)]">
                        <TechTile technology={source.technology} title={source.name} size={20} />{source.name}
                    </Link>
                </nav>

                {error || !page ? (
                    <div role="alert" className="flex flex-col items-center gap-3 rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-6 py-12 text-center">
                        <h1 className="m-0 text-lg font-semibold">Page indisponible pour le moment</h1>
                        <p className="m-0 max-w-sm text-sm leading-6 text-[var(--dr-text-2)]">{error ?? 'Cette page est introuvable.'}</p>
                        <div className="flex flex-wrap justify-center gap-2">
                            <button type="button" onClick={() => router.reload()} className={ui.primary}>Réessayer</button>
                            <a href={originalUrl} target="_blank" rel="noopener noreferrer" className={ui.secondary}>Ouvrir l’original</a>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_240px] lg:items-start">
                        <article className="min-w-0">
                            <header className="mb-5 flex flex-col gap-3">
                                <h1 lang={locale} className="m-0 font-['Manrope',sans-serif] text-[28px] font-extrabold leading-[1.12] tracking-[-0.03em] [overflow-wrap:anywhere] lg:text-[36px]">{page.title}</h1>
                                <div className="flex flex-wrap items-center gap-2">
                                    <Segmented label="Langue de la page" value={locale} onChange={switchTo} items={[{ key: 'fr', label: 'Français' }, { key: 'en', label: 'English' }]} />
                                    <a href={original} target="_blank" rel="noopener noreferrer" className={ui.secondary + ' h-10 text-[13px]'}>
                                        <Svg d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6" size={15} />Original
                                    </a>
                                    <MotionLink whileTap={{ scale: 0.97 }} href={`/docs?source=${source.key}&mode=ia&q=${encodeURIComponent(page.title ?? '')}`} className={ui.secondary + ' h-10 text-[13px]'}>
                                        <Svg d={SPARK} size={15} />{ai.enabled ? 'Demander à l’IA' : 'Assistant IA'}
                                    </MotionLink>
                                </div>
                            </header>

                            <LanguageNotice locale={locale} want={want} french={french} ai={ai} translating={Boolean(translation && ai.translate)} onEnglish={() => switchTo('en')} />

                            {translation && ai.translate && locale === 'en' && want === 'fr'
                                ? <Translator source={source} path={path} translation={translation} ai={ai} english={page.html} />
                                : <Content html={page.html} locale={locale} headings={headings} activeId={activeId} />}

                            <footer className="mt-10 border-t border-[var(--dr-border)] pt-4 text-xs leading-5 text-[var(--dr-text-3)]">
                                {locale === 'fr' && french === 'official'
                                    ? <p className="m-0">{FRENCH_CREDITS[source.key] ?? 'Traduction officielle de la communauté.'}</p>
                                    : source.attribution && <div className="[&_a]:underline" dangerouslySetInnerHTML={{ __html: source.attribution }} />}
                                {locale === 'fr' && french === 'machine' && <p className="m-0 mt-1">Traduction automatique par IA d’une page publiée en anglais.</p>}
                                <p className="m-0 mt-1">{source.provider === 'devdocs' ? 'Page anglaise servie via DevDocs (devdocs.io).' : 'Documentation officielle de Laravel (github.com/laravel/docs).'} {source.version ? `Version : ${source.version}.` : ''}</p>
                            </footer>
                        </article>

                        {headings.length > 0 && !(translation && ai.translate && locale === 'en' && want === 'fr') && (
                            <aside className="hidden lg:sticky lg:top-24 lg:block">
                                <h2 className={ui.eyebrow + ' m-0 mb-3'}>Sur cette page</h2>
                                <Toc headings={headings} activeId={activeId} />
                            </aside>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

const FRENCH_CREDITS = {
    javascript: 'Traduction officielle de la communauté MDN (Mozilla), contenu sous licence CC-BY-SA 2.5.',
    html: 'Traduction officielle de la communauté MDN (Mozilla), contenu sous licence CC-BY-SA 2.5.',
    css: 'Traduction officielle de la communauté MDN (Mozilla), contenu sous licence CC-BY-SA 2.5.',
    dom: 'Traduction officielle de la communauté MDN (Mozilla), contenu sous licence CC-BY-SA 2.5.',
    react: 'Traduction officielle de fr.react.dev, contenu sous licence CC-BY 4.0.',
    php: 'Manuel PHP en français (php.net), © PHP Documentation Group, licence CC-BY 3.0.',
};

function Content({ html, locale, headings, activeId }) {
    return (
        <>
            {headings.length > 2 && (
                <details className="mb-6 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 lg:hidden">
                    <summary className="cursor-pointer list-none text-sm font-semibold [&::-webkit-details-marker]:hidden">Sur cette page · {headings.length} sections</summary>
                    <Toc headings={headings} activeId={activeId} className="mt-3" />
                </details>
            )}
            <div lang={locale} className="dr-doc" onClick={followInternalLink} dangerouslySetInnerHTML={{ __html: html }} />
        </>
    );
}

/** Bandeau de langue : traduction officielle, traduction IA, ou page seulement en anglais. */
function LanguageNotice({ locale, want, french, ai, translating, onEnglish }) {
    if (locale === 'fr' && french === 'machine') {
        return (
            <p className="mb-6 flex flex-wrap items-center gap-x-2 gap-y-1 rounded-[14px] bg-[var(--dr-field)] px-4 py-3 text-sm text-[var(--dr-text-2)]">
                <Svg d={SPARK} size={15} className="text-[var(--dr-accent-text)]" />
                Traduit automatiquement par l’IA.
                <button type="button" onClick={onEnglish} className="font-semibold text-[var(--dr-accent-text)] underline underline-offset-2">Lire l’original en anglais</button>
            </p>
        );
    }
    if (locale === 'en' && want === 'fr' && !translating) {
        const reason = french === 'unavailable'
            ? 'La version française n’a pas pu être chargée : voici la page anglaise.'
            : french === 'missing' ? 'Cette page n’a pas encore été traduite en français par la communauté.' : 'Cette documentation n’existe qu’en anglais.';
        return (
            <div className="mb-6 flex flex-col gap-1 rounded-[14px] border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 text-sm">
                <span className="font-semibold text-[var(--dr-text)]">{reason}</span>
                {!ai.translate && french !== 'unavailable' && (
                    <span className="text-[var(--dr-text-2)]">
                        <Link href="/profile#assistant-ia" className="font-semibold text-[var(--dr-accent-text)] underline underline-offset-2">Active ton assistant IA</Link> pour la traduire en français, ou utilise la traduction de ton navigateur.
                    </span>
                )}
            </div>
        );
    }
    return null;
}

/**
 * Traduction progressive par l'IA : la page est traduite morceau par morceau,
 * le texte français apparaît au fur et à mesure, puis la page se recharge en français.
 */
function Translator({ source, path, translation, ai, english }) {
    const [status, setStatus] = useState('idle'); // idle | running | error
    const [parts, setParts] = useState([]);
    const [error, setError] = useState(null);
    const [progress, setProgress] = useState({ done: translation.done, total: translation.total });
    const cancelled = useRef(false);

    useEffect(() => () => { cancelled.current = true; }, []);

    // Traduction offerte par DevRoad : elle démarre toute seule à l'ouverture de la page.
    useEffect(() => {
        if (ai.translate === 'platform') start();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    async function start() {
        setStatus('running');
        setError(null);
        cancelled.current = false;
        const collected = [];
        for (let chunk = 0; chunk < translation.total; chunk += 1) {
            if (cancelled.current) return;
            try {
                const response = await fetch('/docs/translate', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
                    body: JSON.stringify({ source: source.key, path, chunk }),
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(response.status === 429 ? 'Trop de demandes d’un coup : patiente une minute puis reprends.' : data.message ?? 'La traduction a échoué.');
                collected.push(data.html);
                setParts([...collected]);
                setProgress({ done: chunk + 1, total: data.total });
                if (data.done) {
                    router.reload({ preserveScroll: false });
                    return;
                }
            } catch (exception) {
                setError(exception.message);
                setStatus('error');
                return;
            }
        }
    }

    const percent = Math.round((progress.done / Math.max(progress.total, 1)) * 100);

    return (
        <div className="flex flex-col gap-5">
            <section className={ui.card + ' flex flex-col gap-3 p-4 sm:flex-row sm:items-center'} aria-live="polite">
                <div className="flex min-w-0 flex-1 flex-col gap-2">
                    <span className="text-sm font-semibold">
                        {status === 'running' ? `Traduction en cours… ${progress.done}/${progress.total}` : status === 'error' ? 'Traduction interrompue' : 'Pas encore de version française'}
                    </span>
                    {status !== 'idle' && <span className="h-1.5 overflow-hidden rounded-full bg-[var(--dr-field)]"><m.span className="block h-full rounded-full bg-[var(--dr-accent)]" animate={{ width: percent + '%' }} transition={softSpring} /></span>}
                    {status === 'idle' && <span className="text-[13px] text-[var(--dr-text-2)]">{`${ai.translate === 'platform' ? 'DevRoad' : `Ton IA (${ai.provider})`} la traduit en ${translation.total > 1 ? translation.total + ' passages' : 'un passage'} ; la traduction est ensuite gardée pour tous les lecteurs.`}</span>}
                    {error && <span role="alert" className="text-[13px] text-[var(--dr-danger)]">{error}</span>}
                </div>
                {ai.translate
                    ? status !== 'running' && <button type="button" onClick={start} className={ui.primary + ' shrink-0'}><Svg d={SPARK} size={16} stroke={2.2} />{status === 'error' ? 'Reprendre' : progress.done > 0 ? 'Terminer la traduction' : 'Traduire en français'}</button>
                    : <Link href="/profile#assistant-ia" className={ui.primary + ' shrink-0'}>Ajouter ma clé</Link>}
            </section>

            <AnimatePresence initial={false}>
                {parts.length > 0 && (
                    <m.div key="fr" initial={{ opacity: 0 }} animate={{ opacity: 1 }} lang="fr" className="dr-doc" onClick={followInternalLink} dangerouslySetInnerHTML={{ __html: parts.join('').replace(/^<h1>.*?<\/h1>/s, '') }} />
                )}
            </AnimatePresence>
            {status === 'running' && <div className="flex flex-col gap-2" aria-hidden="true">{[92, 80, 86].map((width, index) => <span key={index} className="h-3 animate-pulse rounded-full bg-[var(--dr-field)]" style={{ width: width + '%' }} />)}</div>}
            {/* En attendant la traduction : la page anglaise reste lisible (et les liens vers ses sections fonctionnent). */}
            {parts.length === 0 && <div lang="en" className="dr-doc" onClick={followInternalLink} dangerouslySetInnerHTML={{ __html: english }} />}
        </div>
    );
}

function Toc({ headings, activeId, className = '' }) {
    return (
        <ol className={'m-0 flex max-h-[70vh] list-none flex-col gap-0.5 overflow-y-auto p-0 ' + className}>
            {headings.map((heading) => (
                <li key={heading.id}>
                    <a
                        href={'#' + heading.id}
                        aria-current={activeId === heading.id ? 'location' : undefined}
                        className={'block rounded-lg border-l-2 py-1.5 pr-2 text-[13px] leading-5 transition-colors ' + (heading.level === 3 ? 'pl-5 ' : 'pl-3 ') + (activeId === heading.id ? 'border-[var(--dr-accent)] font-semibold text-[var(--dr-text)]' : 'border-transparent text-[var(--dr-text-2)] hover:text-[var(--dr-text)]')}
                    >
                        {heading.text}
                    </a>
                </li>
            ))}
        </ol>
    );
}
