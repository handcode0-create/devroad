import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import MemoForm from '@/Components/Memos/MemoForm';

export default function Edit({ memo }) {
    const form = useForm({
        title: memo.title ?? '',
        content: memo.content ?? '',
        formatting: memo.formatting ?? { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 },
        tags: (memo.tags ?? []).map((tag) => tag.name),
        is_favorite: Boolean(memo.is_favorite),
    });

    function submit(event) {
        event.preventDefault();
        form.put('/memos/' + memo.id);
    }

    return (
        <AppLayout>
            <Head title={'Modifier ' + memo.title} />
            <div className="mx-auto max-w-3xl">
                <PageHeader title="Modifier la fiche" backHref={'/memos/' + memo.id} backLabel={memo.title} />
                <MemoForm form={form} onSubmit={submit} submitLabel="Enregistrer" cancelHref={'/memos/' + memo.id} />
            </div>
        </AppLayout>
    );
}
