import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    ChevronRight,
    Circle,
    CircleCheck,
    Clock3,
    Lock,
    Pencil,
    Play,
} from 'lucide-react';

import AppLayout from '@/Layouts/AppLayout';
import technologyLogos from '@/Config/technologyLogos';

export default function Show({ roadmap }) {
    const progress = clampProgress(roadmap?.progress);
    const steps = Array.isArray(roadmap?.steps) ? roadmap.steps : [];

    const currentStep =
        steps.find((step) => step.status === 'in_progress') ??
        steps.find((step) => step.status === 'todo') ??
        steps[0] ??
        null;

    const logo = getTechnologyLogo(roadmap?.technology);

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
                                            alt={`${roadmap?.title ?? 'Technologie'} logo`}
                                            className="h-full w-full object-contain"
                                        />
                                    ) : (
                                        <span className="text-2xl font-bold text-[#FF8A3D]">
                                            {getInitial(roadmap?.title)}
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

                <section>
                    <div className="mb-4">
                        <h2 className="text-lg font-bold text-white">
                            Ton parcours
                        </h2>

                        <p className="mt-1 text-xs text-slate-500">
                            Choisis une étape pour continuer ton cours.
                        </p>
                    </div>

                    {steps.length > 0 ? (
                        <div className="space-y-2">
                            {steps.map((step, index) => (
                                <StepCard
                                    key={step.id}
                                    step={step}
                                    index={index}
                                />
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-2xl border border-dashed border-white/[0.08] bg-[#0D1725] p-8 text-center">
                            <p className="text-sm text-slate-500">
                                Cette feuille de route ne contient pas encore d'étapes.
                            </p>

                            <Link
                                href={`/roadmaps/${roadmap?.id}/edit`}
                                className="mt-4 inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-semibold text-[#08111F] transition hover:bg-[#ff781a]"
                            >
                                <Pencil size={16} />
                                Configurer le parcours
                            </Link>
                        </div>
                    )}
                </section>

                {currentStep && (
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

                            <ArrowRight
                                size={19}
                                className="shrink-0 text-[#FF8A3D]"
                            />
                        </Link>
                    </section>
                )}
            </div>
        </AppLayout>
    );
}

function StepCard({ step, index }) {
    const completed = step.status === 'completed';
    const inProgress = step.status === 'in_progress';
    const blocked = step.status === 'blocked';

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
            </div>

            {!blocked && (
                <ChevronRight
                    size={17}
                    className="shrink-0 text-slate-700"
                />
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
    if (status === 'completed') {
        return <CircleCheck size={17} className="text-emerald-400" />;
    }

    if (status === 'in_progress') {
        return <Clock3 size={17} className="text-[#FF8A3D]" />;
    }

    if (status === 'blocked') {
        return <Lock size={16} className="text-slate-600" />;
    }

    return <Circle size={17} className="text-slate-700" />;
}

function getTechnologyLogo(title) {
    if (!title) {
        return null;
    }

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
