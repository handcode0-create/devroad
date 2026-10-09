import { Link } from '@inertiajs/react';
import { Fragment, useMemo, useState } from 'react';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { ICON, Svg, TechTile, ui } from '@/Components/Ui/Design';

// Catalogue de cours de l'accueil : une carte par cours (vignette, catégorie, titre, niveau, durée),
// filtrable par technologie, avec un encart d'aide intercalé après la première carte.

const PAGE_SIZE = 6;

const LEVELS = {
    beginner: { label: 'Facile', bars: 1 },
    intermediate: { label: 'Moyen', bars: 2 },
    professional: { label: 'Avancé', bars: 3 },
};

const STATUS_LABEL = {
    in_progress: 'En cours',
    completed: 'Terminé',
    blocked: 'Verrouillé',
};

const TECH_LABELS = {
    laravel: 'Laravel', nextjs: 'Next.js', react: 'React', javascript: 'JavaScript', typescript: 'TypeScript',
    php: 'PHP', html: 'HTML', css: 'CSS', tailwind: 'Tailwind CSS', node: 'Node.js', git: 'Git',
    github: 'GitHub', docker: 'Docker', mysql: 'MySQL', postgresql: 'PostgreSQL',
    python: 'Python', django: 'Django', fastapi: 'FastAPI', flutter: 'Flutter',
    supabase: 'Supabase', prisma: 'Prisma', svelte: 'Svelte', java: 'Java',
};

const techKey = (course) => String(course.technology ?? '').toLowerCase().trim() || 'autre';
const techLabel = (key) => TECH_LABELS[key] ?? (key === 'autre' ? 'Autre' : key.charAt(0).toUpperCase() + key.slice(1));

function formatDuration(minutes) {
    const value = Number(minutes);
    if (!value || value < 1) return null;
    if (value < 60) return `${value} min`;
    const hours = Math.floor(value / 60);
    const rest = value % 60;
    return rest ? `${hours} h ${String(rest).padStart(2, '0')}` : `${hours} h`;
}

function LevelBars({ bars }) {
    return (
        <svg width="16" height="16" viewBox="0 0 16 16" aria-hidden="true" className="shrink-0">
            {[0, 1, 2].map((index) => (
                <rect key={index} x={1 + index * 5} y={10 - index * 4} width="3.4" height={5 + index * 4} rx="1" fill="currentColor" opacity={index < bars ? 1 : 0.28} />
            ))}
        </svg>
    );
}

function CourseCard({ course }) {
    const level = LEVELS[course.difficulty_level] ?? null;
    const duration = formatDuration(course.estimated_minutes);
    const status = STATUS_LABEL[course.status] ?? null;
    const blocked = course.status === 'blocked';
    const category = `${techLabel(techKey(course))} · Cours`;

    const body = (
        <>
            <TechTile technology={course.technology} title={course.title} size={88} />
            <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                <span className="truncate text-[12px] font-bold uppercase tracking-[0.08em] text-[var(--dr-accent-text)]">{category}</span>
                <h3 className="m-0 text-[17px] font-bold leading-[1.3] tracking-[-0.01em] text-[var(--dr-text)] [overflow-wrap:anywhere]">
                    {blocked
                        ? course.title
                        : <Link href={`/steps/${course.id}`} className={'rounded-sm outline-none after:absolute after:inset-0 after:rounded-[18px] after:content-[\'\'] ' + ui.focus}>{course.title}</Link>}
                </h3>
                <div className="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-[14px] text-[var(--dr-text-2)]">
                    {level && <span className="inline-flex items-center gap-1.5"><LevelBars bars={level.bars} />{level.label}</span>}
                    {duration && <span className="inline-flex items-center gap-1.5"><Svg d={['M12 7v5l3 2', 'M12 3a9 9 0 100 18 9 9 0 000-18z']} size={16} />{duration}</span>}
                    {status && (
                        <span className={'inline-flex h-6 items-center gap-1.5 rounded-full px-2.5 text-xs font-semibold ' + (course.status === 'completed' ? 'bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]' : 'bg-[var(--dr-field)] text-[var(--dr-text)]')}>
                            {course.status === 'in_progress' && <span aria-hidden="true" className="h-1.5 w-1.5 rounded-full bg-[var(--dr-accent)]" />}
                            {status}
                        </span>
                    )}
                </div>
            </div>
        </>
    );

    return (
        <m.article
            whileTap={blocked ? undefined : { scale: 0.985 }}
            className={ui.card + ' relative flex items-center gap-4 p-4 transition-colors has-[a:focus-visible]:ring-2 has-[a:focus-visible]:ring-[var(--dr-accent)] ' + (blocked ? 'opacity-60' : 'hover:border-[var(--dr-border-2)]')}
        >
            {body}
        </m.article>
    );
}

