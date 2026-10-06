import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import FavoriteButton from '@/Components/Memos/FavoriteButton';
import MemoContent from '@/Components/Memos/MemoContent';
import { ICON, MotionLink, Svg, monogram, ui } from '@/Components/Ui/Design';

function formatDate(value) {
    if (!value) return '';
    const date = new Date(value);
    const today = new Date();
    const day = (d) => new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime();
    const days = Math.round((day(today) - day(date)) / 86400000);
    if (days === 0) return 'aujourd’hui à ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    if (days === 1) return 'hier';
    return 'le ' + date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', ...(date.getFullYear() !== today.getFullYear() ? { year: 'numeric' } : {}) });
}

// Lecture d'une fiche : en-tête (icône, titre, dossier, tags), barre d'actions,
// contenu dans une colonne de lecture confortable, puis fichiers joints.
export default function Show({ memo }) {
    const [busy, setBusy] = useState(null);
    const breadcrumbs = memo.breadcrumbs ?? [];

    function duplicate() {
        setBusy('duplicate');
        router.post('/memos/' + memo.id + '/duplicate', {}, { onFinish: () => setBusy(null) });
    }

    // Corbeille immédiate : la notification sur la liste propose « Annuler ».
    function trash() {
        setBusy('trash');
        router.delete('/memos/' + memo.id, { onFinish: () => setBusy(null) });
    }

    return (
        <AppLayout>
            <Head title={memo.title} />
            <div className={'mx-auto flex w-full flex-col gap-6 font-[\'Figtree\',system-ui,sans-serif] text-[var(--dr-text)] ' + (memo.is_full_width ? 'max-w-[1200px]' : 'max-w-3xl')}>
                <nav aria-label="Fil d'Ariane" className="flex min-w-0 flex-wrap items-center gap-1 text-sm text-[var(--dr-text-2)]">
                    <Link href="/memos" className={'-ml-1 inline-flex min-h-9 items-center gap-1.5 rounded-lg px-1 font-medium hover:text-[var(--dr-text)] ' + ui.focus}>
                        <Svg d="M19 12H5M11 6l-6 6 6 6" size={16} />Fiches mémo
                    </Link>
                    {breadcrumbs.map((item) => (
                        <span key={item.id} className="flex min-w-0 items-center gap-1">
                            <span aria-hidden="true" className="text-[var(--dr-text-3)]">/</span>
                            <Link href={'/memos?folder=' + item.id} className="truncate rounded px-0.5 hover:text-[var(--dr-accent-text)]">{item.name}</Link>
                        </span>
                    ))}
                </nav>

                {memo.cover && <img src={memo.cover.url} alt="" className="max-h-72 w-full rounded-[18px] object-cover" />}

                <header className="flex flex-col gap-4">
                    <div className="flex items-start gap-4">
                        <span aria-hidden="true" className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-[var(--dr-field)] text-[26px] leading-none text-[var(--dr-accent-text)]">
                            {memo.icon || <span className="font-['JetBrains_Mono',ui-monospace,monospace] text-xl">{monogram(memo.title)}</span>}
                        </span>
                        <div className="flex min-w-0 flex-1 flex-col gap-1.5">
                            <h1 className="m-0 font-['Manrope',sans-serif] text-[28px] font-extrabold leading-[1.12] tracking-[-0.03em] [overflow-wrap:anywhere] lg:text-[38px]">{memo.title}</h1>
                            <span className="text-sm text-[var(--dr-text-3)]">Modifiée {formatDate(memo.updated_at)}</span>
                        </div>
                    </div>

                    {memo.tags?.length > 0 && (
                        <ul className="m-0 flex list-none flex-wrap gap-2 p-0" aria-label="Collections">
                            {memo.tags.map((tag) => (
                                <li key={tag.id}>
                                    <Link href={'/memos?tag=' + tag.slug} className={'inline-flex h-8 items-center rounded-full bg-[var(--dr-field)] px-3 text-[13px] text-[var(--dr-text-2)] hover:bg-[var(--dr-accent-soft)] hover:text-[var(--dr-accent-text)] ' + ui.focus}>#{tag.name}</Link>
                                </li>
                            ))}
                        </ul>
                    )}

                    <div className="flex flex-wrap items-center gap-2" role="toolbar" aria-label="Actions de la fiche">
                        <MotionLink whileTap={{ scale: 0.97 }} href={'/memos/' + memo.id + '/edit'} className={ui.primary}><Svg d={ICON.edit} size={16} stroke={2.2} />Modifier</MotionLink>
                        <span className="flex h-11 items-center rounded-xl border border-[var(--dr-border)] bg-[var(--dr-surface)] px-1"><FavoriteButton memo={memo} variant="design" className="!m-0 !h-9 !w-9 !rounded-[10px]" /></span>
                        <button type="button" onClick={duplicate} disabled={busy !== null} aria-label="Dupliquer la fiche" title="Dupliquer" className={ui.secondary + ' w-11 px-0 sm:w-auto sm:px-4'}><Svg d={ICON.copy} size={16} /><span className="hidden sm:inline">{busy === 'duplicate' ? 'Duplication…' : 'Dupliquer'}</span></button>
                        <button type="button" onClick={trash} disabled={busy !== null} aria-label="Mettre à la corbeille" title="Mettre à la corbeille" className={ui.danger + ' ml-auto w-11 px-0 sm:w-auto sm:px-3'}><Svg d={ICON.trash} size={16} /><span className="hidden sm:inline">{busy === 'trash' ? 'Suppression…' : 'Corbeille'}</span></button>
                    </div>
                </header>

                <article className={ui.card + ' min-w-0 overflow-hidden p-5 sm:p-8'}>
                    {String(memo.content ?? '').trim()
                        ? <MemoContent content={memo.content} formatting={memo.formatting} />
                        : <p className="m-0 text-[15px] text-[var(--dr-text-2)]">Cette fiche est vide. <Link href={'/memos/' + memo.id + '/edit'} className="font-semibold text-[var(--dr-accent-text)]">Commencer à écrire</Link></p>}
                </article>

                {memo.attachments?.length > 0 && (
                    <section aria-labelledby="fichiers-titre" className="flex flex-col gap-3">
                        <h2 id="fichiers-titre" className={ui.sectionTitle + ' m-0'}>Fichiers et médias</h2>
                        <ul className="m-0 grid list-none gap-3 p-0 sm:grid-cols-2">
                            {memo.attachments.map((file) => (
                                <li key={file.id} className={ui.card + ' overflow-hidden'}>
                                    {file.is_image
                                        ? <a href={file.url} target="_blank" rel="noreferrer" className="block bg-[var(--dr-field)]"><img src={file.url} alt={file.name} className="max-h-64 w-full object-contain" /></a>
                                        : <div className="flex items-center gap-3 p-4"><span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--dr-field)] text-[var(--dr-text-2)]"><Svg d="M7 3h7l5 5v13H7zM14 3v5h5" size={18} /></span><span className="flex min-w-0 flex-col"><span className="truncate text-sm font-semibold">{file.name}</span><span className="text-xs text-[var(--dr-text-3)]">{Math.max(1, Math.round(file.size / 1024))} Ko</span></span></div>}
                                    <div className="flex items-center justify-between gap-3 border-t border-[var(--dr-border)] px-4 py-2.5">
                                        <span className="truncate text-xs text-[var(--dr-text-3)]">{file.is_image ? file.name : file.mime_type}</span>
                                        <a href={file.download_url} className="inline-flex shrink-0 items-center gap-1.5 text-[13px] font-semibold text-[var(--dr-accent-text)]"><Svg d="M12 4v11M7 10l5 5 5-5M5 20h14" size={14} />Télécharger</a>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </AppLayout>
    );
}
