import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import CourseForm from '@/Components/Roadmaps/CourseForm';

export default function Create({ roadmap, next_position = 1 }) {
    const form = useForm({
        title: '',
        description: '',
        objective: '',
        content: '',
        code_example: '',
        workspace_file: '',
        workspace_language: ['laravel', 'php', 'node', 'javascript', 'typescript', 'react', 'nextjs', 'html', 'css', 'tailwind', 'git', 'github', 'docker', 'mysql', 'postgresql'].includes(roadmap?.technology)
            ? roadmap.technology
            : '',
        workspace_files: [],
        exercise_title: '',
        exercise_description: '',
        exercise_hint: '',
        exercise_solution: '',
        estimated_minutes: 30,
        position: next_position,
        status: 'todo',
    });

    function submit(event) {
        event.preventDefault();
        form.post('/roadmaps/' + roadmap.id + '/steps');
    }

    return (
        <AppLayout>
            <Head title={'Nouveau cours · ' + roadmap.title} />
            <div className="mx-auto max-w-4xl">
                <PageHeader title="Nouveau cours" subtitle="Crée un cours structuré : explication, exemple, fichiers DevLab et exercice." backHref={'/roadmaps/' + roadmap.id} backLabel={roadmap.title} />
                <CourseForm form={form} roadmap={roadmap} onSubmit={submit} submitLabel="Créer le cours" cancelHref={'/roadmaps/' + roadmap.id} mode="create" />
            </div>
        </AppLayout>
    );
}
