import { Head, Link } from '@inertiajs/react';
import {
    ArrowRight,
    BookOpen,
    CheckCircle2,
    Clock3,
    Map,
    Plus,
    Route,
} from 'lucide-react';

import AppLayout from '@/Layouts/AppLayout';
import technologyLogos from '@/Config/technologyLogos';

export default function Index({ roadmaps }) {
    const items = roadmaps?.data ?? [];

    return (
        <AppLayout>
            <Head title="Roadmaps" />

            <div className="space-y-6">
                <header className="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div className="mb-2 flex items-center gap-2 text-[#FF8A3D]">
                            <Route size={16} />
                            <span className="text-[10px] font-bold uppercase tracking-[0.16em]">
                                Apprentissage
                            </span>
                        </div>

                        <h1 className="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                            Mes roadmaps
                        </h1>

                        <p className="mt-1 max-w-xl text-sm leading-6 text-slate-500">
                            Choisis une technologie, ouvre son parcours et avance
                            étape par étape.
                        </p>
                    </div>

                    <Link
                        href="/roadmaps/create"
                        className="inline-flex w-fit items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-[#08111F] shadow-[0_8px_22px_rgba(255,106,0,0.20)] transition hover:bg-[#ff781a]"
                    >
                        <Plus size={17} />
                        Nouvelle roadmap
                    </Link>
                </header>

                {items.length > 0 ? (
                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        {items.map((roadmap) => (
                            <RoadmapCard
                                key={roadmap.id}
                                roadmap={roadmap}
                            />
                        ))}
                    </div>
                ) : (
                    <EmptyState />
                )}

                {roadmaps?.links?.length > 3 && (
                    <Pagination links={roadmaps.links} />
                )}
            </div>
        </AppLayout>
    );
}

function RoadmapCard({ roadmap }) {
    const progress = Math.min(
        Math.max(Number(roadmap.progress ?? 0), 0),
        100,
    );

    const logo = getTechnologyLogo(roadmap.title);

    return (
        <Link
            href={`/roadmaps/${roadmap.id}`}
            className="group block rounded-3xl border border-white/[0.06] bg-[#0D1725] p-4 transition duration-200 hover:-translate-y-0.5 hover:border-[#FF6A00]/20 hover:bg-[#101B2C]"
        >
            <div className="flex items-start gap-4">
                <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.035] p-2.5">
                    {logo ? (
                        <img
                            src={logo}
                            alt=""
                            className="h-full w-full object-contain"
                        />
                    ) : (
                        <Map
                            size={24}
                            className="text-[#FF8A3D]"
                        />
                    )}
                </div>

                <div className="min-w-0 flex-1">
                    <div className="flex items-center justify-between gap-2">
                        <h2 className="truncate text-base font-bold text-white">
                            {roadmap.title}
                        </h2>

                        <ArrowRight
                            size={17}
                            className="shrink-0 text-slate-700 transition group-hover:translate-x-0.5 group-hover:text-[#FF8A3D]"
                        />
                    </div>

                    {roadmap.description && (
                        <p className="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">
                            {roadmap.description}
                        </p>
                    )}
                </div>
            </div>

            <div className="mt-5">
                <div className="flex items-center justify-between text-[11px]">
                    <span className="font-medium text-slate-600">
                        Progression
                    </span>
                    <span className="font-bold text-white">
                        {progress}%
                    </span>
                </div>

                <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                    <div
                        className="h-full rounded-full bg-[#FF6A00] transition-all"
                        style={{ width: `${progress}%` }}
                    />
                </div>
            </div>

            <div className="mt-4 flex items-center gap-3 text-[11px] text-slate-600">
                <span className="inline-flex items-center gap-1.5">
                    <BookOpen size={13} />
                    {roadmap.steps_count ?? 0} étapes
                </span>

                <span className="inline-flex items-center gap-1.5">
                    <CheckCircle2 size={13} />
                    {roadmap.completed_steps_count ?? 0} terminées
                </span>

                <span className="ml-auto inline-flex items-center gap-1.5 text-slate-500">
                    <Clock3 size={13} />
                    {formatStatus(roadmap.status)}
                </span>
            </div>
        </Link>
    );
}

function EmptyState() {
    return (
        <div className="rounded-3xl border border-dashed border-white/[0.08] bg-[#0D1725] p-10 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <Route size={25} />
            </div>

            <h2 className="mt-4 text-base font-semibold text-white">
                Aucun parcours pour le moment
            </h2>

            <p className="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                Crée une roadmap ou lance le parcours Laravel pour commencer
                à apprendre.
            </p>

            <Link
                href="/roadmaps/create"
                className="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-[#08111F]"
            >
                <Plus size={16} />
                Créer une roadmap
            </Link>
        </div>
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
                    className={[
                        'rounded-lg px-3 py-2 text-xs font-semibold transition',
                        link.active
                            ? 'bg-[#FF6A00] text-[#08111F]'
                            : link.url
                              ? 'bg-[#0D1725] text-slate-500 hover:text-white'
                              : 'cursor-default bg-transparent text-slate-700',
                    ].join(' ')}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </nav>
    );
}

function getTechnologyLogo(title) {
    if (!title) return null;

    const normalized = title
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '')
        .replace(/_/g, '-');

    return (
        technologyLogos[normalized] ??
        technologyLogos[normalized.replace(/\./g, '')] ??
        null
    );
}

function formatStatus(status) {
    const labels = {
        draft: 'Brouillon',
        active: 'En cours',
        completed: 'Terminée',
        archived: 'Archivée',
    };

    return labels[status] ?? 'Brouillon';
}
