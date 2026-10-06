import { Head, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageHead, ui } from '@/Components/Ui/Design';

// Page provisoire (fonctionnelle) côté élève. Contrat de props : docs/parcours-professeur.md
export default function Index({ groups = [], shared_with_teacher = [] }) {
    const form = useForm({ code: '' });

    return (
        <AppLayout>
            <Head title="Mes groupes" />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-6">
                <PageHead eyebrow="Classe" title="Mes groupes" subtitle="Rejoins le groupe de ton professeur avec son code." />

                <form onSubmit={(event) => { event.preventDefault(); form.post('/groups/join', { onSuccess: () => form.reset() }); }} className={ui.card + ' flex flex-col gap-3 p-4'}>
                    <input className={ui.field} placeholder="Code du groupe" value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} />
                    {form.errors.code && <span className="text-sm text-[var(--dr-danger)]">{form.errors.code}</span>}
                    <button className={ui.primary} disabled={form.processing}>Rejoindre</button>
                </form>

                <p className="m-0 text-sm text-[var(--dr-text-2)]">Ton professeur voit : nom, progression, dernière activité et leçon en cours ({shared_with_teacher.length} informations). Rien d'autre.</p>

                <ul className="m-0 flex list-none flex-col gap-2 p-0">
                    {groups.map((group) => (
                        <li key={group.id} className={ui.card + ' flex items-center justify-between gap-3 p-3'}>
                            <span className="text-[var(--dr-text)]">{group.name}<br /><small className="text-[var(--dr-text-2)]">Prof. {group.teacher}</small></span>
                            <button className={ui.danger} onClick={() => router.delete(`/groups/${group.id}/leave`)}>Quitter</button>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    );
}
