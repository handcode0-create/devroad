import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import RoadmapForm from '@/Components/Roadmaps/RoadmapForm';

export default function Create({ technologies = [] }) {
    const form = useForm({
        title: '',
        technology: '',
        description: '',
        status: 'active',
    });

    function submit(event) {
        event.preventDefault();
        form.post('/roadmaps');
    }

    return (
        <AppLayout>
            <Head title="Nouvelle roadmap" />

            <div className="mx-auto max-w-2xl">
                <PageHeader
                    title="Nouveau parcours"
                    subtitle="Choisis une technologie : DevRoad générera automatiquement les cours de ton parcours."
                    backHref="/roadmaps"
                    backLabel="Mes roadmaps"
                />

                <RoadmapForm
                    form={form}
                    technologies={technologies}
                    onSubmit={submit}
                    submitLabel="Créer le parcours"
                    cancelHref="/roadmaps"
                />
            </div>
        </AppLayout>
    );
}
