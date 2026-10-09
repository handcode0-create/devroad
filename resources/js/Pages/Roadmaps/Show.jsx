import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { ICON, MotionLink, PageHead, ProgressRing, Segmented, StatePill, Svg, TechTile, clamp, roadmapState, ui } from '@/Components/Ui/Design';

const TECH_LABELS = {
    laravel: 'Laravel', nextjs: 'Next.js', react: 'React', javascript: 'JavaScript', typescript: 'TypeScript', php: 'PHP',
    html: 'HTML', css: 'CSS', tailwind: 'Tailwind CSS', node: 'Node.js', git: 'Git', github: 'GitHub', docker: 'Docker',
    mysql: 'MySQL', postgresql: 'PostgreSQL',
    python: 'Python', django: 'Django', fastapi: 'FastAPI', flutter: 'Flutter',
    supabase: 'Supabase', prisma: 'Prisma', svelte: 'Svelte', java: 'Java',
};
const LEVELS = { beginner: 'Débutant', intermediate: 'Intermédiaire', professional: 'Professionnel', licence: 'Licence', engineering: 'Cycle ingénieur' };

// Détail d'un parcours : en-tête avec anneau de progression et action « Continuer »,
// puis la frise des étapes. Sur desktop, ressources et infos passent en colonne latérale.
export default function Show({ roadmap }) {
    const [tab, setTab] = useState('steps');
    const steps = Array.isArray(roadmap?.steps) ? roadmap.steps : [];
    const resources = Array.isArray(roadmap?.resources) ? roadmap.resources : [];
    const progress = clamp(roadmap?.progress);
    const current = steps.find((step) => step.status === 'in_progress') ?? steps.find((step) => step.status !== 'completed' && step.status !== 'blocked') ?? null;
    const done = steps.length > 0 && !steps.some((step) => step.status !== 'completed');
    const technology = TECH_LABELS[roadmap?.technology] ?? roadmap?.technology;

    const tabs = [
        { key: 'steps', label: 'Étapes', count: steps.length },
        { key: 'resources', label: 'Ressources', count: resources.length },
        { key: 'about', label: 'À propos' },
    ];

    return (
        <AppLayout>
            <Head title={roadmap?.title ?? 'Parcours'} />

            <div className="flex flex-col gap-6 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] lg:gap-7">
                <PageHead
                    back={{ href: '/roadmaps', label: 'Parcours' }}
                    title={roadmap?.title ?? 'Sans titre'}
                    subtitle={roadmap?.description}
                    actions={<Link href={`/roadmaps/${roadmap?.id}/edit`} className={ui.secondary}><Svg d={ICON.edit} size={16} />Modifier</Link>}
                />

                {/* Progression + reprise */}
                <section aria-label="Progression" className={ui.card + ' flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-6'}>
                    <div className="flex items-center gap-4 sm:flex-1">
                        <ProgressRing value={progress} size={76} stroke={7} />
                        <div className="flex min-w-0 flex-col gap-1.5">
                            <div className="flex flex-wrap items-center gap-2">
                                <TechTile technology={roadmap?.technology} title={roadmap?.title} size={26} />
                                <span className="text-sm font-semibold text-[var(--dr-text-2)]">{technology}</span>
                                <StatePill state={roadmapState(roadmap)} />
                            </div>
                            <span className="text-[15px] font-semibold">{roadmap?.completed_steps_count ?? 0} étapes sur {roadmap?.steps_count ?? steps.length} terminées</span>
                        </div>
                    </div>
                    {current ? (
                        <MotionLink whileTap={{ scale: 0.98 }} href={`/steps/${current.id}`} className={ui.primary + ' h-[52px] w-full justify-between rounded-2xl px-5 sm:w-auto sm:min-w-[300px]'}>
                            <span className="flex min-w-0 flex-col items-start leading-tight">
                                <span className="text-[11px] font-semibold uppercase tracking-[0.08em] opacity-70">{current.status === 'in_progress' ? 'Reprendre' : 'Commencer'}</span>
                                <span className="max-w-[240px] truncate">{current.title}</span>
                            </span>
                            <Svg d={ICON.arrow} size={18} stroke={2.4} />
                        </MotionLink>
                    ) : done ? (
                        <span className="flex h-[52px] items-center gap-2 rounded-2xl bg-[var(--dr-accent-soft)] px-5 text-sm font-bold text-[var(--dr-accent-text)]">
                            <Svg d={ICON.check} size={18} stroke={2.6} />Parcours terminé — bravo !
                        </span>
                    ) : null}
                </section>

                <Segmented label="Sections du parcours" items={tabs} value={tab} onChange={setTab} className="w-fit max-w-full lg:hidden" />

                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-start">
                    <div className={(tab === 'steps' ? 'block' : 'hidden') + ' lg:block'}>
                        <StepsSection steps={steps} roadmapId={roadmap?.id} currentId={current?.id} />
                    </div>
                    <aside className={(tab === 'steps' ? 'hidden' : 'flex') + ' flex-col gap-6 lg:sticky lg:top-24 lg:flex'}>
                        <div className={(tab === 'resources' ? 'block' : 'hidden') + ' lg:block'}><ResourcesCard resources={resources} /></div>
                        <div className={(tab === 'about' ? 'block' : 'hidden') + ' lg:block'}><AboutCard roadmap={roadmap} technology={technology} stepsCount={steps.length} /></div>
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}

function StepsSection({ steps, roadmapId, currentId }) {
    return (
        <section aria-labelledby="etapes-titre" className="flex flex-col gap-3">
            <div className="flex items-center justify-between gap-3">
                <h2 id="etapes-titre" className={ui.sectionTitle + ' m-0'}>Étapes</h2>
                <Link href={`/roadmaps/${roadmapId}/steps/create`} className={ui.ghost + ' h-10 text-[var(--dr-accent-text)]'}>
                    <Svg d={ICON.plus} size={16} stroke={2.4} />Ajouter une étape
                </Link>
            </div>

            {steps.length === 0 ? (
                <div className="rounded-[18px] border border-dashed border-[var(--dr-border-2)] px-5 py-10 text-center">
                    <p className="m-0 text-sm text-[var(--dr-text-2)]">Ce parcours n’a pas encore d’étapes.</p>
                    <Link href={`/roadmaps/${roadmapId}/steps/create`} className={ui.primary + ' mt-4'}><Svg d={ICON.plus} size={16} stroke={2.4} />Ajouter la première étape</Link>
                </div>
            ) : (
                <ol className="m-0 flex list-none flex-col p-0">
                    <AnimatePresence initial>
                        {steps.map((step, index) => (
                            <m.li
                                key={step.id}
                                initial={{ opacity: 0, x: -8 }}
                                animate={{ opacity: 1, x: 0, transition: { ...softSpring, delay: Math.min(index, 12) * 0.03 } }}
                                className="relative flex gap-3.5"
                            >
                                <StepNode step={step} index={index} last={index === steps.length - 1} current={step.id === currentId} />
                                <StepRow step={step} index={index} current={step.id === currentId} />
                            </m.li>
                        ))}
                    </AnimatePresence>
                </ol>
            )}
        </section>
    );
}

/** Pastille de la frise : coche (terminée), lecture (en cours), numéro, cadenas (bloquée). */
function StepNode({ step, index, last, current }) {
    const completed = step.status === 'completed';
    const blocked = step.status === 'blocked';
    return (
        <div className="relative flex w-9 shrink-0 flex-col items-center pt-4" aria-hidden="true">
            <span className={'relative z-10 flex h-9 w-9 items-center justify-center rounded-full text-[13px] font-bold tabular-nums ' + (completed ? 'bg-[var(--dr-accent)] text-[var(--dr-ink)]' : current ? 'bg-[var(--dr-surface)] text-[var(--dr-accent-text)] ring-2 ring-[var(--dr-accent)] ring-offset-2 ring-offset-[var(--dr-bg)]' : 'bg-[var(--dr-field)] text-[var(--dr-text-2)]')}>
                {completed ? <Svg d={ICON.check} size={16} stroke={3} />
                    : blocked ? <Svg d="M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z" size={15} />
                        : index + 1}
            </span>
            {!last && <span className={'w-0.5 flex-1 ' + (completed ? 'bg-[var(--dr-accent)]' : 'bg-[var(--dr-border-2)]')} />}
        </div>
    );
}

function StepRow({ step, index, current }) {
    const blocked = step.status === 'blocked';
    const statusLabel = { completed: 'Terminée', in_progress: 'En cours', blocked: 'Bloquée' }[step.status] ?? 'À faire';
    const levels = [step.difficulty_level, step.academic_level].map((level) => LEVELS[level]).filter(Boolean);

    return (
        <div className={'group relative mb-2.5 flex min-w-0 flex-1 items-center gap-3 rounded-2xl border px-4 py-3.5 transition-colors has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-[var(--dr-accent)] ' + (current ? 'border-[var(--dr-accent)] bg-[var(--dr-surface)] shadow-[var(--dr-shadow)]' : 'border-[var(--dr-border)] bg-[var(--dr-surface)] hover:border-[var(--dr-border-2)]') + (blocked ? ' opacity-60' : '')}>
            <div className="flex min-w-0 flex-1 flex-col gap-1">
                <span className="text-xs font-semibold text-[var(--dr-text-3)]">Étape {index + 1} · <span className={current ? 'text-[var(--dr-accent-text)]' : ''}>{statusLabel}</span></span>
                {blocked
                    ? <span className="text-[15px] font-semibold leading-[1.35]">{step.title}</span>
                    : <Link href={`/steps/${step.id}`} className="text-[15px] font-semibold leading-[1.35] text-[var(--dr-text)] outline-none after:absolute after:inset-0 after:rounded-2xl after:content-['']">{step.title}</Link>}
                {step.description && <p className="m-0 line-clamp-2 text-[13px] leading-5 text-[var(--dr-text-2)]">{step.description}</p>}
                {levels.length > 0 && <div className="mt-1 flex flex-wrap gap-1.5">{levels.map((level) => <span key={level} className="rounded-full bg-[var(--dr-field)] px-2.5 py-0.5 text-xs text-[var(--dr-text-2)]">{level}</span>)}</div>}
            </div>
            {!blocked && <>
                <Link href={`/steps/${step.id}/edit`} aria-label={`Modifier « ${step.title} »`} className={'relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] text-[var(--dr-text-3)] opacity-100 transition hover:bg-[var(--dr-field)] hover:text-[var(--dr-text)] lg:opacity-0 lg:group-hover:opacity-100 lg:focus-visible:opacity-100 ' + ui.focus}>
                    <Svg d={ICON.edit} size={15} />
                </Link>
                <Svg d={ICON.chevronRight} size={17} className="shrink-0 text-[var(--dr-text-3)] transition-transform group-hover:translate-x-0.5" />
            </>}
        </div>
    );
}

function ResourcesCard({ resources }) {
    return (
        <section aria-labelledby="ressources-titre" className={ui.card + ' flex flex-col gap-3 p-5'}>
            <h2 id="ressources-titre" className={ui.sectionTitle + ' m-0'}>Ressources</h2>
            {resources.length === 0 ? (
                <p className="m-0 text-sm text-[var(--dr-text-2)]">Aucune ressource configurée pour cette technologie.</p>
            ) : (
                <ul className="m-0 flex list-none flex-col gap-2 p-0">
                    {resources.map((resource) => (
                        <li key={resource.url}>
                            <a href={resource.url} target="_blank" rel="noreferrer" className={'group flex items-center gap-3 rounded-xl bg-[var(--dr-field)] px-3.5 py-3 transition-colors hover:bg-[var(--dr-accent-soft)] ' + ui.focus}>
                                <span className="flex min-w-0 flex-1 flex-col">
                                    <span className="text-sm font-semibold text-[var(--dr-text)]">{resource.label}</span>
                                    <span className="truncate text-xs text-[var(--dr-text-3)]">{resource.url.replace(/^https?:\/\//, '')}</span>
                                </span>
                                <Svg d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6" size={15} className="shrink-0 text-[var(--dr-text-3)] group-hover:text-[var(--dr-accent-text)]" />
                                <span className="sr-only">(nouvel onglet)</span>
                            </a>
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}

function AboutCard({ roadmap, technology, stepsCount }) {
    const rows = [
        ['Technologie', technology],
        ['Étapes', String(roadmap?.steps_count ?? stepsCount)],
        ['Progression', clamp(roadmap?.progress) + ' %'],
    ];
    const about = roadmap?.about?.description;
    return (
        <section aria-labelledby="apropos-titre" className={ui.card + ' flex flex-col gap-4 p-5'}>
            <h2 id="apropos-titre" className={ui.sectionTitle + ' m-0'}>À propos</h2>
            <dl className="m-0 grid grid-cols-3 gap-2">
                {rows.map(([label, value]) => (
                    <div key={label} className="flex flex-col gap-0.5 rounded-[14px] bg-[var(--dr-field)] px-3 py-2.5">
                        <dt className="text-xs text-[var(--dr-text-3)]">{label}</dt>
                        <dd className="m-0 truncate text-sm font-bold">{value}</dd>
                    </div>
                ))}
            </dl>
            {about && <p className="m-0 text-sm leading-[1.6] text-[var(--dr-text-2)]">{about}</p>}
        </section>
    );
}
