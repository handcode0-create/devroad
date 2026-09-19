import { Head, usePage } from '@inertiajs/react';
import { KeyRound, LogOut, Mail, ShieldCheck, UserRound } from 'lucide-react';

import AppLayout from '@/Layouts/AppLayout';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import DeleteUserForm from './Partials/DeleteUserForm';

export default function Edit({ mustVerifyEmail, status }) {
    const { auth } = usePage().props;
    const user = auth?.user;

    return (
        <AppLayout>
            <Head title="Paramètres" />

            <div className="mx-auto max-w-5xl space-y-6">
                <header>
                    <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-[#FF8A3D]">
                        Compte
                    </p>
                    <h1 className="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Paramètres
                    </h1>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Gère ton profil, ton adresse e-mail et la sécurité de ton compte DevRoad.
                    </p>
                </header>

                <section className="overflow-hidden rounded-3xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725]">
                    <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-7">
                        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#FF6A00] text-[#08111F]">
                            <UserRound size={28} />
                        </div>
                        <div className="min-w-0 flex-1">
                            <p className="text-xl font-bold text-white">{user?.name ?? 'Utilisateur'}</p>
                            <div className="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                <Mail size={14} />
                                <span className="truncate">{user?.email ?? 'Compte DevRoad'}</span>
                            </div>
                        </div>
                        <div className="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400">
                            <ShieldCheck size={14} />
                            Compte sécurisé
                        </div>
                    </div>
                </section>

                <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                    <div className="mb-6 flex items-start gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                            <UserRound size={18} />
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-white">Informations du profil</h2>
                            <p className="mt-1 text-xs text-slate-500">
                                Mets à jour ton nom et ton adresse e-mail.
                            </p>
                        </div>
                    </div>

                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                        className="max-w-2xl"
                    />
                </section>

                <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                    <div className="mb-6 flex items-start gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                            <KeyRound size={18} />
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-white">Mot de passe</h2>
                            <p className="mt-1 text-xs text-slate-500">
                                Utilise un mot de passe long et unique pour protéger ton compte.
                            </p>
                        </div>
                    </div>

                    <UpdatePasswordForm className="max-w-2xl" />
                </section>

                <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                    <div className="flex items-start gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/[0.04] text-slate-400">
                            <LogOut size={18} />
                        </div>
                        <div>
                            <h2 className="text-base font-bold text-white">Session</h2>
                            <p className="mt-1 text-xs leading-5 text-slate-500">
                                Pour fermer ta session, utilise le bouton de déconnexion de ton compte.
                            </p>
                        </div>
                    </div>
                </section>

                <DeleteUserForm />
            </div>
        </AppLayout>
    );
}
