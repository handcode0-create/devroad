import { Head, router, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import ConfirmModal from '@/Components/Ui/ConfirmModal';
import CourseForm from '@/Components/Roadmaps/CourseForm';
import { buttonClass } from '@/Components/Ui/buttons';

export default function Edit({ roadmap, step }) {
    const [confirmDelete, setConfirmDelete] = useState(false);

    const form = useForm({
        title: step.title ?? '',
        description: step.description ?? '',
        objective: step.objective ?? '',
        content: step.content ?? '',
        code_example: step.code_example ?? '',
        workspace_file: step.workspace_file ?? '',
        workspace_language: step.workspace_language ?? roadmap?.technology ?? '',
        workspace_files: Array.isArray(step.workspace_files) ? step.workspace_files : [],
        exercise_title: step.exercise_title ?? '',
        exercise_description: step.exercise_description ?? '',
        exercise_hint: step.exercise_hint ?? '',
        exercise_solution: step.exercise_solution ?? '',
        estimated_minutes: step.estimated_minutes ?? 30,
        position: step.position ?? 1,
        status: step.status ?? 'todo',
    });

    function submit(event) {
        event.preventDefault();
        form.put('/steps/' + step.id);
    }

    function destroy() {
        router.delete('/steps/' + step.id, {
            preserveScroll: true,
            onFinish: () => setConfirmDelete(false),
        });
    }

    return (
        <AppLayout>
            <Head title={'Modifier ' + step.title} />
            <div className="mx-auto max-w-4xl">
                <PageHeader title="Modifier le cours" subtitle={'Cours ' + step.position + ' · ' + roadmap.title} backHref={'/roadmaps/' + roadmap.id} backLabel={roadmap.title} />
                <CourseForm form={form} roadmap={roadmap} onSubmit={submit} submitLabel="Enregistrer les modifications" cancelHref={'/steps/' + step.id} mode="edit" />
                <section className="mt-6 rounded-2xl border border-red-500/15 bg-red-500/[0.04] p-5">
                    <h2 className="text-sm font-semibold text-white">Zone sensible</h2>
                    <p className="mt-1 text-xs leading-5 text-slate-500">Supprimer ce cours le retire définitivement de ce parcours.</p>
                    <button type="button" onClick={() => setConfirmDelete(true)} className={buttonClass('danger')}>
                        <Trash2 size={16} />
                        Supprimer ce cours
                    </button>
                </section>
            </div>
            <ConfirmModal
                show={confirmDelete}
                title="Supprimer ce cours ?"
                description="Le cours et son contenu pédagogique seront définitivement supprimés."
                confirmLabel="Supprimer"
                onClose={() => setConfirmDelete(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
