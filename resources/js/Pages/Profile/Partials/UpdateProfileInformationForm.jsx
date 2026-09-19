import { Link, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';

import { inputClass, Field } from '@/Components/Ui/Field';

export default function UpdateProfileInformation({ mustVerifyEmail, status, className = '' }) {
    const user = usePage().props.auth.user;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        name: user.name,
        email: user.email,
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'));
    };

    return (
        <section className={className}>
            <form onSubmit={submit} className="space-y-5">
                <Field label="Nom" htmlFor="name" error={errors.name}>
                    <input
                        id="name"
                        className={inputClass}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                        autoComplete="name"
                    />
                </Field>

                <Field label="Adresse e-mail" htmlFor="email" error={errors.email}>
                    <input
                        id="email"
                        type="email"
                        className={inputClass}
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        required
                        autoComplete="username"
                    />
                </Field>

                {mustVerifyEmail && user.email_verified_at === null && (
                    <div className="rounded-2xl border border-amber-400/10 bg-amber-400/[0.05] p-4 text-xs leading-5 text-amber-300">
                        Ton adresse e-mail n'est pas encore vérifiée.{' '}
                        <Link
                            href={route('verification.send')}
                            method="post"
                            as="button"
                            className="font-semibold underline underline-offset-2"
                        >
                            Renvoyer le lien de vérification
                        </Link>
                        {status === 'verification-link-sent' && (
                            <p className="mt-2 text-emerald-300">
                                Un nouveau lien de vérification a été envoyé.
                            </p>
                        )}
                    </div>
                )}

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F] transition hover:bg-[#ff781a] disabled:opacity-50"
                    >
                        {processing ? 'Enregistrement...' : 'Enregistrer'}
                    </button>

                    {recentlySuccessful && (
                        <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
                            <CheckCircle2 size={15} />
                            Enregistré
                        </span>
                    )}
                </div>
            </form>
        </section>
    );
}
