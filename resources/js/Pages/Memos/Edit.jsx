import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import MemoForm from '@/Components/Memos/MemoForm';

export default function Edit({ memo }) {
    const form = useForm({
        title: memo.title ?? '',
        content: memo.content ?? '',
        // Le backend attend des noms ; envoyer [] retire tous les tags.
        tags: (memo.tags ?? []).map((tag) => tag.name),
        is_favorite: Boolean(memo.is_favorite),
    });

    function submit(event) {
        event.preventDefault();
        form.put(`/memos/${memo.id}`);
    }

    return (
        <AppLayout>
            <Head title={`Modifier ${memo.title}`} />

            <div className="mx-auto max-w-2xl">
                <PageHeader
                    title="Modifier la fiche"
                    backHref={`/memos/${memo.id}`}
                    backLabel={memo.title}
                />

                <MemoForm
                    form={form}
                    onSubmit={submit}
                    submitLabel="Enregistrer"
                    cancelHref={`/memos/${memo.id}`}
                />
            </div>
        </AppLayout>
    );
}