function InfoBanner() {
    return (
        <aside aria-label="À propos des parcours" className="flex flex-col gap-1.5 rounded-[18px] bg-[var(--dr-accent-soft)] px-5 py-5">
            <p className="m-0 text-[17px] font-bold leading-[1.35] text-[var(--dr-text)]">Un cours, un parcours : quelle différence ?</p>
            <Link href="/roadmaps" className={'inline-flex min-h-11 w-fit items-center gap-2 rounded-lg text-[16px] font-bold text-[var(--dr-accent-text)] ' + ui.focus}>
                Découvrir mes parcours<Svg d={ICON.arrow} size={16} stroke={2.4} />
            </Link>
        </aside>
    );
}

export default function CourseCatalog({ courses = [] }) {
    const [tech, setTech] = useState('all');
    const [visible, setVisible] = useState(PAGE_SIZE);

    const technologies = useMemo(() => {
        const keys = [];
        courses.forEach((course) => {
            const key = techKey(course);
            if (!keys.includes(key)) keys.push(key);
        });
        return keys;
    }, [courses]);

    if (!courses.length) return null;

    const filtered = tech === 'all' ? courses : courses.filter((course) => techKey(course) === tech);
    const shown = filtered.slice(0, visible);
    const remaining = filtered.length - shown.length;

    function pick(key) {
        setTech(key);
        setVisible(PAGE_SIZE);
    }

    return (
        <section data-anim="block" aria-labelledby="catalogue-cours" className="flex flex-col gap-4">
            <div className="flex items-baseline justify-between gap-3">
                <h2 id="catalogue-cours" className="m-0 font-['Manrope',sans-serif] text-[22px] font-extrabold tracking-[-0.02em] text-[var(--dr-text)]">Mes cours</h2>
                <Link href="/roadmaps" className="text-sm font-semibold text-[var(--dr-accent-text)]">Tous les parcours</Link>
            </div>

            {technologies.length > 1 && (
                <div role="group" aria-label="Filtrer par technologie" className="-mx-5 flex gap-2 overflow-x-auto px-5 pb-1 dr-scrollbar-none lg:mx-0 lg:px-0">
                    {['all', ...technologies].map((key) => {
                        const active = tech === key;
                        return (
                            <button
                                key={key}
                                type="button"
                                aria-pressed={active}
                                onClick={() => pick(key)}
                                className={'inline-flex h-11 shrink-0 items-center gap-2 rounded-full border px-4 text-[15px] transition-colors ' + ui.focus + ' ' + (active ? 'border-[var(--dr-accent)] bg-[var(--dr-accent-soft)] font-semibold text-[var(--dr-text)]' : 'border-[var(--dr-border-2)] bg-[var(--dr-surface)] text-[var(--dr-text-2)] hover:text-[var(--dr-text)]')}
                            >
                                {key === 'all' ? 'Tous' : techLabel(key)}
                                {active && key !== 'all' && <Svg d="M6 6l12 12M18 6L6 18" size={14} stroke={2.4} />}
                            </button>
                        );
                    })}
                </div>
            )}

            <p className="m-0 text-[15px] font-semibold text-[var(--dr-text)]" aria-live="polite">
                {filtered.length} cours
            </p>

            <ul className="m-0 flex list-none flex-col gap-3.5 p-0">
                <AnimatePresence initial={false} mode="popLayout">
                    {shown.map((course, index) => (
                        <Fragment key={course.id}>
                            <m.li
                                layout
                                initial={{ opacity: 0, y: 12 }}
                                animate={{ opacity: 1, y: 0, transition: { ...softSpring, delay: Math.min(index, 6) * 0.04 } }}
                                exit={{ opacity: 0, scale: 0.97, transition: { duration: 0.15 } }}
                                className="min-w-0"
                            >
                                <CourseCard course={course} />
                            </m.li>
                            {index === 0 && shown.length > 1 && (
                                <m.li layout initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="min-w-0">
                                    <InfoBanner />
                                </m.li>
                            )}
                        </Fragment>
                    ))}
                </AnimatePresence>
            </ul>

            {remaining > 0 && (
                <button type="button" onClick={() => setVisible((value) => value + PAGE_SIZE)} className={ui.secondary + ' w-full'}>
                    Voir plus de cours ({remaining})
                </button>
            )}
        </section>
    );
}
