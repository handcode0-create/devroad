import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageHead, ui } from '@/Components/Ui/Design';

// Page provisoire (fonctionnelle) : stats d'un groupe. Contrat de props : docs/parcours-professeur.md
export default function GroupShow({ group, stats }) {
    return (
        <AppLayout>
            <Head title={group.name} />
            <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 px-4 py-6">
                <PageHead back={{ href: '/teacher', label: 'Mes groupes' }} eyebrow={group.technology ?? 'Toutes technologies'} title={group.name} subtitle={`Code d'invitation : ${group.join_code}`} />

                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    {[
                        ['Élèves', stats.members_count],
                        ['Progression moy.', `${stats.average_progress} %`],
                        ['Actifs (7 j)', stats.active_last_7_days],
                        ['Inactifs', stats.stalled_count],
                    ].map(([label, value]) => (
                        <div key={label} className={ui.card + ' p-4'}>
                            <div className="text-xs text-[var(--dr-text-2)]">{label}</div>
                            <div className="text-2xl font-extrabold text-[var(--dr-text)]">{value}</div>
                        </div>
                    ))}
                </div>

                {stats.blocking_step && (
                    <p className="m-0 text-sm text-[var(--dr-text-2)]">
                        Étape où le plus d'élèves en sont : « {stats.blocking_step.title} » ({stats.blocking_step.students}).
                    </p>
                )}

                <ul className="m-0 flex list-none flex-col gap-2 p-0">
                    {stats.members.map((member) => (
                        <li key={member.id} className={ui.card + ' flex items-center justify-between gap-3 p-3'}>
                            <span className="text-[var(--dr-text)]">{member.name}<br /><small className="text-[var(--dr-text-2)]">{member.current_step ?? '—'}</small></span>
                            <span className="text-sm text-[var(--dr-text-2)]">{member.progress} %</span>
                            <button className={ui.danger} onClick={() => router.delete(`/teacher/groups/${group.id}/students/${member.id}`)}>Retirer</button>
                        </li>
                    ))}
                </ul>

                <div className="flex flex-wrap gap-2">
                    <button className={ui.secondary} onClick={() => router.post(`/teacher/groups/${group.id}/code`)}>Nouveau code</button>
                    <button className={ui.secondary} onClick={() => router.patch(`/teacher/groups/${group.id}`, { archived: !group.archived })}>{group.archived ? 'Rouvrir' : 'Archiver'}</button>
                    <Link as="button" method="delete" href={`/teacher/groups/${group.id}`} className={ui.danger}>Supprimer</Link>
                </div>
            </div>
        </AppLayout>
    );
}
