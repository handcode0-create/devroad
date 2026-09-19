import { Head, router, useForm } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import ConfirmModal from '@/Components/Ui/ConfirmModal';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import RoadmapForm from '@/Components/Roadmaps/RoadmapForm';
import { buttonClass } from '@/Components/Ui/buttons';

export default function Edit({ roadmap, technologies = [] }) {
    const [confirmDelete, setConfirmDelete] = useState(false);
    const form = useForm({
        title: roadmap.title ?? '',
        technology: roadmap.technology ?? '',
        description: roadmap.description ?? '',
        status: roadmap.status ?? 'draft',
    });

    function submit(event) {
        event.preventDefault();
        form.put(`/roadmaps/${roadmap.id}`);
    }

    function destroy() {
        router.delete(`/roadmaps/${roadmap.id}`, {
            preserveScroll: true,
            onFinish: () => setConfirmDelete(false),
        });
    }

    return (
        <AppLayout>
            <Head title={`Modifier ${roadmap.title}`} />

            <div className="mx-auto max-w-2xl">
                <PageHeader
                    title="Modifier la roadmap"
                    backHref={`/roadmaps/${roadmap.id}`}
                    backLabel={roadmap.title}
                />

                <RoadmapForm
                    form={form}
                    technologies={technologies}
                    onSubmit={submit}
                    submitLabel="Enregistrer"
                    cancelHref={`/roadmaps/${roadmap.id}`}
                />

                <section className="mt-6 rounded-2xl border border-red-500/15 bg-red-500/[0.04] p-5">
                    <h2 className="text-sm font-semibold text-white">Zone sensible</h2>
                    <p className="mt-1 text-xs leading-5 text-slate-500">
                        Supprimer la roadmap supprime aussi toutes ses étapes.
                    </p>

                    <button type="button" onClick={() => setConfirmDelete(true)} className={`${buttonClass('danger')} mt-4`}>
                        <Trash2 size={16} aria-hidden="true" />
                        Supprimer cette roadmap
                    </button>
                </section>
            </div>

            <ConfirmModal
                show={confirmDelete}
                title="Supprimer cette roadmap ?"
                description="La roadmap et toutes ses étapes seront définitivement supprimées."
                confirmLabel="Supprimer"
                onClose={() => setConfirmDelete(false)}
                onConfirm={destroy}
            />
        </AppLayout>
    );
}
