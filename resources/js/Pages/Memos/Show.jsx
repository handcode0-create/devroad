import { Head, Link, router } from '@inertiajs/react';
import { Download, File, Folder, Image as ImageIcon, Pencil, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/Ui/PageHeader';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import MemoContent from '@/Components/Memos/MemoContent';
import TagBadge from '@/Components/Memos/TagBadge';
import { buttonClass } from '@/Components/Ui/buttons';
import ConfirmModal from '@/Components/Ui/ConfirmModal';

export default function Show({ memo }) {
    const [confirmDelete, setConfirmDelete] = useState(false);

    function destroy() {
        router.delete('/memos/' + memo.id, { preserveScroll: true, onFinish: () => setConfirmDelete(false) });
    }

    return (
        <AppLayout>
            <Head title={memo.title} />
            <div className="mx-auto max-w-3xl">
                <PageHeader title={memo.title} backHref="/memos" backLabel="Fiches mémo" actions={<FavoriteButton memo={memo} />} />
                {memo.tags?.length > 0 && <ul className="-mt-2 mb-5 flex flex-wrap gap-2" aria-label="Tags">{memo.tags.map((tag) => <li key={tag.id}><Link href={'/memos?tag=' + tag.slug}><TagBadge name={tag.name} /></Link></li>)}</ul>}
                {memo.folder && <div className="mb-3 flex items-center gap-2 text-xs text-slate-500"><Folder size={14} className="text-[#FF8A3D]" />{memo.folder.name}</div>}
                <article className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-6">
                    <MemoContent content={memo.content} formatting={memo.formatting} />
                    {memo.attachments?.length > 0 && <section className="mt-8 border-t border-white/[0.06] pt-5">
                        <h2 className="mb-3 text-xs font-bold uppercase tracking-[0.14em] text-slate-500">Fichiers et médias</h2>
                        <div className="grid gap-3 sm:grid-cols-2">
                            {memo.attachments.map((file) => <div key={file.id} className="overflow-hidden rounded-xl border border-white/[0.06] bg-[#08111F]">
                                {file.is_image
                                    ? <a href={file.url} target="_blank" rel="noreferrer" className="block"><img src={file.url} alt={file.name} className="max-h-72 w-full object-contain bg-black/10" /></a>
                                    : <div className="flex items-center gap-3 p-3"><File size={22} className="text-slate-500" /><div className="min-w-0 flex-1"><p className="truncate text-sm text-slate-300">{file.name}</p><p className="text-[10px] text-slate-600">{Math.max(1, Math.round(file.size / 1024))} Ko</p></div></div>}
                                <div className="flex items-center justify-between border-t border-white/[0.06] px-3 py-2">
                                    <span className="truncate text-[10px] text-slate-600">{file.mime_type}</span>
                                    <a href={file.download_url} className="inline-flex items-center gap-1.5 text-xs font-semibold text-[#FF8A3D] hover:text-[#FFB078]"><Download size={13} />Télécharger</a>
                                </div>
                            </div>)}
                        </div>
                    </section>}
                </article>
                <div className="mt-6 flex flex-wrap items-center gap-3">
                    <Link href={'/memos/' + memo.id + '/edit'} className={buttonClass('primary')}><Pencil size={16} />Modifier</Link>
                    <button type="button" onClick={() => setConfirmDelete(true)} className={buttonClass('danger')}><Trash2 size={16} />Supprimer</button>
                </div>
            </div>
            <ConfirmModal show={confirmDelete} title="Supprimer cette fiche mémo ?" description="Cette action est définitive et la fiche sera supprimée." confirmLabel="Supprimer" onClose={() => setConfirmDelete(false)} onConfirm={destroy} />
        </AppLayout>
    );
}
