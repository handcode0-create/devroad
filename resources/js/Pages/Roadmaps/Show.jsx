import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    BookOpen,
    Check,
    CheckCircle2,
    ChevronRight,
    Circle,
    Clock3,
    ExternalLink,
    Info,
    Lock,
    Pencil,
    Play,
    BookMarked,
} from 'lucide-react';
import { useState } from 'react';

import AppLayout from '@/Layouts/AppLayout';
import technologyLogos from '@/Config/technologyLogos';

const TABS = [
    { id: 'steps', label: 'Étapes', icon: BookOpen },
    { id: 'resources', label: 'Ressources', icon: BookMarked },
    { id: 'about', label: 'À propos', icon: Info },
];

export default function Show({ roadmap }) {
    const [activeTab, setActiveTab] = useState('steps');
    const progress = clampProgress(roadmap?.progress);
    const steps = Array.isArray(roadmap?.steps) ? roadmap.steps : [];
    const resources = Array.isArray(roadmap?.resources) ? roadmap.resources : [];
    const logo = getTechnologyLogo(roadmap?.technology);

    const currentStep =
        steps.find((step) => step.status !== 'completed') ??
        null;

    return (
        <AppLayout>
            <Head title={roadmap?.title ?? 'Feuille de route'} />

            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <Link
                        href="/roadmaps"
                        className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-white"
                    >
                        <ArrowLeft size={16} />
                        Toutes les feuilles de route
                    </Link>

                    <Link
                        href={`/roadmaps/${roadmap?.id}/edit`}
                        className="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 transition hover:text-white"
                    >
                        <Pencil size={14} />
                        Modifier
                    </Link>
                </div>

                <section className="overflow-hidden rounded-3xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725]">
                    <div className="p-5 sm:p-7">
                        <div className="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                            <div className="flex min-w-0 items-start gap-4">
                                <div className="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/[0.07] bg-white/[0.04] p-3">
                                    {logo ? (
                                        <img
                                            src={logo}
                                            alt={`${roadmap?.technology ?? roadmap?.title ?? 'Technologie'} logo`}
                                            className="h-full w-full object-contain"
                                        />
                                    ) : (
                                        <span className="text-2xl font-bold text-[#FF8A3D]">
                                            {getInitial(roadmap?.technology ?? roadmap?.title)}
                                        </span>
                                    )}
                                </div>

                                <div className="min-w-0">
                                    <span className="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#FF8A3D]">
                                        Feuille de route
                                    </span>

                                    <h1 className="mt-1 truncate text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                        {roadmap?.title ?? 'Sans titre'}
                                    </h1>

                                    {roadmap?.technology && (
                                        <p className="mt-1 text-xs font-semibold text-slate-600">
                                            {formatTechnology(roadmap.technology)}
                                        </p>
                                    )}

                                    {roadmap?.description && (
                                        <p className="mt-2 max-w-xl text-sm leading-6 text-slate-500">
                                            {roadmap.description}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <span className="inline-flex w-fit shrink-0 rounded-full bg-[#FF6A00]/10 px-3 py-1.5 text-xs font-semibold text-[#FF8A3D]">
                                {formatStatus(roadmap?.status)}
                            </span>
                        </div>

                        <div className="mt-7">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-medium text-slate-500">
                                    Progression
                                </span>
                                <span className="text-sm font-bold text-white">
                                    {progress}%
                                </span>
                            </div>

                            <div className="mt-2 h-2 overflow-hidden rounded-full bg-white/[0.06]">
                                <div
                                    className="h-full rounded-full bg-[#FF6A00] transition-all duration-500"
                                    style={{ width: `${progress}%` }}
                                />
                            </div>

                            <div className="mt-2 text-[11px] text-slate-600">
                                {roadmap?.completed_steps_count ?? 0} / {roadmap?.steps_count ?? 0} étapes terminées
                            </div>
                        </div>
                    </div>
                </section>

                <nav className="grid grid-cols-3 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-1.5" aria-label="Navigation de la roadmap">
                    {TABS.map((tab) => {
                        const Icon = tab.icon;
                        const active = activeTab === tab.id;

                        return (
                            <button
                                key={tab.id}
                                type="button"
                                onClick={() => setActiveTab(tab.id)}
                                className={[
                                    'inline-flex items-center justify-center gap-2 rounded-xl px-3 py-3 text-xs font-bold transition sm:text-sm',
                                    active
                                        ? 'bg-[#FF6A00] text-[#08111F] shadow-[0_8px_20px_rgba(255,106,0,0.16)]'
                                        : 'text-slate-500 hover:bg-white/[0.03] hover:text-white',
                                ].join(' ')}
                            >
                                <Icon size={16} />
                                {tab.label}
                            </button>
                        );
                    })}
                </nav>

                {activeTab === 'steps' && (
                    <StepsTab
                        steps={steps}
                        currentStep={currentStep}
                        roadmapId={roadmap?.id}
                    />
                )}

                {activeTab === 'resources' && (
                    <ResourcesTab resources={resources} />
                )}

                {activeTab === 'about' && (
                    <AboutTab roadmap={roadmap} />
                )}
            </div>
        </AppLayout>
    );
}

function StepsTab({ steps, currentStep, roadmapId }) {
    return (
        <>
            <section>
                <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 className="text-lg font-bold text-white">Tes cours</h2>
                        <p className="mt-1 text-xs text-slate-500">
                            Crée, modifie ou supprime les cours de ce parcours. Chaque cours est une étape pédagogique indépendante.
                        </p>
                    </div>
                    <Link
                        href={`/roadmaps/${roadmapId}/steps/create`}
                        className="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-xs font-bold text-[#08111F] transition hover:bg-[#ff781a]"
                    >
                        <Pencil size={15} />
                        Ajouter un cours
                    </Link>
                </div>

                {steps.length > 0 ? (
                    <div className="space-y-2">
                        {steps.map((step, index) => (
                            <StepCard
                            key={step.id}
                            step={step}
                            index={index}
                            locked={
                                step.status === "todo" &&
                                currentStep?.id !== step.id
                            }
                        />
                        ))}
                    </div>
                ) : (
                    <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
                        <p className="text-sm text-slate-500">
                            Cette feuille de route ne contient pas encore d'étapes.
                        </p>

                        <Link
                            href={`/roadmaps/${roadmapId}/edit`}
                            className="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-[#08111F] transition hover:bg-[#ff781a]"
                        >
                            <Pencil size={16} />
                            Configurer le parcours
                        </Link>
                    </div>
                )}
            </section>

            {currentStep ? (
                <section className="sticky bottom-4 z-20">
                    <Link
                        href={`/steps/${currentStep.id}`}
                        className="flex items-center justify-between rounded-2xl border border-[#FF6A00]/20 bg-[#111D2D]/95 p-4 shadow-[0_18px_40px_rgba(0,0,0,0.4)] backdrop-blur-xl"
                    >
                        <div className="flex min-w-0 items-center gap-3">
                            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FF6A00] text-[#0B3A82]">
                                <Play size={17} fill="currentColor" />
                            </div>

                            <div className="min-w-0">
                                <p className="text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-600">
                                    Continuer
                                </p>
                                <p className="mt-1 truncate text-sm font-bold text-white">
                                    {currentStep.title}
                                </p>
                            </div>
                        </div>

                        <ArrowRight size={19} className="shrink-0 text-[#FF8A3D]" />
                    </Link>
                </section>
            ) : (
                <section className="rounded-2xl border border-emerald-500/10 bg-emerald-500/[0.035] p-5 sm:p-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-emerald-400">
                                Parcours terminé
                            </p>
                            <h2 className="mt-1 text-lg font-bold text-white">
                                Tu as terminé toutes les étapes.
                            </h2>
                            <p className="mt-1 text-sm leading-6 text-slate-500">
                                Tu peux revoir un cours terminé depuis la liste ci-dessus.
                            </p>
                        </div>

                        <span className="inline-flex items-center gap-2 rounded-xl bg-emerald-500/10 px-4 py-2.5 text-sm font-bold text-emerald-400">
                            <CheckCircle2 size={17} />
                            100% complété
                        </span>
                    </div>
                </section>
            )}
        </>
    );
}

function ResourcesTab({ resources }) {
    return (
        <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
            <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                    <BookMarked size={18} />
                </div>
                <div>
                    <h2 className="text-lg font-bold text-white">Ressources</h2>
                    <p className="mt-1 text-xs leading-5 text-slate-500">
                        Documentation et ressources utiles pour approfondir cette technologie.
                    </p>
                </div>
            </div>

            {resources.length > 0 ? (
                <div className="mt-6 grid gap-3 sm:grid-cols-2">
                    {resources.map((resource) => (
                        <a
                            key={resource.url}
                            href={resource.url}
                            target="_blank"
                            rel="noreferrer"
                            className="group flex items-center gap-3 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4 transition hover:border-[#FF6A00]/20 hover:bg-white/[0.035]"
                        >
                            <div className="min-w-0 flex-1">
                                <p className="text-sm font-semibold text-white">
                                    {resource.label}
                                </p>
                                <p className="mt-1 truncate text-[11px] text-slate-600">
                                    {resource.url}
                                </p>
                            </div>
                            <ExternalLink size={16} className="shrink-0 text-slate-600 group-hover:text-[#FF8A3D]" />
                        </a>
                    ))}
                </div>
            ) : (
                <p className="mt-6 text-sm text-slate-500">
                    Aucune ressource n'est encore configurée pour cette technologie.
                </p>
            )}
        </section>
    );
}

function AboutTab({ roadmap }) {
    return (
        <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
            <div className="flex items-start gap-3">
                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                    <Info size={18} />
                </div>
                <div>
                    <h2 className="text-lg font-bold text-white">À propos</h2>
                    <p className="mt-1 text-xs text-slate-500">
                        Informations sur ce parcours.
                    </p>
                </div>
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-2">
                <InfoCard label="Technologie" value={formatTechnology(roadmap?.technology)} />
                <InfoCard label="Statut" value={formatStatus(roadmap?.status)} />
                <InfoCard label="Étapes" value={String(roadmap?.steps_count ?? 0)} />
                <InfoCard label="Progression" value={`${clampProgress(roadmap?.progress)}%`} />
            </div>

            <div className="mt-4 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
                <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                    Présentation de la technologie
                </p>
                <p className="mt-3 text-sm leading-7 text-slate-400">
                    {roadmap?.about?.description ?? roadmap?.description ?? 'Aucune présentation disponible.'}
                </p>
            </div>
        </section>
    );
}

function InfoCard({ label, value }) {
    return (
        <div className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
            <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                {label}
            </p>
            <p className="mt-2 text-sm font-bold text-white">{value}</p>
        </div>
    );
}

function StepCard({ step, index, locked = false }) {
    const completed = step.status === 'completed';
    const inProgress = step.status === 'in_progress';
    const blocked = step.status === 'blocked' || locked;

    const cardClass = completed
        ? 'border-emerald-500/10 bg-emerald-500/[0.035]'
        : inProgress
          ? 'border-[#FF6A00]/20 bg-[#FF6A00]/[0.045]'
          : blocked
            ? 'border-white/[0.04] bg-white/[0.015] opacity-70'
            : 'border-white/[0.06] bg-[#0D1725] hover:border-white/[0.10] hover:bg-[#101B2C]';

    const iconClass = completed
        ? 'bg-emerald-500/10 text-emerald-400'
        : inProgress
          ? 'bg-[#FF6A00] text-[#0B3A82]'
          : blocked
            ? 'bg-white/[0.04] text-slate-600'
            : 'bg-white/[0.04] text-slate-500';

    const content = (
        <div className={`flex items-center gap-4 rounded-2xl border p-4 transition-all ${cardClass}`}>
            <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full ${iconClass}`}>
                {completed ? (
                    <Check size={18} />
                ) : inProgress ? (
                    <Play size={17} fill="currentColor" />
                ) : blocked ? (
                    <Lock size={16} />
                ) : (
                    <span className="text-xs font-bold">{index + 1}</span>
                )}
            </div>

            <div className="min-w-0 flex-1">
                <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0">
                        <p className="text-[10px] font-medium uppercase tracking-[0.12em] text-slate-600">
                            Étape {index + 1}
                        </p>
                        <h3 className="mt-1 truncate text-sm font-semibold text-white">
                            {step.title}
                        </h3>
                    </div>
                    <StatusIcon status={step.status} />
                </div>

                {step.description && (
                    <p className="mt-1 line-clamp-2 text-xs leading-5 text-slate-600">
                        {step.description}
                    </p>
                )}

                {locked && (
                    <p className="mt-2 inline-flex items-center gap-1 text-[10px] font-semibold text-slate-600">
                        <Lock size={11} />
                        Termine les étapes précédentes pour débloquer ce cours.
                    </p>
                )}
            </div>

            {!blocked && (
                <div className="flex shrink-0 items-center gap-1">
                    <Link
                        href={`/steps/${step.id}/edit`}
                        onClick={(event) => event.stopPropagation()}
                        className="rounded-lg p-2 text-slate-600 transition hover:bg-white/[0.05] hover:text-white"
                        aria-label={`Modifier ${step.title}`}
                    >
                        <Pencil size={14} />
                    </Link>
                    <ChevronRight size={17} className="text-slate-700" />
                </div>
            )}
        </div>
    );

    return blocked ? (
        <div>{content}</div>
    ) : (
        <Link href={`/steps/${step.id}`}>{content}</Link>
    );
}

function StatusIcon({ status }) {
    if (status === 'completed') return <CheckCircle2 size={17} className="text-emerald-400" />;
    if (status === 'in_progress') return <Clock3 size={17} className="text-[#FF8A3D]" />;
    if (status === 'blocked') return <Lock size={16} className="text-slate-600" />;
    return <Circle size={17} className="text-slate-700" />;
}

function getTechnologyLogo(technology) {
    return technology ? technologyLogos[technology] ?? null : null;
}

function formatTechnology(technology) {
    const labels = {
        laravel: 'Laravel',
        nextjs: 'Next.js',
        react: 'React',
        javascript: 'JavaScript',
        typescript: 'TypeScript',
        php: 'PHP',
        html: 'HTML',
        css: 'CSS',
        tailwind: 'Tailwind CSS',
        node: 'Node.js',
        git: 'Git',
        github: 'GitHub',
        docker: 'Docker',
        mysql: 'MySQL',
        postgresql: 'PostgreSQL',
    };

    return labels[technology] ?? technology ?? 'Technologie';
}

function getInitial(title) {
    return title?.trim()?.charAt(0)?.toUpperCase() ?? 'D';
}

function clampProgress(value) {
    return Math.min(Math.max(Number(value ?? 0), 0), 100);
}

function formatStatus(status) {
    const labels = {
        draft: 'Brouillon',
        active: 'En cours',
        completed: 'Terminée',
        archived: 'Archivée',
    };

    return labels[status] ?? status ?? 'Brouillon';
}
