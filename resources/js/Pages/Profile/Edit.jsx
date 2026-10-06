import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
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
    GraduationCap,
    UserRound,
    Palette,
} from 'lucide-react';
import { useState } from 'react';

import ThemePicker from '@/Components/Ui/ThemePicker';
import { PageHead } from '@/Components/Ui/Design';
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
        answer: 'Depuis Parcours, ouvre « Nouveau parcours », choisis une technologie et DevRoad génère le parcours correspondant.',
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

    const learningProfile = usePage().props.learningProfile;

    const levelLabels = {
        beginner: 'Débutant',
        intermediate: 'Intermédiaire',
        professional: 'Professionnel',
    };

    const levelDescriptions = {
        beginner: 'Tu construis tes fondamentaux et tes premières habitudes de développement.',
        intermediate: 'Tu consolides tes pratiques et développes des projets plus structurés.',
        professional: 'Tu approfondis l’architecture, la qualité et les pratiques de production.',
    };

    const selectedLevel = learningProfile?.level;
    const recommendedLevel = learningProfile?.assessment_scores?.recommended_level;
    const preferencesSaved = status === 'preferences-updated';

    return (
        <AppLayout>
            <Head title="Paramètres" />

            <div className="mx-auto max-w-5xl space-y-6 font-['Figtree',system-ui,sans-serif]">
                <PageHead title="Paramètres" subtitle="Ton profil, ton apparence, ton rythme d’apprentissage et l’aide dont tu as besoin." />

                <nav aria-label="Sections des paramètres" className="-mx-4 flex gap-2 overflow-x-auto px-4 pb-1 dr-scrollbar-none sm:mx-0 sm:px-0">
                    <SettingsAnchor href="#profil" label="Profil" icon={UserRound} />
                    <SettingsAnchor href="#preferences" label="Préférences" icon={Settings2} />
                    <SettingsAnchor href="#apparence" label="Apparence" icon={Palette} />
                    <SettingsAnchor href="#objectifs" label="Objectifs" icon={Target} />
                    <SettingsAnchor href="#aide" label="Aide & support" icon={CircleHelp} />
                </nav>

                <section
                    id="profil"
                    className="scroll-mt-24 overflow-hidden rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)]"
                >
                    <div className="flex flex-col gap-5 p-5 sm:flex-row sm:items-center sm:p-7">
                        <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-[var(--dr-accent)] text-xl font-bold text-[var(--dr-ink)]">
                            {String(user?.name ?? 'U').trim().split(/\s+/).slice(0, 2).map((part) => part[0]?.toUpperCase()).join('')}
                        </div>

                        <div className="min-w-0 flex-1">
                            <p className="font-['Manrope',sans-serif] text-xl font-extrabold tracking-[-0.02em] text-[var(--dr-text)]">
                                {user?.name ?? 'Utilisateur'}
                            </p>

                            <div className="mt-1 flex items-center gap-2 text-xs text-[var(--dr-text-3)]">
                                <Mail size={14} />
                                <span className="truncate">
                                    {user?.email ?? 'Compte DevRoad'}
                                </span>
                            </div>
                        </div>

                        <div className="inline-flex w-fit items-center gap-2 rounded-full bg-[var(--dr-field)] px-3 py-1.5 text-xs font-semibold text-[var(--dr-text-2)]">
                            <ShieldCheck size={14} />
                            Compte sécurisé
                        </div>
                    </div>
                </section>

                <section
                    id="niveau-apprentissage"
                    className="scroll-mt-24 overflow-hidden rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
                >
                    <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-start gap-4">
                            <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]">
                                <GraduationCap size={22} />
                            </div>
                            <div>
                                <p className="text-[10px] font-bold uppercase tracking-[0.16em] text-[var(--dr-accent-text)]">Niveau d’apprentissage</p>
                                <div className="mt-1 flex flex-wrap items-center gap-2">
                                    <h2 className="text-xl font-bold text-[var(--dr-text)]">{levelLabels[selectedLevel] ?? 'Non défini'}</h2>
                                    {selectedLevel && <span className="rounded-full border border-[#FF6A00]/25 bg-[var(--dr-accent-soft)] px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.12em] text-[var(--dr-accent-text)]">Niveau choisi</span>}
                                </div>
                                <p className="mt-2 max-w-2xl text-xs leading-5 text-[var(--dr-text-2)]">{levelDescriptions[selectedLevel] ?? 'Choisis ton niveau pour personnaliser les évaluations et recommandations DevRoad.'}</p>
                                {recommendedLevel && recommendedLevel !== selectedLevel && <p className="mt-2 text-xs text-[var(--dr-text-3)]">Le questionnaire recommande : <span className="font-semibold text-[var(--dr-text-2)]">{levelLabels[recommendedLevel]}</span>.</p>}
                            </div>
                        </div>
                        <Link href={route('onboarding.level')} className="inline-flex w-full shrink-0 items-center justify-center rounded-xl border border-[#FF6A00]/20 bg-[var(--dr-accent-soft)] px-4 py-2.5 text-sm font-bold text-[var(--dr-accent-text)] transition hover:bg-[var(--dr-accent-soft)] sm:w-auto">Modifier mon niveau</Link>
                    </div>
                </section>

                <section
                    id="profil-informations"
                    className="scroll-mt-24 rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
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
                    className="scroll-mt-24 rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
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
                                className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[var(--dr-ink)] transition hover:bg-[#ff781a] disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {processing ? 'Enregistrement...' : 'Enregistrer les préférences'}
                            </button>

                            {preferencesSaved && (
                                <span className="text-xs font-semibold text-[var(--dr-accent-text)]">
                                    Préférences enregistrées.
                                </span>
                            )}
                        </div>
                    </form>
                </section>

                <section
                    id="apparence"
                    className="scroll-mt-24 rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={Palette}
                        title="Apparence"
                        description="Choisis ton thème : il s’applique tout de suite et suit ton compte sur tous tes appareils."
                    />
                    <div className="mt-5 flex flex-col gap-4">
                        <ThemePicker preference={user?.theme ?? 'nuit'} columns="grid-cols-3 sm:grid-cols-5" />
                    </div>
                </section>

                <section
                    id="objectifs"
                    className="scroll-mt-24 rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
                >
                    <SectionHeader
                        icon={Target}
                        title="Objectifs d’apprentissage"
                        description="Choisis une direction claire et un rythme que tu peux tenir dans la durée."
                    />

                    <div className="grid gap-5 md:grid-cols-2">
                        <div>
                            <label className="mb-2 block text-xs font-semibold text-[var(--dr-text-2)]">
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
                                <p className="mt-1.5 text-xs text-[var(--dr-danger)]">{errors.learning_goal}</p>
                            )}
                        </div>

                        <div>
                            <label className="mb-2 block text-xs font-semibold text-[var(--dr-text-2)]">
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
                                                    ? 'border-[#FF6A00]/40 bg-[#FF6A00] text-[var(--dr-ink)]'
                                                    : 'border-[var(--dr-border)] bg-[var(--dr-hover)] text-[var(--dr-text-2)] hover:bg-[var(--dr-hover)] hover:text-[var(--dr-text)]',
                                            ].join(' ')}
                                        >
                                            {sessions}
                                        </button>
                                    );
                                })}
                            </div>

                            <p className="mt-2 text-[11px] text-[var(--dr-text-3)]">
                                {data.weekly_goal_sessions} session
                                {Number(data.weekly_goal_sessions) > 1 ? 's' : ''} ciblée
                                {Number(data.weekly_goal_sessions) > 1 ? 's' : ''} par semaine.
                            </p>
                        </div>
                    </div>

                    <div className="mt-6 rounded-2xl border border-[#FF6A00]/10 bg-[#FF6A00]/[0.04] p-4">
                        <p className="text-xs font-semibold text-[var(--dr-accent-text)]">
                            Ton rythme actuel
                        </p>

                        <p className="mt-1 text-sm font-bold text-[var(--dr-text)]">
                            {data.daily_goal_minutes} minutes par jour · {data.weekly_goal_sessions} session
                            {Number(data.weekly_goal_sessions) > 1 ? 's' : ''} par semaine
                        </p>

                        <p className="mt-1 text-xs leading-5 text-[var(--dr-text-3)]">
                            DevRoad conservera ces paramètres sur ton compte pour tes prochains parcours.
                        </p>
                    </div>

                    <div className="mt-5">
                        <button
                            type="button"
                            onClick={savePreferences}
                            disabled={processing}
                            className="rounded-xl border border-[#FF6A00]/20 bg-[var(--dr-accent-soft)] px-4 py-2.5 text-sm font-bold text-[var(--dr-accent-text)] transition hover:bg-[var(--dr-accent-soft)] disabled:opacity-50"
                        >
                            {processing ? 'Enregistrement...' : 'Enregistrer mes objectifs'}
                        </button>
                    </div>
                </section>

                <section
                    id="securite"
                    className="scroll-mt-24 rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
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
                    className="scroll-mt-24 rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7"
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
                            className="group rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-hover)] p-4 transition hover:border-[#FF6A00]/20 hover:bg-[#FF6A00]/[0.04]"
                        >
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--dr-hover)] text-[var(--dr-text-2)]">
                                    <MessageCircleQuestion size={18} />
                                </div>

                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-bold text-[var(--dr-text)]">Signaler un problème</p>
                                    <p className="mt-1 text-xs text-[var(--dr-text-3)]">
                                        Ouvrir les issues du projet DevRoad.
                                    </p>
                                </div>

                                <ExternalLink
                                    size={16}
                                    className="text-[var(--dr-text-3)] transition group-hover:text-[var(--dr-accent-text)]"
                                />
                            </div>
                        </a>

                        <a
                            href="https://github.com/handcode0-create/devroad"
                            target="_blank"
                            rel="noreferrer"
                            className="group rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-hover)] p-4 transition hover:border-[#FF6A00]/20 hover:bg-[#FF6A00]/[0.04]"
                        >
                            <div className="flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--dr-hover)] text-[var(--dr-text-2)]">
                                    <Code2 size={18} />
                                </div>

                                <div className="min-w-0 flex-1">
                                    <p className="text-sm font-bold text-[var(--dr-text)]">Projet DevRoad</p>
                                    <p className="mt-1 text-xs text-[var(--dr-text-3)]">
                                        Consulter le dépôt et son évolution.
                                    </p>
                                </div>

                                <ExternalLink
                                    size={16}
                                    className="text-[var(--dr-text-3)] transition group-hover:text-[var(--dr-accent-text)]"
                                />
                            </div>
                        </a>
                    </div>

                    <div className="mt-5 overflow-hidden rounded-2xl border border-[var(--dr-border)]">
                        {faqItems.map((item, index) => {
                            const open = openFaq === index;

                            return (
                                <div
                                    key={item.question}
                                    className={index > 0 ? 'border-t border-[var(--dr-border)]' : ''}
                                >
                                    <button
                                        type="button"
                                        onClick={() => setOpenFaq(open ? -1 : index)}
                                        className="flex w-full items-center gap-3 px-4 py-4 text-left"
                                    >
                                        <span className="flex-1 text-sm font-semibold text-[var(--dr-text)]">
                                            {item.question}
                                        </span>

                                        <span className="text-xs font-bold text-[var(--dr-accent-text)]">
                                            {open ? '−' : '+'}
                                        </span>
                                    </button>

                                    {open && (
                                        <div className="px-4 pb-4 text-xs leading-5 text-[var(--dr-text-3)]">
                                            {item.answer}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </section>

                <section className="rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7">
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

                <section className="rounded-3xl border border-[var(--dr-border)] bg-[var(--dr-surface)] p-5 sm:p-7">
                    <div className="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-start gap-3">
                            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-[var(--dr-hover)] text-[var(--dr-text-2)]">
                                <LogOut size={18} />
                            </div>

                            <div>
                                <h2 className="text-base font-bold text-[var(--dr-text)]">Session</h2>
                                <p className="mt-1 text-xs leading-5 text-[var(--dr-text-3)]">
                                    Ferme ta session actuelle sur DevRoad.
                                </p>
                            </div>
                        </div>

                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[var(--dr-border)] bg-[var(--dr-hover)] px-4 py-2.5 text-sm font-bold text-[var(--dr-text)] transition hover:border-[#FF6A00]/30 hover:bg-[var(--dr-accent-soft)] hover:text-[var(--dr-accent-text)] sm:w-auto"
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
            className="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-full border border-[var(--dr-border)] bg-[var(--dr-surface)] px-4 text-[13px] font-semibold text-[var(--dr-text-2)] transition hover:border-[#FF6A00]/20 hover:text-[var(--dr-accent-text)]"
        >
            <Icon size={15} />
            {label}
        </a>
    );
}

function SectionHeader({ icon: Icon, title, description }) {
    return (
        <div className="mb-6 flex items-start gap-3">
            <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[var(--dr-accent-soft)] text-[var(--dr-accent-text)]">
                <Icon size={18} />
            </div>

            <div>
                <h2 className="text-base font-bold text-[var(--dr-text)]">{title}</h2>
                <p className="mt-1 text-xs leading-5 text-[var(--dr-text-3)]">{description}</p>
            </div>
        </div>
    );
}

function FieldSelect({ label, value, onChange, options, error }) {
    return (
        <div>
            <label className="mb-2 block text-xs font-semibold text-[var(--dr-text-2)]">
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

            {error && <p className="mt-1.5 text-xs text-[var(--dr-danger)]">{error}</p>}
        </div>
    );
}

function ToggleRow({ icon: Icon, title, description, checked, onChange }) {
    return (
        <div className="flex items-center justify-between gap-4 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-hover)] p-4">
            <div className="flex min-w-0 items-start gap-3">
                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[var(--dr-hover)] text-[var(--dr-text-2)]">
                    <Icon size={17} />
                </div>

                <div className="min-w-0">
                    <p className="text-sm font-semibold text-[var(--dr-text)]">{title}</p>
                    <p className="mt-1 text-xs leading-5 text-[var(--dr-text-3)]">{description}</p>
                </div>
            </div>

            <button
                type="button"
                role="switch"
                aria-checked={checked}
                onClick={() => onChange(!checked)}
                className={[
                    'relative h-7 w-12 shrink-0 rounded-full transition',
                    checked ? 'bg-[#FF6A00]' : 'bg-[var(--dr-border-2)]',
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
        <div className="rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-hover)] p-4">
            <p className="text-[10px] font-bold uppercase tracking-[0.14em] text-[var(--dr-text-3)]">
                {label}
            </p>
            <p className="mt-2 text-sm font-semibold text-[var(--dr-text)]">{value}</p>
        </div>
    );
}

const selectClass =
    'w-full rounded-xl border border-[var(--dr-border)] bg-[var(--dr-field)] px-3 py-2.5 text-sm text-[var(--dr-text)] outline-none transition focus:border-[#FF6A00]/50 focus:ring-2 focus:ring-[#FF6A00]/10';
