import { Link } from '@inertiajs/react';
import { Field, inputClass } from '@/Components/Ui/Field';
import { buttonClass } from '@/Components/Ui/buttons';
import technologyLogos, { TECHNOLOGIES } from '@/Config/technologyLogos';

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

            <Field
                label="Technologie"
                htmlFor="technology"
                error={errors.technology}
                hint="La technologie choisie détermine le logo affiché sur la roadmap."
            >
                <select
                    id="technology"
                    value={data.technology ?? ''}
                    onChange={(e) => setData('technology', e.target.value)}
                    className={inputClass}
                    aria-invalid={errors.technology ? 'true' : undefined}
                >
                    <option value="">Sélectionner une technologie</option>
                    {TECHNOLOGIES.map((technology) => (
                        <option key={technology.value} value={technology.value}>
                            {technology.label}
                        </option>
                    ))}
                </select>

                {data.technology && technologyLogos[data.technology] && (
                    <div className="mt-3 flex items-center gap-3 rounded-xl border border-white/[0.06] bg-white/[0.025] p-3">
                        <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-white/[0.04] p-2">
                            <img
                                src={technologyLogos[data.technology]}
                                alt=""
                                className="h-full w-full object-contain"
                            />
                        </div>

                        <div>
                            <p className="text-xs font-semibold text-white">
                                Logo de la technologie
                            </p>
                            <p className="mt-0.5 text-[11px] text-slate-500">
                                {TECHNOLOGIES.find(
                                    (item) => item.value === data.technology,
                                )?.label}
                            </p>
                        </div>
                    </div>
                )}
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
