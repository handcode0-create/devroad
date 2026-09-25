import { Head, Link, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import AuthLayout, { AuthLink } from '@/Components/Auth/AuthLayout';
import AuthButton from '@/Components/Auth/AuthButton';

const levelLabels = {
    beginner: 'Débutant',
    intermediate: 'Intermédiaire',
    professional: 'Professionnel',
};

export default function Onboarding({ academicLevels, technologies, goals, categories, profile }) {
    const categoryEntries = Object.entries(categories ?? {});
    const [step, setStep] = useState(0);
    const [categoryIndex, setCategoryIndex] = useState(0);
    const [technologySearch, setTechnologySearch] = useState('');

    const { data, setData, post, processing, errors } = useForm({
        level: profile?.level ?? '',
        academic_level: profile?.academic_level ?? '',
        experience_years: profile?.experience_years ?? '',
        technologies: profile?.technologies ?? [],
        goals: profile?.goals ?? [],
        answers: profile?.answers ?? {},
    });

    const currentCategory = categoryEntries[categoryIndex]?.[1];
    const currentQuestions = currentCategory?.questions ?? [];

    const assessmentProgress = useMemo(() => {
        const total = categoryEntries.reduce((sum, item) => sum + item[1].questions.length, 0);
        const answered = Object.keys(data.answers).length;
        return total ? Math.round((answered / total) * 100) : 0;
    }, [categoryEntries, data.answers]);

    const estimatedLevel = useMemo(() => {
        let score = 0;
        let maximum = 0;

        categoryEntries.forEach((item) => {
            item[1].questions.forEach((question) => {
                maximum += 2;
                const answer = data.answers[question.id];
                const option = question.options.find((choice) => choice.id === answer);
                score += Number(option?.score ?? 0);
            });
        });

        const percentage = maximum ? (score / maximum) * 100 : 0;
        if (percentage >= 70) return 'professional';
        if (percentage >= 40) return 'intermediate';
        return 'beginner';
    }, [categoryEntries, data.answers]);

    const filteredTechnologies = Object.entries(technologies ?? {}).filter((item) =>
        item[1].toLowerCase().includes(technologySearch.toLowerCase()) ||
        item[0].toLowerCase().includes(technologySearch.toLowerCase())
    );

    function toggle(field, value, limit) {
        const current = data[field] ?? [];
        if (current.includes(value)) {
            setData(field, current.filter((item) => item !== value));
        } else if (current.length < limit) {
            setData(field, [...current, value]);
        }
    }

    function next() {
        if (step === 0 && data.academic_level && data.experience_years !== '') {
            setStep(1);
            return;
        }

        if (step === 1 && data.technologies.length && data.goals.length) {
            setStep(2);
            return;
        }

        if (step === 2 && currentQuestions.every((question) => data.answers[question.id])) {
            if (categoryIndex < categoryEntries.length - 1) {
                setCategoryIndex((value) => value + 1);
            } else {
                setStep(3);
            }
        }
    }

    function previous() {
        if (step === 1) setStep(0);
        else if (step === 2 && categoryIndex > 0) setCategoryIndex((value) => value - 1);
        else if (step === 2) setStep(1);
        else if (step === 3) {
            setStep(2);
            setCategoryIndex(categoryEntries.length - 1);
        }
    }

    function submit(event) {
        event?.preventDefault();
        post(route('onboarding.store'));
    }

    return (
        <AuthLayout
            title={['Construisons ton parcours', 'Ton terrain de jeu', currentCategory?.label ?? 'Évaluation', 'Ton profil est prêt'][step]}
            subtitle={[
                'Quelques informations pour personnaliser ton expérience.',
                'Choisis ce que tu veux apprendre. Aucun parcours ne sera verrouillé.',
                'Réponds naturellement : cette évaluation sert à adapter les recommandations.',
                'Ton niveau choisi est conservé. Le questionnaire fournit aussi une recommandation.',
            ][step]}
            footer={
                <AuthLink href={route('logout')} method="post" as="button">
                    Quitter l’onboarding
                </AuthLink>
            }
        >
            <Head title="Personnaliser mon parcours" />

            <form onSubmit={submit} noValidate>
                <div className="mb-7">
                <div className="mb-2 flex items-center justify-between text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                    <span>Étape {step + 1} / 4</span>
                    <span>{step === 2 ? assessmentProgress + '% évalué' : 'Profil'}</span>
                </div>
                <div className="h-1.5 overflow-hidden rounded-full bg-white/[0.06]">
                    <div className="h-full rounded-full bg-[#FF6A00] transition-all" style={{ width: ((step + 1) / 4) * 100 + '%' }} />
                </div>
            </div>

            {step === 0 && (
                <section className="space-y-5">
                    <ChoiceGroup
                        title="Ton parcours académique"
                        options={academicLevels}
                        value={data.academic_level}
                        onChange={(value) => setData('academic_level', value)}
                        single
                    />
                    <div>
                        <label className="mb-2 block text-sm font-semibold text-white">
                            Années d’expérience en développement
                        </label>
                        <input
                            type="number"
                            min="0"
                            max="50"
                            value={data.experience_years}
                            onChange={(event) => setData('experience_years', event.target.value)}
                            className="h-12 w-full rounded-xl border border-white/[0.08] bg-[#101A2A] px-4 text-white outline-none focus:border-[#FF6A00]/50"
                            placeholder="0"
                        />
                    </div>
                </section>
            )}

            {step === 1 && (
                <section className="space-y-6">
                    <div>
                        <div className="mb-3 flex items-end justify-between">
                            <div>
                                <h2 className="text-sm font-semibold text-white">Technologies</h2>
                                <p className="mt-1 text-xs text-slate-500">Jusqu’à 10 technologies.</p>
                            </div>
                            <span className="text-xs text-slate-500">{data.technologies.length}/10</span>
                        </div>
                        <input
                            value={technologySearch}
                            onChange={(event) => setTechnologySearch(event.target.value)}
                            placeholder="Rechercher une technologie..."
                            className="mb-3 h-11 w-full rounded-xl border border-white/[0.08] bg-[#101A2A] px-4 text-sm text-white outline-none focus:border-[#FF6A00]/50"
                        />
                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            {filteredTechnologies.map((item) => (
                                <Choice
                                    key={item[0]}
                                    label={item[1]}
                                    active={data.technologies.includes(item[0])}
                                    onClick={() => toggle('technologies', item[0], 10)}
                                />
                            ))}
                        </div>
                    </div>

                    <ChoiceGroup
                        title="Tes objectifs"
                        options={goals}
                        values={data.goals}
                        onChange={(value) => toggle('goals', value, 5)}
                    />
                </section>
            )}

            {step === 2 && currentCategory && (
                <section className="space-y-5">
                    <div className="flex items-center justify-between">
                        <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#FF8A3D]">
                            Catégorie {categoryIndex + 1} / {categoryEntries.length}
                        </p>
                        <p className="text-xs text-slate-500">
                            {currentQuestions.filter((question) => data.answers[question.id]).length}/{currentQuestions.length}
                        </p>
                    </div>

                    {currentQuestions.map((question, index) => (
                        <div key={question.id} className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-4">
                            <p className="text-sm font-semibold leading-6 text-white">
                                {index + 1}. {question.question}
                            </p>
                            <div className="mt-3 space-y-2">
                                {question.options.map((option) => (
                                    <button
                                        key={option.id}
                                        type="button"
                                        onClick={() => setData('answers', { ...data.answers, [question.id]: option.id })}
                                        className={[
                                            'w-full rounded-xl border px-4 py-3 text-left text-sm transition',
                                            data.answers[question.id] === option.id
                                                ? 'border-[#FF6A00]/50 bg-[#FF6A00]/10 text-white'
                                                : 'border-white/[0.07] bg-white/[0.02] text-slate-400 hover:border-white/[0.14] hover:text-white',
                                        ].join(' ')}
                                    >
                                        {option.label}
                                    </button>
                                ))}
                            </div>
                        </div>
                    ))}
                </section>
            )}

            {step === 3 && (
                <section className="space-y-5">
                    <div className="grid gap-3 sm:grid-cols-2">
                        <div className="rounded-2xl border border-[#FF6A00]/20 bg-[#FF6A00]/[0.06] p-5">
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-[#FF8A3D]">
                                Ton niveau
                            </p>
                            <p className="mt-2 text-2xl font-bold text-white">
                                {levelLabels[data.level] ?? 'Non défini'}
                            </p>
                            <p className="mt-2 text-xs leading-5 text-slate-400">
                                Niveau choisi au début de ton inscription.
                            </p>
                            <Link
                                href={route('onboarding.level')}
                                className="mt-3 inline-flex text-xs font-semibold text-[#FF8A3D] hover:text-[#FFA66E]"
                            >
                                Modifier mon niveau
                            </Link>
                        </div>

                        <div className="rounded-2xl border border-white/[0.06] bg-[#0D1725] p-5">
                            <p className="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">
                                Recommandation du questionnaire
                            </p>
                            <p className="mt-2 text-2xl font-bold text-white">
                                {levelLabels[estimatedLevel]}
                            </p>
                            <p className="mt-2 text-xs leading-5 text-slate-400">
                                Cette recommandation est enregistrée avec ton score sans remplacer ton choix.
                            </p>
                        </div>
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        {categoryEntries.map((item) => (
                            <div key={item[0]} className="rounded-xl border border-white/[0.06] bg-[#0D1725] p-4">
                                <p className="text-sm font-semibold text-white">{item[1].label}</p>
                                <p className="mt-1 text-xs text-slate-500">
                                    {item[1].questions.filter((question) => data.answers[question.id]).length}/{item[1].questions.length} réponses
                                </p>
                            </div>
                        ))}
                    </div>
                </section>
            )}

            <div className="mt-7 flex gap-3">
                {step > 0 && (
                    <button type="button" onClick={previous} className="flex-1 rounded-xl border border-white/[0.08] bg-white/[0.03] px-4 py-3 text-sm font-semibold text-slate-300">
                        Retour
                    </button>
                )}

                {step < 3 ? (
                    <button
                        type="button"
                        onClick={next}
                        disabled={
                            (step === 0 && (!data.academic_level || data.experience_years === '')) ||
                            (step === 1 && (!data.technologies.length || !data.goals.length)) ||
                            (step === 2 && !currentQuestions.every((question) => data.answers[question.id]))
                        }
                        className="flex-1 rounded-xl bg-[#FF6A00] px-4 py-3 text-sm font-semibold text-white disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Continuer
                    </button>
                ) : (
                    <AuthButton processing={processing} busyLabel="Création de ton parcours...">
                        Commencer DevRoad
                    </AuthButton>
                )}
            </div>

                {Object.keys(errors).length > 0 && (
                <p className="mt-4 text-sm text-red-400">{Object.values(errors)[0]}</p>
            )}
                </form>
        </AuthLayout>
    );
}

function ChoiceGroup({ title, options, value, values = [], onChange, single = false }) {
    return (
        <div>
            <h2 className="text-sm font-semibold text-white">{title}</h2>
            <div className="mt-3 grid gap-2 sm:grid-cols-2">
                {Object.entries(options ?? {}).map((item) => (
                    <Choice
                        key={item[0]}
                        label={item[1]}
                        active={single ? value === item[0] : values.includes(item[0])}
                        onClick={() => onChange(item[0])}
                    />
                ))}
            </div>
        </div>
    );
}

function Choice({ label, active, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={[
                'rounded-xl border px-4 py-3 text-left text-sm font-medium transition',
                active
                    ? 'border-[#FF6A00]/50 bg-[#FF6A00]/10 text-white'
                    : 'border-white/[0.07] bg-white/[0.02] text-slate-400 hover:border-white/[0.14] hover:text-white',
            ].join(' ')}
        >
            {label}
        </button>
    );
}
