import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import MemoForm from '@/Components/Memos/MemoForm';
import { normalizeMemoContent } from '@/Components/Memos/MemoEditor';

export default function Edit({ memo, folders = [] }) {
    const form = useForm({
        title: memo.title ?? '',
        content: normalizeMemoContent(memo.content ?? ''),
        formatting: memo.formatting ?? { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 },
        folder_id: memo.folder?.id ?? null,
        attachments: [],
        tags: (memo.tags ?? []).map((tag) => tag.name),
        is_favorite: Boolean(memo.is_favorite),
    });

    function submit(event) {
        event.preventDefault();
        form.transform((data) => ({ ...data, _method: 'put' })).post('/memos/' + memo.id, { forceFormData: true });
    }

    return (
        <AppLayout>
            <Head title={'Modifier ' + memo.title} />
            <div className="mx-auto max-w-3xl">
                <PageHeader title="Modifier la fiche" backHref={'/memos/' + memo.id} backLabel={memo.title} />
                <MemoForm form={form} folders={folders} onSubmit={submit} submitLabel="Enregistrer" cancelHref={'/memos/' + memo.id} />
            </div>
        </AppLayout>
    );
}
