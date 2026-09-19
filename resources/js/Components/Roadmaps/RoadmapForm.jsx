import { Link } from '@inertiajs/react';
import { Field, inputClass } from '@/Components/Ui/Field';
import { buttonClass } from '@/Components/Ui/buttons';

const STATUSES = [
    { value: 'draft', label: 'Brouillon' },
    { value: 'active', label: 'En cours' },
    { value: 'completed', label: 'Terminée' },
    { value: 'archived', label: 'Archivée' },
];

export default function RoadmapForm({ form, onSubmit, submitLabel, cancelHref }) {
    const { data, setData, errors, processing } = form;

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
                    placeholder="Ex. Laravel, de débutant à avancé"
                    maxLength={255}
                    autoFocus
                    aria-invalid={errors.title ? 'true' : undefined}
                    className={inputClass}
                />
            </Field>

            <Field label="Description" htmlFor="description" error={errors.description} hint="Optionnel : à quoi sert ce parcours ?">
                <textarea
                    id="description"
                    rows={4}
                    value={data.description ?? ''}
                    onChange={(e) => setData('description', e.target.value)}
                    maxLength={5000}
                    className={inputClass}
                />
            </Field>

            <Field label="Statut" htmlFor="status" error={errors.status}>
                <select
                    id="status"
                    value={data.status}
                    onChange={(e) => setData('status', e.target.value)}
                    className={inputClass}
                >
                    {STATUSES.map((status) => (
                        <option key={status.value} value={status.value}>
                            {status.label}
                        </option>
                    ))}
                </select>
            </Field>

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
