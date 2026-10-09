import { Head, Link, router, useForm } from "@inertiajs/react";
import { Loader2 } from "lucide-react";
import { AnimatePresence, m, softSpring } from "@/Components/Ui/Motion";
import { ICON, MotionLink, ProgressBar, Svg, TechTile, ui } from "@/Components/Ui/Design";

import AppLayout from "@/Layouts/AppLayout";
import CourseContent from "@/Components/Learning/CourseContent";
import CodeWorkspace from "@/Components/Learning/CodeWorkspace";
import { useEffect, useState } from "react";

export default function Show({
    step,
    roadmap,
    previous_step,
    next_step,
}) {
    const [displayStatus, setDisplayStatus] = useState(step.status);
    const [statusLoading, setStatusLoading] = useState(false);
    const [exerciseCompleted, setExerciseCompleted] = useState(
        Boolean(step.exercise?.completed),
    );
    const [showMemoForm, setShowMemoForm] = useState(false);
    const [readingProgress, setReadingProgress] = useState(0);
    const [devlabOpening, setDevlabOpening] = useState(false);
    const [roadmapCompletedSteps, setRoadmapCompletedSteps] = useState(
        Number(roadmap.completed_steps_count ?? 0),
    );

    const completed = displayStatus === "completed";
    const blocked = displayStatus === "blocked";
    const hasExercise = Boolean(step.exercise);

    useEffect(() => {
        setDisplayStatus(step.status);
        setExerciseCompleted(Boolean(step.exercise?.completed));
    }, [step.status, step.exercise?.completed]);

    useEffect(() => {
        const updateReadingProgress = () => {
            const scrollableHeight =
                document.documentElement.scrollHeight - window.innerHeight;

            if (scrollableHeight <= 0) {
                setReadingProgress(100);
                return;
            }

            const progress = (window.scrollY / scrollableHeight) * 100;

            setReadingProgress(Math.min(100, Math.max(0, progress)));
        };

        updateReadingProgress();
        window.addEventListener("scroll", updateReadingProgress, {
            passive: true,
        });
        window.addEventListener("resize", updateReadingProgress);

        return () => {
            window.removeEventListener("scroll", updateReadingProgress);
            window.removeEventListener("resize", updateReadingProgress);
        };
    }, []);

    async function openInDevLab() {
        if (devlabOpening) {
            return;
        }

        setDevlabOpening(true);

        try {
            const response = await fetch(
                `/devlab/projects/for-step/${step.id}`,
                {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                        "X-CSRF-TOKEN":
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute("content") ?? "",
                    },
                },
            );

            const result = await response.json();

            if (!response.ok || !result.project?.id) {
                throw new Error(
                    result.message ?? "Impossible d'ouvrir DevLab.",
                );
            }

            window.location.assign(
                `/devlab?project=${result.project.id}`,
            );
        } catch (error) {
            setDevlabOpening(false);
        }
    }

    function changeStatus(status) {
        if (statusLoading || status === displayStatus) {
            return;
        }

        if (status === "completed" && hasExercise && !exerciseCompleted) {
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
                onSuccess: () => {
                    setRoadmapCompletedSteps((current) =>
                        status === "completed"
                            ? Math.min(current + 1, Number(roadmap.steps_count ?? current + 1))
                            : Math.max(current - 1, 0),
                    );
                },
                onFinish: () => setStatusLoading(false),
            },
        );
    }

    function changeExercise(completed) {
        const previousValue = exerciseCompleted;

        setExerciseCompleted(completed);

        router.patch(
            `/steps/${step.id}/exercise`,
            { completed },
            {
                preserveScroll: true,
                preserveState: true,
                only: ["step"],
                onError: () => setExerciseCompleted(previousValue),
            },
        );
    }

    const memoForm = useForm({
        title: `Notes — ${step.title}`,
        content: buildMemoContent(step),
        tags: [],
        is_favorite: false,
    });

    function createMemo(event) {
        event.preventDefault();

        memoForm.post(`/steps/${step.id}/memo`, {
            preserveScroll: true,
            onSuccess: () => setShowMemoForm(false),
        });
    }

    const total = Number(roadmap.steps_count ?? 0);
    const canComplete = !(hasExercise && !exerciseCompleted && !completed);

    return (
        <AppLayout>
            <Head title={step.title} />

            {/* Progression de lecture */}
            <div className="fixed inset-x-0 top-0 z-50 h-[3px]" role="progressbar" aria-label="Progression de lecture" aria-valuemin={0} aria-valuemax={100} aria-valuenow={Math.round(readingProgress)}>
                <div className="h-full bg-[var(--dr-accent)] transition-[width] duration-150" style={{ width: `${readingProgress}%` }} />
            </div>

            <div className="mx-auto flex w-full min-w-0 max-w-6xl flex-col gap-6 font-['Figtree',system-ui,sans-serif] text-[var(--dr-text)] lg:gap-7">
                {/* En-tête de l'étape */}
                <header className="flex flex-col gap-4">
                    <Link href={`/roadmaps/${roadmap.id}`} className={"-ml-1 inline-flex min-h-9 w-fit max-w-full items-center gap-1.5 rounded-lg px-1 text-sm font-medium text-[var(--dr-text-2)] hover:text-[var(--dr-text)] " + ui.focus}>
                        <Svg d="M19 12H5M11 6l-6 6 6 6" size={16} />
                        <TechTile technology={roadmap.technology} title={roadmap.title} size={22} />
                        <span className="truncate">{roadmap.title}</span>
                    </Link>
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="inline-flex h-7 items-center rounded-full bg-[var(--dr-accent-soft)] px-3 text-xs font-bold text-[var(--dr-accent-text)]">Étape {step.position}{total ? ` sur ${total}` : ""}</span>
                        <StatusBadge status={displayStatus} />
                        {step.estimated_minutes && (
                            <span className="inline-flex h-7 items-center gap-1.5 rounded-full bg-[var(--dr-field)] px-3 text-xs font-semibold text-[var(--dr-text-2)]">
                                <Svg d={ICON.clock} size={13} />{step.estimated_minutes} min
                            </span>
                        )}
                    </div>
                    <h1 className="m-0 font-['Manrope',sans-serif] text-[30px] font-extrabold leading-[1.1] tracking-[-0.03em] [overflow-wrap:anywhere] lg:text-[40px]">{step.title}</h1>
                    {step.description && <p className="m-0 max-w-3xl text-[15px] leading-[1.6] text-[var(--dr-text-2)] [overflow-wrap:anywhere]">{step.description}</p>}
                    {total > 0 && (
                        <div className="flex max-w-xl items-center gap-3">
                            <ProgressBar value={progressFromCount(roadmapCompletedSteps, total)} className="h-1.5 flex-1" />
                            <span className="text-xs font-semibold tabular-nums text-[var(--dr-text-2)]">{roadmapCompletedSteps} / {total} étapes</span>
                        </div>
                    )}
                </header>

                <div className="grid min-w-0 gap-6 lg:grid-cols-[minmax(0,1fr)_300px] lg:items-start">
                    <main className="flex min-w-0 flex-col gap-5">
                        {step.objective && (
                            <section className="flex gap-3.5 rounded-[18px] border border-[color-mix(in_srgb,var(--dr-accent)_30%,transparent)] bg-[var(--dr-accent-soft)] p-5">
                                <span aria-hidden="true" className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--dr-accent)] text-[var(--dr-ink)]"><Svg d={["M12 3a9 9 0 100 18 9 9 0 000-18z", "M12 8a4 4 0 100 8 4 4 0 000-8z", "M12 12h.01"]} size={17} stroke={2.2} /></span>
                                <div className="min-w-0">
                                    <h2 className="m-0 text-xs font-bold uppercase tracking-[0.12em] text-[var(--dr-accent-text)]">Objectif</h2>
                                    <p className="m-0 mt-1.5 text-[15px] leading-[1.6] text-[var(--dr-text)] [overflow-wrap:anywhere]">{step.objective}</p>
                                </div>
                            </section>
                        )}

                        <article className={ui.card + " min-w-0 overflow-hidden p-5 sm:p-8"}>
                            <h2 className={ui.sectionTitle + " m-0 mb-5"}>Le cours</h2>
                            {step.content ? (
                                <CourseContent content={step.content} />
                            ) : (
                                <p className="m-0 text-[15px] leading-7 text-[var(--dr-text-2)]">Le contenu de cette étape n’a pas encore été rédigé. Tu peux l’ajouter depuis « Modifier l’étape ».</p>
                            )}
                        </article>

                        {step.code_example && (
                            <section className="min-w-0 overflow-hidden rounded-[18px] border border-[var(--dr-border)] bg-[var(--dr-field)]">
                                <div className="flex items-center gap-2 border-b border-[var(--dr-border)] px-5 py-3">
                                    <Svg d={ICON.code} size={16} className="text-[var(--dr-accent-text)]" />
                                    <span className="text-sm font-semibold">Exemple de code</span>
                                    <CopyButton text={step.code_example} />
                                </div>
                                <pre className="m-0 overflow-x-auto p-5 font-['JetBrains_Mono',ui-monospace,monospace] text-[13px] leading-6 text-[var(--dr-text)]"><code>{step.code_example}</code></pre>
                            </section>
                        )}

                        {step.workspace?.enabled && (
                            <section className={ui.card + " flex flex-col gap-4 p-5 sm:flex-row sm:items-center"}>
                                <span aria-hidden="true" className="flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]"><Svg d={ICON.code} size={20} /></span>
                                <div className="min-w-0 flex-1">
                                    <h2 className="m-0 text-base font-semibold">Pratique dans DevLab</h2>
                                    <p className="m-0 mt-1 text-sm text-[var(--dr-text-2)]">{step.devlab_project ? `Projet lié : ${step.devlab_project.name}` : "Un projet DevLab lié à cette étape sera créé pour toi."}</p>
                                </div>
                                <button type="button" onClick={openInDevLab} disabled={devlabOpening} aria-busy={devlabOpening} className={ui.primary + " shrink-0"}>
                                    {devlabOpening ? <Loader2 size={16} className="animate-spin" /> : <Svg d={ICON.code} size={16} stroke={2.2} />}
                                    {devlabOpening ? "Ouverture…" : step.devlab_project ? "Continuer dans DevLab" : "Ouvrir dans DevLab"}
                                </button>
                            </section>
                        )}

                        {step.workspace?.enabled && <CodeWorkspace workspace={step.workspace} stepId={step.id} />}

                        {hasExercise && (
                            <section className={ui.card + " min-w-0 p-5 sm:p-7"} aria-labelledby="exercice-titre">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="text-xs font-bold uppercase tracking-[0.12em] text-[var(--dr-accent-text)]">Exercice pratique</span>
                                    <AnimatePresence>{exerciseCompleted && <m.span initial={{ scale: 0.6, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} exit={{ scale: 0.6, opacity: 0 }} transition={softSpring} className="inline-flex h-6 items-center gap-1 rounded-full bg-[var(--dr-accent)] px-2.5 text-xs font-bold text-[var(--dr-ink)]"><Svg d={ICON.check} size={12} stroke={3} />Validé</m.span>}</AnimatePresence>
                                </div>
                                <h2 id="exercice-titre" className="m-0 mt-2 text-lg font-semibold">{step.exercise.title}</h2>
                                <p className="m-0 mt-2 text-[15px] leading-[1.6] text-[var(--dr-text-2)]">{step.exercise.description}</p>
                                {step.exercise.hint && (
                                    <details className="group mt-4 rounded-xl bg-[var(--dr-field)] px-4 py-3">
                                        <summary className="cursor-pointer list-none text-sm font-semibold text-[var(--dr-text)] [&::-webkit-details-marker]:hidden">
                                            <span className="inline-flex items-center gap-2"><Svg d={ICON.chevronRight} size={15} className="transition-transform group-open:rotate-90" />Afficher l’indice</span>
                                        </summary>
                                        <p className="m-0 mt-2 text-sm leading-6 text-[var(--dr-text-2)]">{step.exercise.hint}</p>
                                    </details>
                                )}
                                <button type="button" onClick={() => changeExercise(!exerciseCompleted)} aria-pressed={exerciseCompleted} className={(exerciseCompleted ? ui.secondary : ui.primary) + " mt-5"}>
                                    <Svg d={ICON.check} size={16} stroke={2.6} />
                                    {exerciseCompleted ? "Rouvrir l’exercice" : "J’ai réalisé l’exercice"}
                                </button>
                                {exerciseCompleted && step.exercise.solution && (
                                    <details className="group mt-4 rounded-xl bg-[var(--dr-field)] px-4 py-3">
                                        <summary className="cursor-pointer list-none text-sm font-semibold [&::-webkit-details-marker]:hidden"><span className="inline-flex items-center gap-2"><Svg d={ICON.chevronRight} size={15} className="transition-transform group-open:rotate-90" />Voir une solution possible</span></summary>
                                        <pre className="m-0 mt-3 overflow-x-auto whitespace-pre-wrap font-['JetBrains_Mono',ui-monospace,monospace] text-[13px] leading-6 text-[var(--dr-text)]">{step.exercise.solution}</pre>
                                    </details>
                                )}
                            </section>
                        )}

                        {/* Terminer l'étape */}
                        {!blocked && (
                            <section aria-label="Avancement" className={"flex flex-col gap-4 rounded-[18px] border p-5 sm:flex-row sm:items-center " + (completed ? "border-[color-mix(in_srgb,var(--dr-accent)_45%,transparent)] bg-[var(--dr-accent-soft)]" : "border-[var(--dr-border)] bg-[var(--dr-surface)] shadow-[var(--dr-shadow)]")}>
                                <div className="min-w-0 flex-1">
                                    <h2 className="m-0 text-base font-semibold">{completed ? "Étape terminée" : "Tu maîtrises cette étape ?"}</h2>
                                    <p className="m-0 mt-1 text-sm text-[var(--dr-text-2)]">
                                        {completed ? "Bravo ! Passe à la suite ou reviens dessus quand tu veux." : canComplete ? "Marque-la comme terminée pour avancer dans le parcours." : "Valide d’abord l’exercice pratique ci-dessus."}
                                    </p>
                                </div>
                                <button type="button" disabled={statusLoading || !canComplete} onClick={() => changeStatus(completed ? "in_progress" : "completed")} aria-busy={statusLoading} className={(completed ? ui.secondary : ui.primary) + " shrink-0"}>
                                    {statusLoading ? <Loader2 size={16} className="animate-spin" /> : <Svg d={completed ? "M3 12a9 9 0 109-9M3 4v5h5" : ICON.check} size={16} stroke={2.4} />}
                                    {statusLoading ? "Enregistrement…" : completed ? "Remettre en cours" : "Marquer comme terminée"}
                                </button>
                            </section>
                        )}

                        <StepPager previous={previous_step} next={next_step} unlocked={completed} />

                        {/* Notes */}
                        <section className={ui.card + " p-5"} aria-labelledby="notes-titre">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                <div className="min-w-0 flex-1">
                                    <h2 id="notes-titre" className="m-0 text-base font-semibold">Garder une fiche mémo</h2>
                                    <p className="m-0 mt-1 text-sm text-[var(--dr-text-2)]">Le cours et l’exemple sont pré-remplis : ajuste puis enregistre.</p>
                                </div>
                                <button type="button" onClick={() => setShowMemoForm((value) => !value)} aria-expanded={showMemoForm} className={ui.secondary + " shrink-0"}>
                                    <Svg d={showMemoForm ? "M6 6l12 12M18 6L6 18" : ICON.plus} size={16} stroke={2.2} />
                                    {showMemoForm ? "Fermer" : "Créer une fiche"}
                                </button>
                            </div>
                            <AnimatePresence initial={false}>
                                {showMemoForm && (
                                    <m.form onSubmit={createMemo} initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} transition={softSpring} className="overflow-hidden">
                                        <div className="flex flex-col gap-4 pt-5">
                                            <label className="flex flex-col gap-2">
                                                <span className="text-sm font-semibold">Titre</span>
                                                <input data-dr-native value={memoForm.data.title} onChange={(event) => memoForm.setData("title", event.target.value)} className={ui.field} />
                                                {memoForm.errors.title && <span className="text-sm text-[var(--dr-danger)]">{memoForm.errors.title}</span>}
                                            </label>
                                            <label className="flex flex-col gap-2">
                                                <span className="text-sm font-semibold">Contenu</span>
                                                <textarea data-dr-native rows={10} value={memoForm.data.content} onChange={(event) => memoForm.setData("content", event.target.value)} className={ui.field + " h-auto py-3 font-['JetBrains_Mono',ui-monospace,monospace] text-[13px] leading-6"} />
                                                {memoForm.errors.content && <span className="text-sm text-[var(--dr-danger)]">{memoForm.errors.content}</span>}
                                            </label>
                                            <div className="flex flex-wrap gap-2">
                                                <button type="submit" disabled={memoForm.processing} className={ui.primary}>{memoForm.processing ? "Création…" : "Enregistrer la fiche"}</button>
                                                <button type="button" onClick={() => setShowMemoForm(false)} className={ui.ghost}>Annuler</button>
                                            </div>
                                        </div>
                                    </m.form>
                                )}
                            </AnimatePresence>
                        </section>
                    </main>

                    {/* Colonne desktop : sommaire de la navigation */}
                    <aside className="hidden flex-col gap-4 lg:sticky lg:top-24 lg:flex">
                        <section className={ui.card + " flex flex-col gap-3 p-5"}>
                            <h2 className="m-0 text-xs font-bold uppercase tracking-[0.12em] text-[var(--dr-text-3)]">Dans ce parcours</h2>
                            <ProgressBar value={progressFromCount(roadmapCompletedSteps, total)} className="h-1.5" />
                            <span className="text-sm text-[var(--dr-text-2)]">{roadmapCompletedSteps} étapes terminées sur {total}</span>
                            <Link href={`/roadmaps/${roadmap.id}`} className={ui.secondary + " h-10 text-[13px]"}>Voir toutes les étapes</Link>
                        </section>
                    </aside>
                </div>
            </div>
        </AppLayout>
    );
}

