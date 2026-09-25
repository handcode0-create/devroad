import { Head, useForm } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import MemoForm from '@/Components/Memos/MemoForm';
import { autoMemoTitle, normalizeMemoContent } from '@/Components/Memos/MemoEditor';

export default function Edit({ memo, folders = [] }) {
    const [saved, setSaved] = useState(false);
    const [existingAttachments, setExistingAttachments] = useState(memo.attachments ?? []);
    const [attachmentProcessingId, setAttachmentProcessingId] = useState(null);

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

    function deleteAttachment(attachment) {
        if (attachmentProcessingId || !window.confirm('Supprimer « ' + attachment.name + ' » ?')) return;
        setAttachmentProcessingId(attachment.id);
        router.delete('/memos/attachments/' + attachment.id, {
            preserveScroll: true,
            onSuccess: () => {
                setExistingAttachments((current) => current.filter((item) => item.id !== attachment.id));
                if (Number(form.data.cover_attachment_id) === Number(attachment.id)) form.setData('cover_attachment_id', null);
            },
            onFinish: () => setAttachmentProcessingId(null),
        });
    }

    function submit(normalized = null) {
        setSaved(false);

        form.transform((data) => {
            const content = normalized?.content ?? normalizeMemoContent(data.content);
            const title = normalized?.title ?? (data.title.trim() || autoMemoTitle(content) || '');

            return {
                ...data,
                title,
                content,
                _method: 'put',
            };
        }).post('/memos/' + memo.id, {
            forceFormData: true,
            onSuccess: (page) => {
                const nextMemo = page.props.memo ?? memo;
                setExistingAttachments(nextMemo.attachments ?? []);
                const nextData = {
                    title: nextMemo.title ?? '',
                    content: normalizeMemoContent(nextMemo.content ?? ''),
                    formatting: nextMemo.formatting ?? { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 },
                    folder_id: nextMemo.folder?.id ?? null,
                    attachments: [],
                    tags: (nextMemo.tags ?? []).map((tag) => tag.name),
                    is_favorite: Boolean(nextMemo.is_favorite),
                    icon: nextMemo.icon ?? '📝',
                    cover_attachment_id: nextMemo.cover_attachment_id ?? null,
                    is_full_width: Boolean(nextMemo.is_full_width),
                };

                form.setData(nextData);
                form.defaults(nextData);
                setSaved(true);
            },
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
                    attachments={existingAttachments}
                    onDeleteAttachment={deleteAttachment}
                    attachmentProcessingId={attachmentProcessingId}
                    onSubmit={submit}
                    saved={saved}
                    submitLabel="Enregistrer"
                    cancelHref={'/memos/' + memo.id}
                />
            </div>
        </AppLayout>
    );
}
