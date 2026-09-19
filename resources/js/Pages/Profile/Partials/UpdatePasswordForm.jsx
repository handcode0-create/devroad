import { useForm } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';

import { inputClass, Field } from '@/Components/Ui/Field';

export default function UpdatePasswordForm({ className = '' }) {
    const { data, setData, errors, put, reset, processing, recentlySuccessful } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const updatePassword = (e) => {
        e.preventDefault();

        put(route('password.update'), {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <section className={className}>
            <form onSubmit={updatePassword} className="space-y-5">
                <Field label="Mot de passe actuel" htmlFor="current_password" error={errors.current_password}>
                    <input
                        id="current_password"
                        type="password"
                        className={inputClass}
                        value={data.current_password}
                        onChange={(e) => setData('current_password', e.target.value)}
                        autoComplete="current-password"
                    />
                </Field>

                <Field label="Nouveau mot de passe" htmlFor="password" error={errors.password}>
                    <input
                        id="password"
                        type="password"
                        className={inputClass}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="new-password"
                    />
                </Field>

                <Field label="Confirmation" htmlFor="password_confirmation" error={errors.password_confirmation}>
                    <input
                        id="password_confirmation"
                        type="password"
                        className={inputClass}
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        autoComplete="new-password"
                    />
                </Field>

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-xl bg-[#FF6A00] px-4 py-2.5 text-sm font-bold text-[#08111F] transition hover:bg-[#ff781a] disabled:opacity-50"
                    >
                        {processing ? 'Mise à jour...' : 'Changer le mot de passe'}
                    </button>

                    {recentlySuccessful && (
                        <span className="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-400">
                            <CheckCircle2 size={15} />
                            Mot de passe mis à jour
                        </span>
                    )}
                </div>
            </form>
        </section>
    );
}
