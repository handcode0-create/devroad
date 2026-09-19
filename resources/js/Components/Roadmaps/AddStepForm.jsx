import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { inputClass } from '@/Components/Ui/Field';
import { buttonClass } from '@/Components/Ui/buttons';

// À placer dans Roadmaps/Show.jsx, sous la liste des étapes :
//   <AddStepForm roadmapId={roadmap.id} />
// La position est calculée par le backend (l'étape va à la fin).
export default function AddStepForm({ roadmapId }) {
    const form = useForm({ title: '' });

    function submit(event) {
        event.preventDefault();

        form.post(`/roadmaps/${roadmapId}/steps`, {
            preserveScroll: true,
            onSuccess: () => form.reset('title'),
        });
    }

    return (
        <form onSubmit={submit} noValidate className="mt-4">
            <label htmlFor="new-step-title" className="sr-only">
                Titre de la nouvelle étape
            </label>

            <div className="flex flex-col gap-2 sm:flex-row">
                <input
                    id="new-step-title"
                    type="text"
                    value={form.data.title}
                    onChange={(e) => form.setData('title', e.target.value)}
                    placeholder="Ajouter une étape (ex. Routage)"
                    maxLength={255}
                    aria-invalid={form.errors.title ? 'true' : undefined}
                    className={inputClass}
                />

                <button type="submit" disabled={form.processing} className={`${buttonClass('primary')} shrink-0`}>
                    <Plus size={16} aria-hidden="true" />
                    Ajouter
                </button>
            </div>

            {form.errors.title && (
                <p role="alert" className="mt-1.5 text-xs font-medium text-red-400">
                    {form.errors.title}
                </p>
            )}
        </form>
    );
}
