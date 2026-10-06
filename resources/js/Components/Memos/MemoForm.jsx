import { Link } from '@inertiajs/react';
import { Download, File, Folder, ImagePlus, Trash2, X } from 'lucide-react';
import { Field, inputClass } from '@/Components/Ui/Field';
import { buttonClass } from '@/Components/Ui/buttons';
import TagInput from '@/Components/Memos/TagInput';
import MemoEditor from '@/Components/Memos/MemoEditor';

export default function MemoForm({ form, onSubmit, saved = false, submitLabel, cancelHref, folders = [], attachments = [], onDeleteAttachment = null, attachmentProcessingId = null }) {
    const { data, setData, errors, processing, isDirty } = form;
    const tagsError = errors.tags ?? Object.entries(errors).find(([key]) => key.startsWith('tags.'))?.[1];
    const selectedFiles = Array.isArray(data.attachments) ? data.attachments : [];
    // Erreurs des champs qui n'ont pas d'emplacement dédié (mise en forme,
    // icône, couverture, fichiers…) : sans ce résumé, un refus serait silencieux.
    const otherErrors = Object.entries(errors)
        .filter(([key]) => !['title', 'content'].includes(key) && !key.startsWith('tags'))
        .map(([, message]) => message);

    function addFiles(event) {
        const incoming = Array.from(event.target.files ?? []);
        const merged = [...selectedFiles, ...incoming].filter((file, index, files) =>
            files.findIndex((candidate) => candidate.name === file.name && candidate.size === file.size && candidate.lastModified === file.lastModified) === index,
        );
        const available = Math.max(0, 8 - attachments.length);
        const valid = merged.filter((file) => file.size <= 5 * 1024 * 1024).slice(0, available);
        setData('attachments', valid);
        event.target.value = '';
    }

    function removeFile(index) {
        setData('attachments', selectedFiles.filter((_, fileIndex) => fileIndex !== index));
    }

    function handleSubmit(event) {
        event.preventDefault();
        if (!processing) onSubmit();
    }

    function handleButtonClick(event) {
        event.preventDefault();
        if (!processing) onSubmit();
    }

    return (
        <form onSubmit={handleSubmit} noValidate className="space-y-5 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-6">
            <Field label="Titre" htmlFor="title" error={errors.title} hint="Le titre est obligatoire (3 à 255 caractères).">
                <input id="title" type="text" value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="Titre de la fiche" maxLength={255} autoFocus aria-invalid={errors.title ? 'true' : undefined} className={inputClass} />
            </Field>

            <Field label="Contenu" htmlFor="content" error={errors.content} hint="Les blocs de code entre triples accents restent affichés en monospace.">
                <MemoEditor content={data.content} onContentChange={(content) => setData('content', content)} formatting={data.formatting} onFormattingChange={(formatting) => setData('formatting', formatting)} onAutoTitle={(title) => { if (title) setData('title', title); }} attachments={attachments} />
            </Field>

            <Field label="Page" htmlFor="memo-icon" hint="Icône, couverture et largeur de page comme dans Notion.">
                <div className="grid gap-3 sm:grid-cols-[88px_minmax(0,1fr)]">
                    <input id="memo-icon" type="text" value={data.icon ?? '📝'} onChange={(e) => setData('icon', e.target.value.slice(0, 4))} className={inputClass + ' text-center text-xl'} aria-label="Icône de la fiche" />
                    <select id="cover_attachment_id" value={data.cover_attachment_id ?? ''} onChange={(e) => setData('cover_attachment_id', e.target.value || null)} className={inputClass}>
                        <option value="">Sans couverture</option>
                        {attachments.filter((file) => file.is_image).map((file) => <option key={file.id} value={file.id}>{file.name}</option>)}
                    </select>
                </div>
                <label className="mt-3 flex items-center gap-2 text-xs text-[var(--dr-text-2)]">
                    <input type="checkbox" checked={Boolean(data.is_full_width)} onChange={(e) => setData('is_full_width', e.target.checked)} className="h-4 w-4 rounded border-[var(--dr-border-2)] bg-[var(--dr-field)] text-[var(--dr-accent-text)] focus:ring-[#FF6A00]/30" />
                    Page pleine largeur
                </label>
            </Field>

            <Field label="Dossier" htmlFor="folder_id" hint="Organise tes fiches dans une arborescence.">
                <div className="flex items-center gap-2">
                    <Folder size={15} className="shrink-0 text-[var(--dr-accent-text)]" />
                    <select id="folder_id" value={data.folder_id ?? ''} onChange={(e) => setData('folder_id', e.target.value || null)} className={inputClass}>
                        <option value="">Sans dossier</option>
                        {folders.map((folder) => <option key={folder.id} value={folder.id}>{folder.parent_id ? '↳ ' : ''}{folder.name}</option>)}
                    </select>
                </div>
            </Field>

            <Field label="Fichiers et images" htmlFor="attachments" hint={`Images, PDF, Markdown, JSON, CSV ou ZIP · 5 Mo maximum par fichier · ${attachments.length}/8 fichiers déjà enregistrés.`}>
                {attachments.length > 0 && (
                    <div className="mb-3 space-y-2">
                        {attachments.map((file) => (
                            <div key={file.id} className="flex items-center gap-3 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] px-3 py-2.5">
                                {file.is_image ? <img src={file.url} alt="" className="h-10 w-10 shrink-0 rounded-lg object-cover" /> : <File size={18} className="shrink-0 text-[var(--dr-text-3)]" />}
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-xs font-medium text-[var(--dr-text-2)]">{file.name}</p>
                                    <p className="text-[10px] text-[var(--dr-text-3)]">{Math.max(1, Math.round(file.size / 1024))} Ko · {file.mime_type}</p>
                                </div>
                                <a href={file.download_url} className="rounded-lg p-1.5 text-[var(--dr-text-3)] hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]" title="Télécharger" aria-label={"Télécharger " + file.name}>
                                    <Download size={14} />
                                </a>
                                {onDeleteAttachment && <button type="button" disabled={attachmentProcessingId === file.id} onClick={() => onDeleteAttachment(file)} className="rounded-lg p-1.5 text-[var(--dr-text-3)] hover:bg-red-400/[0.08] hover:text-[var(--dr-danger)] disabled:opacity-40" title="Supprimer" aria-label={"Supprimer " + file.name}>
                                    <Trash2 size={14} />
                                </button>}
                            </div>
                        ))}
                    </div>
                )}
                <div className="rounded-xl border border-[var(--dr-border)] bg-[var(--dr-bg)] p-3">
                    <label htmlFor="attachments" data-disabled={attachments.length + selectedFiles.length >= 8 ? 'true' : undefined} className="flex cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-[var(--dr-border)] px-4 py-4 text-xs font-semibold text-[var(--dr-text-2)] transition hover:border-[#FF6A00]/30 hover:text-[var(--dr-text)]">
                        <ImagePlus size={16} className="text-[var(--dr-accent-text)]" />
                        {attachments.length + selectedFiles.length >= 8 ? 'Limite de 8 fichiers atteinte' : 'Ajouter des fichiers ou images'}
                    </label>
                    <input id="attachments" type="file" multiple disabled={attachments.length + selectedFiles.length >= 8} accept="image/*,.pdf,.md,.txt,.json,.csv,.zip" onChange={addFiles} className="sr-only" />
                    {selectedFiles.length > 0 && (
                        <ul className="mt-3 space-y-2">
                            {selectedFiles.map((file, index) => (
                                <li key={file.name + file.size + index} className="flex items-center gap-2 rounded-lg bg-[var(--dr-hover)] px-3 py-2 text-xs text-[var(--dr-text-2)]">
                                    <File size={14} className="shrink-0 text-[var(--dr-text-3)]" />
                                    <span className="min-w-0 flex-1 truncate">{file.name}</span>
                                    <span className="text-[10px] text-[var(--dr-text-3)]">{Math.max(1, Math.round(file.size / 1024))} Ko</span>
                                    <button type="button" onClick={() => removeFile(index)} className="text-[var(--dr-text-3)] hover:text-[var(--dr-text)]" aria-label={"Retirer " + file.name}>
                                        <X size={13} />
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </Field>

            <Field label="Tags" htmlFor="tags" error={tagsError}>
                <TagInput id="tags" value={data.tags} onChange={(tags) => setData('tags', tags)} />
            </Field>

            <label className="flex items-center gap-2.5 text-sm text-[var(--dr-text-2)]">
                <input type="checkbox" checked={data.is_favorite} onChange={(e) => setData('is_favorite', e.target.checked)} className="h-4 w-4 rounded border-[var(--dr-border-2)] bg-[var(--dr-field)] text-[var(--dr-accent-text)] focus:ring-[#FF6A00]/30" />
                Ajouter aux favoris
            </label>

            {otherErrors.length > 0 && (
                <div role="alert" className="rounded-xl border border-red-400/20 bg-red-500/[0.06] px-4 py-3 text-sm text-[var(--dr-danger)]">
                    <p className="font-semibold">La fiche n’a pas pu être enregistrée :</p>
                    <ul className="mt-1 list-disc space-y-0.5 pl-5 text-xs">
                        {otherErrors.map((message, index) => <li key={index}>{message}</li>)}
                    </ul>
                </div>
            )}

            <div className="flex flex-wrap items-center gap-3 pt-1">
                <button type="button" onClick={handleButtonClick} disabled={processing} className={buttonClass('primary')}>
                    {processing ? 'Enregistrement...' : saved && !isDirty ? 'Enregistré ✓' : submitLabel}
                </button>
                <Link href={cancelHref} className={buttonClass('secondary')}>Annuler</Link>
            </div>
        </form>
    );
}
