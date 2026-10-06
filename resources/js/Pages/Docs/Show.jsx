import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { ICON, MotionLink, Svg, TechTile, ui } from '@/Components/Ui/Design';
import { rememberDoc } from '@/Components/Docs/recent';
import { followInternalLink } from '@/Components/Docs/links';

// Lecture d'une page de documentation, dans le thème DevRoad : sommaire, liens internes
// sans rechargement, lien vers l'original et mention de la licence.
export default function Show({ source, page, path, error, originalUrl, ai = {} }) {
    const contentRef = useRef(null);
    const [activeId, setActiveId] = useState(null);
    const headings = page?.headings ?? [];

    useEffect(() => {
        if (page) rememberDoc({ url: `/docs/${source.key}/${page.path}`, title: page.title, source: source.name, technology: source.technology });
    }, [page?.path]);

    // Ancre (#section) : défilement après le rendu du contenu.
    useEffect(() => {
        const id = decodeURIComponent(window.location.hash.slice(1));
        if (!id) { window.scrollTo({ top: 0 }); return; }
        requestAnimationFrame(() => document.getElementById(id)?.scrollIntoView({ block: 'start' }));
    }, [page?.path]);

    // Sommaire : met en évidence la section en cours de lecture.
    useEffect(() => {
        if (!headings.length || typeof IntersectionObserver === 'undefined') return undefined;
        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)[0];
            if (visible) setActiveId(visible.target.id);
        }, { rootMargin: '-80px 0px -70% 0px' });
        headings.forEach((heading) => { const node = document.getElementById(heading.id); if (node) observer.observe(node); });
        return () => observer.disconnect();
    }, [page?.path]);

    function retry() {
        router.reload();
    }

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
                            <button type="button" onClick={retry} className={ui.primary}>Réessayer</button>
                            <a href={originalUrl} target="_blank" rel="noopener noreferrer" className={ui.secondary}>Ouvrir l’original</a>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_240px] lg:items-start">
                        <article className="min-w-0">
                            <header className="mb-6 flex flex-col gap-3">
                                <h1 className="m-0 font-['Manrope',sans-serif] text-[28px] font-extrabold leading-[1.12] tracking-[-0.03em] [overflow-wrap:anywhere] lg:text-[36px]">{page.title}</h1>
                                <div className="flex flex-wrap items-center gap-2">
                                    <a href={originalUrl} target="_blank" rel="noopener noreferrer" className={ui.secondary + ' h-10 text-[13px]'}>
                                        <Svg d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6" size={15} />Voir l’original
                                    </a>
                                    <MotionLink whileTap={{ scale: 0.97 }} href={`/docs?source=${source.key}&mode=ia&q=${encodeURIComponent(page.title ?? '')}`} className={ui.secondary + ' h-10 text-[13px]'}>
                                        <Svg d="M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5l-1.9-4.6L5.5 9l4.6-1.4z" size={15} />{ai.enabled ? 'Demander à l’IA' : 'Assistant IA'}
                                    </MotionLink>
                                </div>
                            </header>

                            {headings.length > 2 && (
                                <details className="mb-6 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 py-3 lg:hidden">
                                    <summary className="cursor-pointer list-none text-sm font-semibold [&::-webkit-details-marker]:hidden">Sur cette page · {headings.length} sections</summary>
                                    <Toc headings={headings} activeId={activeId} className="mt-3" />
                                </details>
                            )}

                            <div ref={contentRef} className="dr-doc" onClick={followInternalLink} dangerouslySetInnerHTML={{ __html: page.html }} />

                            <footer className="mt-10 border-t border-[var(--dr-border)] pt-4 text-xs leading-5 text-[var(--dr-text-3)]">
                                {source.attribution && <div className="[&_a]:underline" dangerouslySetInnerHTML={{ __html: source.attribution }} />}
                                <p className="m-0 mt-1">{source.provider === 'devdocs' ? 'Page servie via DevDocs (devdocs.io).' : 'Documentation officielle de Laravel (github.com/laravel/docs).'} {source.version ? `Version : ${source.version}.` : ''}</p>
                            </footer>
                        </article>

                        {headings.length > 0 && (
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
