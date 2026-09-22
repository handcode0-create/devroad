import { Link } from '@inertiajs/react';
import { Field, inputClass } from '@/Components/Ui/Field';
import { buttonClass } from '@/Components/Ui/buttons';
import TagInput from '@/Components/Memos/TagInput';
import MemoEditor, { autoMemoTitle } from '@/Components/Memos/MemoEditor';

export default function MemoForm({ form, onSubmit, submitLabel, cancelHref }) {
    const { data, setData, errors, processing } = form;
    const tagsError = errors.tags ?? Object.entries(errors).find(([key]) => key.startsWith('tags.'))?.[1];
    function applyAutoTitle(title) { if (title) setData('title', title); }
    function handleSubmit(event) {
        if (!data.title.trim()) { const title = autoMemoTitle(data.content); if (title) setData('title', title); }
        onSubmit(event);
    }
    return (
        <form onSubmit={handleSubmit} noValidate className="space-y-5 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-6">
            <Field label="Titre" htmlFor="title" error={errors.title} hint="Laisse vide pour générer automatiquement le titre depuis la première ligne.">
                <input id="title" type="text" value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="Titre de la fiche" maxLength={255} autoFocus aria-invalid={errors.title ? 'true' : undefined} className={inputClass} />
            </Field>
            <Field label="Contenu" htmlFor="content" error={errors.content} hint="Les blocs de code entre triples accents restent affichés en monospace.">
                <MemoEditor content={data.content} onContentChange={(content) => setData('content', content)} formatting={data.formatting} onFormattingChange={(formatting) => setData('formatting', formatting)} onAutoTitle={applyAutoTitle} />
            </Field>
            <Field label="Tags" htmlFor="tags" error={tagsError}><TagInput id="tags" value={data.tags} onChange={(tags) => setData('tags', tags)} /></Field>
            <label className="flex items-center gap-2.5 text-sm text-slate-300"><input type="checkbox" checked={data.is_favorite} onChange={(e) => setData('is_favorite', e.target.checked)} className="h-4 w-4 rounded border-white/20 bg-[#101A2A] text-[#FF6A00] focus:ring-[#FF6A00]/30" />Ajouter aux favoris</label>
            <div className="flex flex-wrap items-center gap-3 pt-1"><button type="submit" disabled={processing} className={buttonClass('primary')}>{processing ? 'Enregistrement...' : submitLabel}</button><Link href={cancelHref} className={buttonClass('secondary')}>Annuler</Link></div>
        </form>
    );
}