import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

const LANGUAGES = [
    ['laravel', 'Laravel'],
    ['php', 'PHP'],
    ['node', 'Node.js'],
    ['javascript', 'JavaScript'],
    ['html', 'HTML'],
    ['css', 'CSS'],
    ['typescript', 'TypeScript'],
    ['react', 'React'],
    ['nextjs', 'Next.js'],
    ['tailwind', 'Tailwind CSS'],
    ['git', 'Git'],
    ['github', 'GitHub Actions'],
    ['docker', 'Docker'],
    ['mysql', 'MySQL'],
    ['postgresql', 'PostgreSQL'],
];

export default function CourseForm({ form, roadmap, onSubmit, submitLabel = 'Enregistrer', cancelHref, mode = 'create' }) {
    const [showAdvanced, setShowAdvanced] = useState(
        Boolean(form.data.workspace_files?.length || form.data.exercise_title),
    );

    function updateFile(index, key, value) {
        const files = [...(form.data.workspace_files ?? [])];
        files[index] = { ...files[index], [key]: value };
        form.setData('workspace_files', files);
        if (key === 'path' && index === 0) form.setData('workspace_file', value);
    }

    function addFile() {
        form.setData('workspace_files', [
            ...(form.data.workspace_files ?? []),
            { path: '', content: '' },
        ]);
    }

    function removeFile(index) {
        const files = (form.data.workspace_files ?? []).filter((_, i) => i !== index);
        form.setData('workspace_files', files);
        if (index === 0) form.setData('workspace_file', files[0]?.path ?? '');
    }

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                <p className="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#FF8A3D]">
                    {mode === 'create' ? 'Nouveau cours' : 'Édition du cours'}
                </p>
                <h2 className="mt-1 text-lg font-bold text-white">{roadmap?.title}</h2>
                <p className="mt-1 text-xs leading-5 text-slate-500">
                    Un cours doit expliquer une notion, montrer une structure correcte et proposer une mise en pratique.
                </p>

                <div className="mt-6 grid gap-5">
                    <Field label="Titre" error={form.errors.title}>
                        <input value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} className={inputClass} placeholder="Ex. Models et Eloquent" autoFocus />
                    </Field>
                    <Field label="Description courte" error={form.errors.description}>
                        <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} className={textareaClass} rows={3} />
                    </Field>
                    <Field label="Objectif pédagogique" error={form.errors.objective}>
                        <textarea value={form.data.objective} onChange={(e) => form.setData('objective', e.target.value)} className={textareaClass} rows={3} />
                    </Field>
                    <Field label="Contenu du cours" error={form.errors.content}>
                        <textarea value={form.data.content} onChange={(e) => form.setData('content', e.target.value)} className={textareaClass} rows={16} placeholder={'## Introduction\n\nExplique le concept...\n\n## À retenir\n\n- ...'} />
                    </Field>
                    <Field label="Exemple principal" error={form.errors.code_example}>
                        <textarea value={form.data.code_example} onChange={(e) => form.setData('code_example', e.target.value)} className={textareaClass + ' font-mono'} rows={12} />
                    </Field>
                </div>
            </section>

            <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <p className="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#FF8A3D]">Workspace DevLab</p>
                        <h2 className="mt-1 text-base font-bold text-white">Structure de fichiers</h2>
                        <p className="mt-1 text-xs leading-5 text-slate-500">
                            Utilise plusieurs fichiers lorsque le concept doit montrer la séparation des responsabilités.
                        </p>
                    </div>
                    <button type="button" onClick={addFile} className="inline-flex items-center gap-2 rounded-xl bg-[#FF6A00] px-3 py-2 text-xs font-bold text-[#08111F]">
                        <Plus size={14} /> Fichier
                    </button>
                </div>

                <div className="mt-5 space-y-4">
                    {(form.data.workspace_files ?? []).length === 0 ? (
                        <div className="rounded-2xl border border-dashed border-white/[0.08] p-5 text-xs text-slate-600">
                            Aucun fichier initial. Le workspace utilisera l'exemple principal.
                        </div>
                    ) : (
                        (form.data.workspace_files ?? []).map((file, index) => (
                            <div key={index} className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
                                <div className="flex items-center gap-2">
                                    <input value={file.path} onChange={(e) => updateFile(index, 'path', e.target.value)} className={inputClass} placeholder="app/Models/Roadmap.php" />
                                    <button type="button" onClick={() => removeFile(index)} className="shrink-0 rounded-xl p-2 text-slate-600 hover:bg-red-500/10 hover:text-red-400" aria-label="Supprimer le fichier">
                                        <Trash2 size={16} />
                                    </button>
                                </div>
                                <textarea value={file.content ?? ''} onChange={(e) => updateFile(index, 'content', e.target.value)} className={textareaClass + ' mt-3 font-mono'} rows={10} placeholder="Contenu du fichier..." />
                                {form.errors['workspace_files.' + index + '.path'] && (
                                    <p className="mt-2 text-xs text-red-400">{form.errors['workspace_files.' + index + '.path']}</p>
                                )}
                            </div>
                        ))
                    )}
                </div>
            </section>

            <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                <button type="button" onClick={() => setShowAdvanced((value) => !value)} className="text-sm font-bold text-white">
                    {showAdvanced ? 'Masquer les options pédagogiques' : 'Afficher les options pédagogiques'}
                </button>

                {showAdvanced && (
                    <div className="mt-5 grid gap-5">
                        <div className="grid gap-5 sm:grid-cols-2">
                            <Field label="Langage / runtime" error={form.errors.workspace_language}>
                                <select value={form.data.workspace_language} onChange={(e) => form.setData('workspace_language', e.target.value)} className={inputClass}>
                                    <option value="">Automatique</option>
                                    {LANGUAGES.map(([value, label]) => <option key={value} value={value}>{label}</option>)}
                                </select>
                            </Field>
                            <Field label="Fichier principal" error={form.errors.workspace_file}>
                                <input value={form.data.workspace_file} onChange={(e) => form.setData('workspace_file', e.target.value)} className={inputClass} placeholder="app/Models/Roadmap.php" />
                            </Field>
                        </div>

                        <div className="grid gap-5 sm:grid-cols-2">
                            <Field label="Durée (minutes)" error={form.errors.estimated_minutes}>
                                <input type="number" min="1" max="1440" value={form.data.estimated_minutes} onChange={(e) => form.setData('estimated_minutes', e.target.value)} className={inputClass} />
                            </Field>
                            <Field label="Position" error={form.errors.position}>
                                <input type="number" min="1" value={form.data.position} onChange={(e) => form.setData('position', e.target.value)} className={inputClass} />
                            </Field>
                        </div>

                        <Field label="Titre de l'exercice" error={form.errors.exercise_title}>
                            <input value={form.data.exercise_title} onChange={(e) => form.setData('exercise_title', e.target.value)} className={inputClass} />
                        </Field>
                        <Field label="Consigne" error={form.errors.exercise_description}>
                            <textarea value={form.data.exercise_description} onChange={(e) => form.setData('exercise_description', e.target.value)} className={textareaClass} rows={4} />
                        </Field>
                        <Field label="Indice" error={form.errors.exercise_hint}>
                            <textarea value={form.data.exercise_hint} onChange={(e) => form.setData('exercise_hint', e.target.value)} className={textareaClass} rows={3} />
                        </Field>
                        <Field label="Solution" error={form.errors.exercise_solution}>
                            <textarea value={form.data.exercise_solution} onChange={(e) => form.setData('exercise_solution', e.target.value)} className={textareaClass + ' font-mono'} rows={8} />
                        </Field>

                        {mode === 'edit' && (
                            <Field label="Statut" error={form.errors.status}>
                                <select value={form.data.status} onChange={(e) => form.setData('status', e.target.value)} className={inputClass}>
                                    <option value="todo">À faire</option>
                                    <option value="in_progress">En cours</option>
                                    <option value="completed">Terminée</option>
                                    <option value="blocked">Bloquée</option>
                                </select>
                            </Field>
                        )}
                    </div>
                )}
            </section>

            <div className="flex flex-wrap justify-end gap-3">
                {cancelHref && <a href={cancelHref} className="rounded-xl border border-white/[0.06] bg-white/[0.03] px-4 py-2.5 text-sm font-semibold text-slate-400 hover:bg-white/[0.06] hover:text-white">Annuler</a>}
                <button type="submit" disabled={form.processing} className="rounded-xl bg-[#FF6A00] px-5 py-2.5 text-sm font-bold text-[#08111F] disabled:opacity-50">
                    {form.processing ? 'Enregistrement...' : submitLabel}
                </button>
            </div>
        </form>
    );
}

function Field({ label, error, children }) {
    return (
        <div>
            <label className="mb-2 block text-xs font-semibold text-slate-300">{label}</label>
            {children}
            {error && <p className="mt-1.5 text-xs text-red-400">{error}</p>}
        </div>
    );
}

const inputClass = 'h-11 w-full rounded-xl border border-white/[0.08] bg-[#07101A] px-3 text-sm text-white outline-none placeholder:text-slate-700 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/10';
const textareaClass = 'w-full rounded-xl border border-white/[0.08] bg-[#07101A] px-3 py-3 text-sm leading-6 text-white outline-none placeholder:text-slate-700 focus:border-[#FF6A00]/40 focus:ring-2 focus:ring-[#FF6A00]/10';
