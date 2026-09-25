import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import MemoForm from '@/Components/Memos/MemoForm';

export default function Create({ folders = [], defaultFolderId = null }) {
    const form = useForm({
        title: '',
        content: '',
        formatting: { fontFamily: 'Inter', fontSize: 16, textTransform: 'none', textAlign: 'left', fontWeight: 400 },
        folder_id: defaultFolderId,
        attachments: [],
        tags: [],
        is_favorite: false,
        icon: '📝',
        cover_attachment_id: null,
        is_full_width: false,
    });

    function submit() {
        form.post('/memos');
    }

    return (
        <AppLayout>
            <Head title="Nouvelle fiche mémo" />
            <div className="mx-auto max-w-3xl">
                <PageHeader title="Nouvelle fiche mémo" subtitle="Note une commande, une notion ou une astuce pour ne plus l'oublier." backHref="/memos" backLabel="Fiches mémo" />
                <MemoForm form={form} folders={folders} attachments={[]} onSubmit={submit} submitLabel="Créer la fiche" cancelHref="/memos" />
            </div>
        </AppLayout>
    );
}