function StepPager({ previous, next, unlocked }) {
    if (!previous && !next) return null;
    const base = "flex min-w-0 flex-1 items-center gap-3 rounded-2xl border px-4 py-3.5 transition-colors " + ui.focus;
    return (
        <nav aria-label="Étapes voisines" className="flex flex-col gap-2.5 sm:flex-row">
            {previous && (
                <Link href={`/steps/${previous.id}`} className={base + " border-[var(--dr-border)] bg-[var(--dr-surface)] hover:border-[var(--dr-border-2)]"}>
                    <Svg d="M19 12H5M11 6l-6 6 6 6" size={17} className="shrink-0 text-[var(--dr-text-3)]" />
                    <span className="flex min-w-0 flex-col"><span className="text-xs text-[var(--dr-text-3)]">Précédente</span><span className="truncate text-sm font-semibold">{previous.title}</span></span>
                </Link>
            )}
            {next && (unlocked ? (
                <MotionLink whileTap={{ scale: 0.98 }} href={`/steps/${next.id}`} className={base + " justify-end border-[var(--dr-accent)] bg-[var(--dr-accent-soft)] text-right"}>
                    <span className="flex min-w-0 flex-col items-end"><span className="text-xs font-semibold text-[var(--dr-accent-text)]">Étape suivante</span><span className="max-w-full truncate text-sm font-semibold">{next.title}</span></span>
                    <Svg d={ICON.arrow} size={17} className="shrink-0 text-[var(--dr-accent-text)]" />
                </MotionLink>
            ) : (
                <div className={base + " justify-end border-dashed border-[var(--dr-border-2)] text-right opacity-75"} title="Termine cette étape pour débloquer la suivante">
                    <span className="flex min-w-0 flex-col items-end"><span className="text-xs text-[var(--dr-text-3)]">Suivante · se débloque une fois terminée</span><span className="max-w-full truncate text-sm font-semibold text-[var(--dr-text-2)]">{next.title}</span></span>
                    <Svg d="M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z" size={16} className="shrink-0 text-[var(--dr-text-3)]" />
                </div>
            ))}
        </nav>
    );
}

