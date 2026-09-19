import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import RoadmapForm from '@/Components/Roadmaps/RoadmapForm';

export default function Create() {
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
                    title="Nouvelle roadmap"
                    subtitle="Donne un nom à ton parcours. Tu ajouteras les étapes ensuite."
                    backHref="/roadmaps"
                    backLabel="Mes roadmaps"
                />

                <RoadmapForm
                    form={form}
                    onSubmit={submit}
                    submitLabel="Créer la roadmap"
                    cancelHref="/roadmaps"
                />
            </div>
        </AppLayout>
    );
}
