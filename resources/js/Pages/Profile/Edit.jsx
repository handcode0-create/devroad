import { Head, Link, useForm, usePage } from '@inertiajs/react';
import {
    Bell,
    CircleHelp,
    ExternalLink,
    Code2,
    KeyRound,
    LogOut,
    Mail,
    MessageCircleQuestion,
    Settings2,
    ShieldCheck,
    Target,
    UserRound,
} from 'lucide-react';
import { useState } from 'react';

import AppLayout from '@/Layouts/AppLayout';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import DeleteUserForm from './Partials/DeleteUserForm';

const goalOptions = [
    { value: 'Maîtriser une technologie', label: 'Maîtriser une technologie' },
    { value: 'Construire des projets', label: 'Construire des projets' },
    { value: 'Préparer mon portfolio', label: 'Préparer mon portfolio' },
    { value: 'Préparer mon insertion professionnelle', label: 'Préparer mon insertion professionnelle' },
];

const dailyGoalOptions = [15, 30, 45, 60, 90];

const faqItems = [
    {
        question: 'Comment créer un nouveau parcours ?',
        answer: 'Depuis Roadmaps, ouvre « Nouvelle roadmap », choisis une technologie et DevRoad génère le parcours correspondant.',
    },
    {
        question: 'Comment reprendre un cours ?',
        answer: 'Ouvre une roadmap puis sélectionne une étape. La progression de chaque étape est enregistrée lorsque tu changes son statut.',
    },
    {
        question: 'À quoi servent les objectifs ?',
        answer: 'Ils servent à définir ton rythme d’apprentissage et la technologie que tu souhaites privilégier. Ces réglages restent associés à ton compte.',
    },
    {
        question: 'Comment signaler un problème ?',
        answer: 'Utilise le lien « Signaler un problème » pour ouvrir directement les issues GitHub du projet DevRoad.',
    },
];

