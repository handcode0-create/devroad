import { Link } from '@inertiajs/react';
import { Field, inputClass } from '@/Components/Ui/Field';
import { buttonClass } from '@/Components/Ui/buttons';
import TagInput from '@/Components/Memos/TagInput';

export default function MemoForm({ form, onSubmit, submitLabel, cancelHref }) {
    const { data, setData, errors, processing } = form;

    // Erreur sur la liste (« tags ») ou sur un tag précis (« tags.0 »)
    const tagsError =
        errors.tags ?? Object.entries(errors).find(([key]) => key.startsWith('tags.'))?.[1];

    return (
        <form
            onSubmit={onSubmit}
            noValidate
            className="space-y-5 rounded-2xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-6"
        >
            <Field label="Titre" htmlFor="title" error={errors.title}>
                <input
                    id="title"
                    type="text"
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    placeholder="Ex. Commandes Artisan utiles"
                    maxLength={255}
                    autoFocus
                    aria-invalid={errors.title ? 'true' : undefined}
                    className={inputClass}
                />
            </Field>

            <Field
                label="Contenu"
                htmlFor="content"
                error={errors.content}
                hint={<>Astuce : entoure le code avec {'```'} avant et après pour l'afficher en bloc.</>}
            >
                <textarea
                    id="content"
                    rows={14}
                    value={data.content}
                    onChange={(e) => setData('content', e.target.value)}
                    maxLength={50000}
                    aria-invalid={errors.content ? 'true' : undefined}
                    className={`${inputClass} font-mono text-[13px] leading-6`}
                />
            </Field>

            <Field label="Tags" htmlFor="tags" error={tagsError}>
                <TagInput id="tags" value={data.tags} onChange={(tags) => setData('tags', tags)} />
            </Field>

            <label className="flex items-center gap-2.5 text-sm text-slate-300">
                <input
                    type="checkbox"
                    checked={data.is_favorite}
                    onChange={(e) => setData('is_favorite', e.target.checked)}
                    className="h-4 w-4 rounded border-white/20 bg-[#101A2A] text-[#FF6A00] focus:ring-[#FF6A00]/30"
                />
                Ajouter aux favoris
            </label>

            <div className="flex flex-wrap items-center gap-3 pt-1">
                <button type="submit" disabled={processing} className={buttonClass('primary')}>
                    {processing ? 'Enregistrement...' : submitLabel}
                </button>

                <Link href={cancelHref} className={buttonClass('secondary')}>
                    Annuler
                </Link>
            </div>
        </form>
    );
}
