import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import MemoForm from '@/Components/Memos/MemoForm';
import { autoMemoTitle, normalizeMemoContent } from '@/Components/Memos/MemoEditor';

export default function Edit({ memo, folders = [] }) {
    const [saved, setSaved] = useState(false);

    const form = useForm({
        title: memo.title ?? '',
        content: normalizeMemoContent(memo.content ?? ''),
        formatting: memo.formatting ?? { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 },
        folder_id: memo.folder?.id ?? null,
        attachments: [],
        tags: (memo.tags ?? []).map((tag) => tag.name),
        is_favorite: Boolean(memo.is_favorite),
        icon: memo.icon ?? '📝',
        cover_attachment_id: memo.cover_attachment_id ?? null,
        is_full_width: Boolean(memo.is_full_width),
    });

    function submit(event) {
        event.preventDefault();
        setSaved(false);

        form.transform((data) => {
            const content = normalizeMemoContent(data.content);
            const title = data.title.trim() || autoMemoTitle(content) || '';

            return {
                ...data,
                title,
                content,
                _method: 'put',
            };
        }).post('/memos/' + memo.id, {
            forceFormData: true,
            onSuccess: () => setSaved(true),
        });
    }

    return (
        <AppLayout>
            <Head title={'Modifier ' + memo.title} />
            <div className="mx-auto max-w-3xl">
                <PageHeader title="Modifier la fiche" backHref={'/memos/' + memo.id} backLabel={memo.title} />
                <MemoForm
                    form={form}
                    folders={folders}
                    attachments={memo.attachments ?? []}
                    onSubmit={submit}
                    saved={saved}
                    submitLabel="Enregistrer"
                    cancelHref={'/memos/' + memo.id}
                />
            </div>
        </AppLayout>
    );
}
