import { Head, Link, router } from "@inertiajs/react";
import {
    ArrowLeft,
    ArrowRight,
    Check,
    CheckCircle2,
    Circle,
    Clock3,
    Code2,
    Lock,
    Loader2,
} from "lucide-react";

import technologyLogos from "@/Config/technologyLogos";

import AppLayout from "@/Layouts/AppLayout";
import { useEffect, useState } from "react";

export default function Show({
    step,
    roadmap,
    previous_step,
    next_step,
}) {
    const [displayStatus, setDisplayStatus] = useState(step.status);
    const [statusLoading, setStatusLoading] = useState(false);

    const completed = displayStatus === "completed";
    const blocked = displayStatus === "blocked";

    useEffect(() => {
        setDisplayStatus(step.status);
    }, [step.status]);

    function changeStatus(status) {
        if (statusLoading || status === displayStatus) {
            return;
        }

        const previousStatus = displayStatus;

        setStatusLoading(true);
        setDisplayStatus(status);

        router.patch(
            `/steps/${step.id}/status`,
            { status },
            {
                preserveScroll: true,
                preserveState: true,
                only: ["step"],
                onError: () => setDisplayStatus(previousStatus),
                onFinish: () => setStatusLoading(false),
            },
        );
    }

    return (
        <AppLayout>
            <Head title={step.title} />

            <div className="mx-auto max-w-5xl space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <Link
                        href={`/roadmaps/${roadmap.id}`}
                        className="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 transition hover:text-white"
                    >
                        <ArrowLeft size={16} />
                        {roadmap.title}
                    </Link>

                    {roadmap.technology && technologyLogos[roadmap.technology] && (
                        <img
                            src={technologyLogos[roadmap.technology]}
                            alt=""
                            className="h-9 w-9 object-contain"
                        />
                    )}
                </div>

                <section className="rounded-3xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725] p-6 sm:p-8">
                    <div className="flex flex-wrap items-center gap-3">
                        <span className="rounded-full bg-[#FF6A00]/10 px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#FF8A3D]">
                            Étape {step.position}
                        </span>
                        <StatusBadge status={displayStatus} />
                        {step.estimated_minutes && (
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-white/[0.04] px-3 py-1.5 text-[10px] font-semibold text-slate-500">
                                <Clock3 size={13} />
                                {step.estimated_minutes} min
                            </span>
                        )}
                    </div>

                    <h1 className="mt-5 text-2xl font-bold tracking-tight text-white sm:text-4xl">
                        {step.title}
                    </h1>

                    {step.description && (
                        <p className="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                            {step.description}
                        </p>
                    )}
                </section>

                <div className="grid gap-6 lg:grid-cols-[minmax(0,1fr)_280px]">
                    <main className="space-y-6">
                        {step.objective && (
                            <section className="rounded-3xl border border-[#FF6A00]/15 bg-[#FF6A00]/[0.04] p-5 sm:p-7">
                                <p className="text-[10px] font-semibold uppercase tracking-[0.16em] text-[#FF8A3D]">
                                    Objectif
                                </p>
                                <p className="mt-3 text-sm leading-7 text-slate-300">
                                    {step.objective}
                                </p>
                            </section>
                        )}

                        <article className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-8">
                            <div className="mb-6 flex items-center gap-3">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-white/[0.04] text-[#FF8A3D]">
                                    <Code2 size={18} />
                                </div>
                                <div>
                                    <p className="text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-600">
                                        Cours
                                    </p>
                                    <h2 className="mt-0.5 text-lg font-bold text-white">
                                        Comprendre la notion
                                    </h2>
                                </div>
                            </div>

                            {step.content ? (
                                <CourseContent content={step.content} />
                            ) : (
                                <div className="space-y-4 text-sm leading-7 text-slate-400">
                                    <p>
                                        Le contenu pédagogique de cette étape
                                        n&apos;a pas encore été renseigné.
                                    </p>
                                    <p>
                                        Cette zone accueillera le cours,
                                        les explications et les exemples
                                        nécessaires pour progresser.
                                    </p>
                                </div>
                            )}
                        </article>

                        {step.code_example && (
                            <section className="overflow-hidden rounded-3xl border border-white/[0.06] bg-[#08111F]">
                                <div className="flex items-center gap-2 border-b border-white/[0.06] px-5 py-4">
                                    <Code2 size={16} className="text-[#FF8A3D]" />
                                    <span className="text-xs font-semibold text-white">
                                        Exemple de code
                                    </span>
                                </div>

                                <pre className="overflow-x-auto p-5 text-xs leading-6 text-slate-300">
                                    <code>{step.code_example}</code>
                                </pre>
                            </section>
                        )}

                        {!blocked && (
                            <section className="rounded-2xl border border-white/[0.06] bg-[#111D2D] p-4 sm:p-5">
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p className="text-sm font-semibold text-white">
                                            Ton avancement
                                        </p>
                                        <p className="mt-1 text-xs text-slate-500">
                                            Termine cette leçon quand tu maîtrises
                                            son contenu.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        disabled={statusLoading}
                                        onClick={() =>
                                            changeStatus(
                                                completed
                                                    ? "in_progress"
                                                    : "completed",
                                            )
                                        }
                                        className={[
                                            "inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition",
                                            completed
                                                ? "border border-white/[0.08] bg-white/[0.04] text-slate-300 hover:bg-white/[0.07]"
                                                : "bg-[#FF6A00] text-white shadow-[0_8px_22px_rgba(255,106,0,0.24)] hover:bg-[#ff781a]",
                                        ].join(" ")}
                                    >
                                        {statusLoading ? (
                                            <>
                                                <Loader2 size={17} className="animate-spin" />
                                                Enregistrement...
                                            </>
                                        ) : (
                                            <>
                                                <Check size={17} />
                                                {completed
                                                    ? "Remettre en cours"
                                                    : "Marquer comme terminée"}
                                            </>
                                        )}
                                    </button>
                                </div>
                            </section>
                        )}
                    </main>

                    <aside className="space-y-4 lg:sticky lg:top-6 lg:self-start">
                        <section className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                Navigation
                            </p>

                            <div className="mt-4 space-y-2">
                                {previous_step ? (
                                    <Link
                                        href={`/steps/${previous_step.id}`}
                                        className="flex items-center gap-3 rounded-xl border border-white/[0.05] p-3 transition hover:bg-white/[0.03]"
                                    >
                                        <ArrowLeft size={15} className="text-slate-600" />
                                        <span className="min-w-0 flex-1 truncate text-xs font-semibold text-slate-300">
                                            {previous_step.title}
                                        </span>
                                    </Link>
                                ) : null}

                                {next_step ? (
                                    <Link
                                        href={`/steps/${next_step.id}`}
                                        className="flex items-center gap-3 rounded-xl border border-[#FF6A00]/10 bg-[#FF6A00]/[0.035] p-3 transition hover:bg-[#FF6A00]/[0.07]"
                                    >
                                        <span className="min-w-0 flex-1 truncate text-xs font-semibold text-white">
                                            {next_step.title}
                                        </span>
                                        <ArrowRight size={15} className="text-[#FF8A3D]" />
                                    </Link>
                                ) : null}
                            </div>
                        </section>

                        <section className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4">
                            <p className="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-600">
                                État
                            </p>

                            <div className="mt-4 flex items-center gap-3">
                                <StatusBadge status={displayStatus} />
                            </div>
                        </section>
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}

function CourseContent({ content }) {
    const blocks = content.split(/\n\s*\n/);

    return (
        <div className="space-y-5 text-sm leading-7 text-slate-400">
            {blocks.map((block, index) => {
                const text = block.trim();

                if (!text) return null;

                if (text.startsWith("## ")) {
                    return (
                        <h3
                            key={index}
                            className="pt-2 text-base font-bold text-white"
                        >
                            {text.slice(3)}
                        </h3>
                    );
                }

                if (text.startsWith("### ")) {
                    return (
                        <h4
                            key={index}
                            className="pt-1 text-sm font-bold text-white"
                        >
                            {text.slice(4)}
                        </h4>
                    );
                }

                if (text.startsWith("- ")) {
                    return (
                        <ul
                            key={index}
                            className="list-disc space-y-2 pl-5 text-slate-400"
                        >
                            {text.split("\n").map((item, itemIndex) => (
                                <li key={itemIndex}>
                                    {item.replace(/^- /, "")}
                                </li>
                            ))}
                        </ul>
                    );
                }

                return <p key={index}>{text}</p>;
            })}
        </div>
    );
}

function StatusBadge({ status }) {
    const config = {
        completed: ["Terminée", "bg-emerald-500/10 text-emerald-400", CheckCircle2],
        in_progress: ["En cours", "bg-[#FF6A00]/10 text-[#FF8A3D]", Clock3],
        blocked: ["Bloquée", "bg-red-500/10 text-red-400", Lock],
        todo: ["À faire", "bg-white/[0.05] text-slate-500", Circle],
    };

    const [label, className, Icon] = config[status] ?? config.todo;

    return (
        <span
            className={[
                "inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold",
                className,
            ].join(" ")}
        >
            <Icon size={13} />
            {label}
        </span>
    );
}