export default function Edit({
    mustVerifyEmail,
    status,
    preferences = {},
    technologies = [],
}) {
    const { auth } = usePage().props;
    const user = auth?.user;

    const {
        data,
        setData,
        patch,
        processing,
        errors,
    } = useForm({
        learning_goal: preferences.learning_goal ?? goalOptions[0].value,
        daily_goal_minutes: preferences.daily_goal_minutes ?? 30,
        weekly_goal_sessions: preferences.weekly_goal_sessions ?? 3,
        preferred_technology: preferences.preferred_technology ?? '',
        email_notifications: preferences.email_notifications ?? true,
        learning_reminders: preferences.learning_reminders ?? true,
    });

    const [openFaq, setOpenFaq] = useState(0);

    const savePreferences = (event) => {
        event.preventDefault();

        patch(route('profile.preferences'), {
            preserveScroll: true,
        });
    };

    const preferencesSaved = status === 'preferences-updated';

    return (
        <AppLayout>
            <Head title="Paramètres" />

            <div className="mx-auto max-w-5xl space-y-6">
                <header>
                    <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-[#FF8A3D]">
                        Compte & apprentissage
                    </p>

                    <h1 className="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Paramètres
                    </h1>

                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Personnalise ton expérience DevRoad, fixe ton rythme d’apprentissage,
                        gère tes notifications et retrouve rapidement l’aide dont tu as besoin.
                    </p>
                </header>

                <nav className="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <SettingsAnchor href="#profil" label="Profil" icon={UserRound} />
                    <SettingsAnchor href="#preferences" label="Préférences" icon={Settings2} />
                    <SettingsAnchor href="#objectifs" label="Objectifs" icon={Target} />
                    <SettingsAnchor href="#aide" label="Aide & support" icon={CircleHelp} />
                </nav>

                <section
                    id="profil"
                    className="scroll-mt-24 overflow-hidden rounded-3xl border border-white/[0.06] bg-gradient-to-br from-[#111D2E] to-[#0D1725]"
                >
                    <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-7">
                        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-[#FF6A00] text-[#08111F] shadow-[0_10px_30px_rgba(255,106,0,0.18)]">
                            <UserRound size={28} />
                        </div>

                        <div className="min-w-0 flex-1">
                            <p className="text-xl font-bold text-white">
                                {user?.name ?? 'Utilisateur'}
                            </p>

                            <div className="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                <Mail size={14} />
                                <span className="truncate">
                                    {user?.email ?? 'Compte DevRoad'}
                                </span>
                            </div>
                        </div>

                        <div className="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1.5 text-xs font-semibold text-emerald-400">
                            <ShieldCheck size={14} />
                            Compte sécurisé
                        </div>
                    </div>
                </section>

                <section
                    id="profil-informations"
                    className="scroll-mt-24 rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={UserRound}
                        title="Informations du profil"
                        description="Mets à jour ton nom, ton adresse e-mail et l’état de vérification de ton compte."
                    />

                    <UpdateProfileInformationForm
                        mustVerifyEmail={mustVerifyEmail}
                        status={status}
                        className="max-w-2xl"
                    />
                </section>

                <section
                    id="preferences"
                    className="scroll-mt-24 rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={Settings2}
                        title="Préférences"
                        description="Configure ce que DevRoad doit utiliser comme rythme et comportement d’apprentissage."
                    />

                    <form onSubmit={savePreferences} className="space-y-7">
                        <div className="grid gap-5 md:grid-cols-2">
                            <FieldSelect
                                label="Technologie prioritaire"
                                value={data.preferred_technology}
                                onChange={(value) => setData('preferred_technology', value)}
                                options={[
                                    { value: '', label: 'Aucune préférence' },
                                    ...technologies,
                                ]}
                                error={errors.preferred_technology}
                            />

                            <FieldSelect
                                label="Objectif quotidien"
                                value={String(data.daily_goal_minutes)}
                                onChange={(value) => setData('daily_goal_minutes', Number(value))}
                                options={dailyGoalOptions.map((minutes) => ({
                                    value: String(minutes),
                                    label: minutes === 1 ? '1 minute' : `${minutes} minutes par jour`,
                                }))}
                                error={errors.daily_goal_minutes}
                            />
                        </div>

                        <div className="grid gap-3 sm:grid-cols-2">
                            <ToggleRow
                                icon={Bell}
                                title="Notifications e-mail"
                                description="Recevoir les informations importantes liées à ton compte."
                                checked={Boolean(data.email_notifications)}
                                onChange={(value) => setData('email_notifications', value)}
                            />

                            <ToggleRow
                                icon={Target}
                                title="Rappels d’apprentissage"
                                description="Activer les rappels liés à ton objectif quotidien."
                                checked={Boolean(data.learning_reminders)}
                                onChange={(value) => setData('learning_reminders', value)}
                            />
                        </div>

                        <div className="flex flex-wrap items-center gap-3">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F] transition hover:bg-[#ff781a] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing ? 'Enregistrement...' : 'Enregistrer les préférences'}
                            </button>

                            {preferencesSaved && (
                                <span className="text-xs font-semibold text-emerald-400">
                                    Préférences enregistrées.
                                </span>
                            )}
                        </div>
                    </form>
                </section>

                <section
                    id="objectifs"
                    className="scroll-mt-24 rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={Target}
                        title="Objectifs d’apprentissage"
                        description="Choisis une direction claire et un rythme que tu peux tenir dans la durée."
                    />

                    <div className="grid gap-5 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-xs font-semibold text-slate-300">
                                Objectif principal
                            </label>

                            <select
                                value={data.learning_goal}
                                onChange={(event) => setData('learning_goal', event.target.value)}
                                className={selectClass}
                            >
                                {goalOptions.map((goal) => (
                                    <option key={goal.value} value={goal.value}>
                                        {goal.label}
                                    </option>
                                ))}
                            </select>

                            {errors.learning_goal && (
                                <p className="mt-1.5 text-xs text-red-400">{errors.learning_goal}</p>
                            )}
                        </div>

                        <div>
                            <label className="mb-2 block text-xs font-semibold text-slate-300">
                                Sessions ciblées par semaine
                            </label>

                            <div className="grid grid-cols-7 gap-2">
                                {[1, 2, 3, 4, 5, 6, 7].map((sessions) => {
                                    const active = Number(data.weekly_goal_sessions) === sessions;

                                    return (
                                        <button
                                            key={sessions}
                                            type="button"
                                            onClick={() => setData('weekly_goal_sessions', sessions)}
                                            className={[
                                                'flex h-10 items-center justify-center rounded-xl border text-xs font-bold transition',
                                                active
                                                    ? 'border-[#FF6A00]/40 bg-[#FF6A00] text-[#08111F]'
                                                    : 'border-white/[0.08] bg-white/[0.03] text-slate-400 hover:bg-white/[0.06] hover:text-white',
                                            ].join(' ')}
                                        >
                                            {sessions}
                                        </button>
                                    );
                                })}
                            </div>

                            <p className="mt-2 text-[11px] text-slate-500">
                                {data.weekly_goal_sessions} session
                                {Number(data.weekly_goal_sessions) > 1 ? 's' : ''} ciblée
                                {Number(data.weekly_goal_sessions) > 1 ? 's' : ''} par semaine.
                            </p>
                        </div>
                    </div>

                    <div className="mt-6 rounded-2xl border border-[#FF6A00]/10 bg-[#FF6A00]/[0.04] p-4">
                        <p className="text-xs font-semibold text-[#FF8A3D]">
                            Ton rythme actuel
                        </p>

                        <p className="mt-1 text-sm font-bold text-white">
                            {data.daily_goal_minutes} minutes par jour · {data.weekly_goal_sessions} session
                            {Number(data.weekly_goal_sessions) > 1 ? 's' : ''} par semaine
                        </p>

                        <p className="mt-1 text-xs leading-5 text-slate-500">
                            DevRoad conservera ces paramètres sur ton compte pour tes prochains parcours.
                        </p>
                    </div>

                    <div className="mt-5">
                        <button
                            type="button"
                            onClick={savePreferences}
                            disabled={processing}
                            className="rounded-xl border border-[#FF6A00]/20 bg-[#FF6A00]/10 px-4 py-2.5 text-sm font-bold text-[#FF8A3D] transition hover:bg-[#FF6A00]/15 disabled:opacity-50"
                        >
                            {processing ? 'Enregistrement...' : 'Enregistrer mes objectifs'}
                        </button>
                    </div>
                </section>

                <section
                    id="securite"
                    className="scroll-mt-24 rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={KeyRound}
                        title="Sécurité"
                        description="Protège ton compte et garde le contrôle de tes accès."
                    />

                    <UpdatePasswordForm className="max-w-2xl" />
                </section>

                <section
                    id="aide"
                    className="scroll-mt-24 rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={CircleHelp}
                        title="Aide & support"
                        description="Retrouve les réponses aux questions courantes ou signale un problème."
                    />

                    <div className="grid gap-3 md:grid-cols-2">
                        <a
                            href="https://github.com/handcode0-create/devroad/issues"
                            target="_blank"
                            rel="noreferrer"
                            className="group rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4 transition hover:border-[#FF6A00]/20 hover:bg-[#FF6A00]/[0.04]"
                        >
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/[0.04] text-slate-300">
                                    <MessageCircleQuestion size={18} />
                                </div>

                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-bold text-white">Signaler un problème</p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        Ouvrir les issues du projet DevRoad.
                                    </p>
                                </div>

                                <ExternalLink
                                    size={16}
                                    className="text-slate-500 transition group-hover:text-[#FF8A3D]"
                                />
                            </div>
                        </a>

                        <a
                            href="https://github.com/handcode0-create/devroad"
                            target="_blank"
                            rel="noreferrer"
                            className="group rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4 transition hover:border-[#FF6A00]/20 hover:bg-[#FF6A00]/[0.04]"
                        >
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/[0.04] text-slate-300">
                                    <Code2 size={18} />
                                </div>

                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-bold text-white">Projet DevRoad</p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        Consulter le dépôt et son évolution.
                                    </p>
                                </div>

                                <ExternalLink
                                    size={16}
                                    className="text-slate-500 transition group-hover:text-[#FF8A3D]"
                                />
                            </div>
                        </a>
                    </div>

                    <div className="mt-5 overflow-hidden rounded-2xl border border-white/[0.06]">
                        {faqItems.map((item, index) => {
                            const open = openFaq === index;

                            return (
                                <div
                                    key={item.question}
                                    className={index > 0 ? 'border-t border-white/[0.06]' : ''}
                                >
                                    <button
                                        type="button"
                                        onClick={() => setOpenFaq(open ? -1 : index)}
                                        className="flex w-full items-center gap-3 px-4 py-4 text-left"
                                    >
                                        <span className="flex-1 text-sm font-semibold text-slate-200">
                                            {item.question}
                                        </span>

                                        <span className="text-xs font-bold text-[#FF8A3D]">
                                            {open ? '−' : '+'}
                                        </span>
                                    </button>

                                    {open && (
                                        <div className="px-4 pb-4 text-xs leading-5 text-slate-500">
                                            {item.answer}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </section>

                <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                    <SectionHeader
                        icon={Code2}
                        title="À propos de DevRoad"
                        description="Une base de travail pour planifier, apprendre et progresser dans tes technologies."
                    />

                    <div className="grid gap-3 sm:grid-cols-3">
                        <InfoCard label="Parcours" value="Roadmaps + cours" />
                        <InfoCard label="Connaissances" value="Mémos + recherche" />
                        <InfoCard label="Interface" value="Mobile + desktop" />
                    </div>
                </section>

                <section className="rounded-3xl border border-white/[0.06] bg-[#0D1725] p-5 sm:p-7">
                    <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-start gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-white/[0.04] text-slate-400">
                                <LogOut size={18} />
                            </div>

                            <div>
                                <h2 className="text-base font-bold text-white">Session</h2>
                                <p className="mt-1 text-xs leading-5 text-slate-500">
                                    Ferme ta session actuelle sur DevRoad.
                                </p>
                            </div>
                        </div>

                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-white/[0.08] bg-white/[0.04] px-4 py-2.5 text-sm font-bold text-slate-200 transition hover:border-[#FF6A00]/30 hover:bg-[#FF6A00]/10 hover:text-[#FF8A3D] sm:w-auto"
                        >
                            <LogOut size={16} />
                            Se déconnecter
                        </Link>
                    </div>
                </section>

                <DeleteUserForm />
            </div>
        </AppLayout>
    );
}

function SettingsAnchor({ href, label, icon: Icon }) {
    return (
        <a
            href={href}
            className="inline-flex items-center justify-center gap-2 rounded-xl border border-white/[0.06] bg-[#0D1725] px-3 py-2.5 text-xs font-bold text-slate-300 transition hover:border-[#FF6A00]/20 hover:text-[#FF8A3D]"
        >
            <Icon size={15} />
            {label}
        </a>
    );
}

function SectionHeader({ icon: Icon, title, description }) {
    return (
        <div className="mb-6 flex items-start gap-3">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#FF6A00]/10 text-[#FF8A3D]">
                <Icon size={18} />
            </div>

            <div>
                <h2 className="text-base font-bold text-white">{title}</h2>
                <p className="mt-1 text-xs leading-5 text-slate-500">{description}</p>
            </div>
        </div>
    );
}

function FieldSelect({ label, value, onChange, options, error }) {
    return (
        <div>
            <label className="mb-2 block text-xs font-semibold text-slate-300">
                {label}
            </label>

            <select
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className={selectClass}
            >
                {options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>

            {error && <p className="mt-1.5 text-xs text-red-400">{error}</p>}
        </div>
    );
}

function ToggleRow({ icon: Icon, title, description, checked, onChange }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
            <div className="flex min-w-0 items-start gap-3">
                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/[0.04] text-slate-400">
                    <Icon size={17} />
                </div>

                <div className="min-w-0">
                    <p className="text-sm font-semibold text-white">{title}</p>
                    <p className="mt-1 text-xs leading-5 text-slate-500">{description}</p>
                </div>
            </div>

            <button
                type="button"
                role="switch"
                aria-checked={checked}
                onClick={() => onChange(!checked)}
                className={[
                    'relative h-7 w-12 shrink-0 rounded-full transition',
                    checked ? 'bg-[#FF6A00]' : 'bg-slate-700',
                ].join(' ')}
            >
                <span
                    className={[
                        'absolute top-1 h-5 w-5 rounded-full bg-white shadow-sm transition',
                        checked ? 'left-6' : 'left-1',
                    ].join(' ')}
                />
            </button>
        </div>
    );
}

function InfoCard({ label, value }) {
    return (
        <div className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
            <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                {label}
            </p>
            <p className="mt-2 text-sm font-semibold text-white">{value}</p>
        </div>
    );
}

const selectClass =
    'w-full rounded-xl border border-white/[0.08] bg-[#101A2A] px-3 py-2.5 text-sm text-white outline-none transition focus:border-[#FF6A00]/50 focus:ring-2 focus:ring-[#FF6A00]/10';
