import { Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { EmptyState, ICON, MotionLink, PageHead, ProgressBar, Segmented, StatePill, Svg, TechTile, clamp, roadmapState, ui } from '@/Components/Ui/Design';

// Parcours — même langage que l'Accueil et les Fiches de la maquette :
// titre Manrope, filtre segmenté, cartes avec tuile techno, progression et prochaine étape.
export default function Index({ roadmaps }) {
    const items = roadmaps?.data ?? [];
    const [filter, setFilter] = useState('all');

    const counts = useMemo(() => items.reduce((acc, roadmap) => {
        acc[roadmapState(roadmap)] += 1;
        return acc;
    }, { todo: 0, progress: 0, done: 0 }), [items]);

    const shown = filter === 'all' ? items : items.filter((roadmap) => roadmapState(roadmap) === filter);
    const average = items.length ? Math.round(items.reduce((sum, roadmap) => sum + clamp(roadmap.progress), 0) / items.length) : 0;
    const total = roadmaps?.total ?? items.length;

    const tabs = [
        { key: 'all', label: 'Tous', count: items.length },
        { key: 'progress', label: 'En cours', count: counts.progress },
        { key: 'todo', label: 'À démarrer', count: counts.todo },
        { key: 'done', label: 'Terminés', count: counts.done },
    ];

    return (
        <AppLayout>
            <Head title="Parcours" />

            <div className="flex flex-col gap-6 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] lg:gap-7">
                <PageHead
                    title="Parcours"
                    subtitle={total ? `${total} parcours · ${average} % de progression moyenne` : 'Choisis une technologie et avance étape par étape.'}
                    actions={<MotionLink whileTap={{ scale: 0.97 }} href="/roadmaps/create" className={ui.primary}><Svg d={ICON.plus} size={16} stroke={2.4} />Nouveau parcours</MotionLink>}
                />

                {items.length > 0 && <Segmented label="Filtrer les parcours" items={tabs} value={filter} onChange={setFilter} className="w-fit max-w-full" />}

                {items.length === 0 ? (
                    <EmptyState
                        icon={<Svg d={ICON.map} size={26} />}
                        title="Aucun parcours pour le moment"
                        text="Choisis une technologie : DevRoad prépare les étapes du parcours pour toi."
                        action={<Link href="/roadmaps/create" className={ui.primary}><Svg d={ICON.plus} size={16} stroke={2.4} />Créer mon premier parcours</Link>}
                    />
                ) : shown.length === 0 ? (
                    <p className="m-0 rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-4 py-8 text-center text-sm text-[var(--dr-text-2)]">Aucun parcours dans cette catégorie.</p>
                ) : (
                    <ul className="m-0 grid list-none gap-4 p-0 [grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr))]">
                        <AnimatePresence initial={false} mode="popLayout">
                            {shown.map((roadmap, index) => (
                                <m.li
                                    key={roadmap.id}
                                    layout
                                    initial={{ opacity: 0, y: 14 }}
                                    animate={{ opacity: 1, y: 0, transition: { ...softSpring, delay: Math.min(index, 8) * 0.04 } }}
                                    exit={{ opacity: 0, scale: 0.96, transition: { duration: 0.15 } }}
                                    className="min-w-0"
                                >
                                    <RoadmapCard roadmap={roadmap} />
                                </m.li>
                            ))}
                        </AnimatePresence>
                    </ul>
                )}

                {roadmaps?.links?.length > 3 && <Pagination links={roadmaps.links} />}
            </div>
        </AppLayout>
    );
}

function RoadmapCard({ roadmap }) {
    const progress = clamp(roadmap.progress);
    const state = roadmapState(roadmap);

    return (
        <article className={ui.card + ' group relative flex h-full flex-col gap-4 p-[18px] transition-[border-color,transform] duration-200 hover:-translate-y-0.5 hover:border-[var(--dr-border-2)] has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-[var(--dr-accent)] motion-reduce:hover:translate-y-0'}>
            <div className="flex items-start gap-3.5">
                <TechTile technology={roadmap.technology} title={roadmap.title} size={48} />
                <div className="flex min-w-0 flex-1 flex-col gap-1">
                    <Link href={`/roadmaps/${roadmap.id}`} className="text-[17px] font-semibold leading-[1.35] tracking-[-0.01em] text-[var(--dr-text)] outline-none after:absolute after:inset-0 after:rounded-[18px] after:content-['']">
                        {roadmap.title}
                    </Link>
                    <span className="text-[13px] text-[var(--dr-text-3)]">{roadmap.completed_steps_count ?? 0} / {roadmap.steps_count ?? 0} étapes</span>
                </div>
                <StatePill state={state} />
            </div>

            {roadmap.description && <p className="m-0 line-clamp-2 text-sm leading-[1.55] text-[var(--dr-text-2)]">{roadmap.description}</p>}

            <div className="mt-auto flex flex-col gap-3">
                <div className="flex items-center gap-3">
                    <ProgressBar value={progress} className="h-1.5 flex-1" />
                    <span className="text-xs font-semibold tabular-nums text-[var(--dr-text-2)]">{progress}%</span>
                </div>
                {roadmap.next_step ? (
                    <Link href={`/steps/${roadmap.next_step.id}`} className={'relative z-10 flex items-center gap-2.5 rounded-xl bg-[var(--dr-field)] px-3 py-2.5 transition-colors hover:bg-[var(--dr-accent-soft)] ' + ui.focus}>
                        <span aria-hidden="true" className="h-2 w-2 shrink-0 rounded-full bg-[var(--dr-accent)] shadow-[0_0_0_4px_var(--dr-accent-soft)]" />
                        <span className="flex min-w-0 flex-1 flex-col">
                            <span className="text-[11px] font-semibold uppercase tracking-[0.08em] text-[var(--dr-text-3)]">Prochaine étape</span>
                            <span className="truncate text-sm font-semibold text-[var(--dr-text)]">{roadmap.next_step.title}</span>
                        </span>
                        <Svg d={ICON.arrow} size={16} className="shrink-0 text-[var(--dr-accent-text)]" />
                    </Link>
                ) : (
                    <span className="flex items-center gap-2 rounded-xl bg-[var(--dr-field)] px-3 py-2.5 text-sm text-[var(--dr-text-2)]">
                        <Svg d={ICON.check} size={16} stroke={2.4} className="text-[var(--dr-accent-text)]" />
                        {(roadmap.steps_count ?? 0) > 0 ? 'Toutes les étapes sont terminées' : 'Aucune étape pour l’instant'}
                    </span>
                )}
            </div>
        </article>
    );
}

function Pagination({ links }) {
    return (
        <nav className="flex flex-wrap justify-center gap-1" aria-label="Pagination">
            {links.map((link, index) => (
                <Link
                    key={index}
                    href={link.url ?? '#'}
                    preserveScroll
                    aria-current={link.active ? 'page' : undefined}
                    className={'inline-flex h-10 min-w-10 items-center justify-center rounded-xl px-3 text-sm font-semibold ' + (link.active ? 'bg-[var(--dr-accent)] text-[var(--dr-ink)]' : link.url ? 'bg-[var(--dr-surface)] text-[var(--dr-text-2)] hover:text-[var(--dr-text)]' : 'pointer-events-none text-[var(--dr-text-3)] opacity-50')}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </nav>
    );
}