function CopyButton({ text }) {
    const [copied, setCopied] = useState(false);
    async function copy() {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(true);
            setTimeout(() => setCopied(false), 1600);
        } catch {}
    }
    return (
        <button type="button" onClick={copy} className={"ml-auto inline-flex h-8 items-center gap-1.5 rounded-lg px-2.5 text-xs font-semibold text-[var(--dr-text-2)] hover:bg-[var(--dr-surface)] hover:text-[var(--dr-text)] " + ui.focus} aria-live="polite">
            <Svg d={copied ? ICON.check : ICON.copy} size={14} stroke={copied ? 2.8 : 2} />{copied ? "Copié" : "Copier"}
        </button>
    );
}

function StatusBadge({ status }) {
    const config = {
        completed: ["Terminée", "bg-[var(--dr-accent)] text-[var(--dr-ink)]", ICON.check],
        in_progress: ["En cours", "bg-[var(--dr-surface)] text-[var(--dr-accent-text)] ring-1 ring-inset ring-[var(--dr-accent)]", "M8 5v14l11-7z"],
        blocked: ["Bloquée", "bg-[var(--dr-field)] text-[var(--dr-danger)]", "M7 11V8a5 5 0 0110 0v3M5 11h14v10H5z"],
        todo: ["À faire", "bg-[var(--dr-field)] text-[var(--dr-text-2)]", "M12 3a9 9 0 100 18 9 9 0 000-18z"],
    };
    const [label, className, icon] = config[status] ?? config.todo;
    return (
        <span className={"inline-flex h-7 items-center gap-1.5 rounded-full px-3 text-xs font-bold " + className}>
            <Svg d={icon} size={12} stroke={2.6} />{label}
        </span>
    );
}

function buildMemoContent(step) {
    const parts = [];

    if (step.objective) {
        parts.push(`## Objectif\n\n${step.objective}`);
    }

    if (step.content) {
        parts.push(step.content);
    }

    if (step.code_example) {
        parts.push(`## Exemple de code\n\n\`\`\`\n${step.code_example}\n\`\`\``);
    }

    return parts.join("\n\n");
}

function progressFromCount(completedSteps, totalSteps) {
    if (!totalSteps) {
        return 0;
    }

    return Math.min(
        100,
        Math.max(0, Math.round((completedSteps / totalSteps) * 100)),
    );
}
