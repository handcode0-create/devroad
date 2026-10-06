import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageHead, ui } from '@/Components/Ui/Design';

// Page provisoire (fonctionnelle) du parcours professeur : remplace-la par ta propre maquette.
// Contrat de props : docs/parcours-professeur.md
export default function Dashboard({ groups = [], technologies = [] }) {
    const form = useForm({ name: '', technology: '' });

    return (
        <AppLayout>
            <Head title="Espace professeur" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-6">
                <PageHead eyebrow="Professeur" title="Mes groupes" subtitle="Crée un groupe, partage son code, suis la progression." />

                <form
                    onSubmit={(event) => { event.preventDefault(); form.post('/teacher/groups', { onSuccess: () => form.reset() }); }}
                    className={ui.card + ' flex flex-col gap-3 p-4'}
                >
                    <input className={ui.field} placeholder="Nom du groupe (ex. Terminale D — option info)" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                    {form.errors.name && <span className="text-sm text-[var(--dr-danger)]">{form.errors.name}</span>}
                    <select className={ui.field} value={form.data.technology} onChange={(e) => form.setData('technology', e.target.value)}>
                        <option value="">Toutes les technologies</option>
                        {technologies.map((tech) => <option key={tech} value={tech}>{tech}</option>)}
                    </select>
                    <button className={ui.primary} disabled={form.processing}>Créer le groupe</button>
                </form>

                <ul className="m-0 flex list-none flex-col gap-3 p-0">
                    {groups.map((group) => (
                        <li key={group.id}>
                            <Link href={`/teacher/groups/${group.id}`} className={ui.card + ' flex items-center justify-between gap-3 p-4 text-[var(--dr-text)] no-underline'}>
                                <span className="font-bold">{group.name}</span>
                                <span className="text-sm text-[var(--dr-text-2)]">{group.students_count} élève(s) · code {group.join_code}</span>
                            </Link>
                        </li>
                    ))}
                    {groups.length === 0 && <li className="text-sm text-[var(--dr-text-2)]">Aucun groupe pour l'instant.</li>}
                </ul>
            </div>
        </AppLayout>
    );
}
