import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Eye, EyeOff, LockKeyhole, Mail } from 'lucide-react';
import { useState } from 'react';

export default function Login({ status, canResetPassword }) {
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <GuestLayout
            title="Connectez-vous"
            description="Accédez à votre espace DevRoad et reprenez votre progression là où vous l’avez laissée."
        >
            <Head title="Connexion" />

            {status && (
                <div className="mb-4 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-300">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="email" value="Adresse e-mail" className="sr-only" />
                    <div className="relative">
                        <Mail className="pointer-events-none absolute left-4 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-white/35" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="block w-full rounded-2xl border border-white/[0.15] bg-white/[0.035] py-3.5 pl-11 pr-4 text-sm text-white placeholder:text-white/35 shadow-inner shadow-white/[0.02] focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="username"
                            isFocused={true}
                            placeholder="Adresse e-mail"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </div>
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <div className="relative">
                        <LockKeyhole className="pointer-events-none absolute left-4 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-white/35" />
                        <TextInput
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={data.password}
                            className="block w-full rounded-2xl border border-white/[0.15] bg-white/[0.035] py-3.5 pl-11 pr-12 text-sm text-white placeholder:text-white/35 shadow-inner shadow-white/[0.02] focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="current-password"
                            placeholder="Mot de passe"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword((value) => !value)}
                            className="absolute right-3.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-white/35 transition hover:text-white"
                            aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                        >
                            {showPassword ? <EyeOff className="h-[17px] w-[17px]" /> : <Eye className="h-[17px] w-[17px]" />}
                        </button>
                    </div>
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="flex items-center justify-between px-1">
                    <label className="flex cursor-pointer items-center gap-2">
                        <Checkbox
                            name="remember"
                            checked={data.remember}
                            onChange={(e) => setData('remember', e.target.checked)}
                        />
                        <span className="text-xs text-white/45">Se souvenir de moi</span>
                    </label>

                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="text-xs font-semibold text-[#FF8A3D] transition hover:text-[#FF6A00]"
                        >
                            Mot de passe oublié ?
                        </Link>
                    )}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-3 rounded-2xl bg-[#FF6A00] px-4 py-3.5 text-sm font-bold text-white shadow-[0_10px_35px_rgba(255,106,0,.22)] transition hover:bg-[#ff7a1a] focus:outline-none focus:ring-2 focus:ring-[#FF6A00]/40 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {processing ? 'Connexion...' : 'Se connecter'}
                    {!processing && <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />}
                </button>
            </form>

            <div className="my-5 flex items-center gap-3">
                <div className="h-px flex-1 bg-white/[0.09]" />
                <span className="text-[10px] font-medium uppercase tracking-[0.16em] text-white/30">Ou</span>
                <div className="h-px flex-1 bg-white/[0.09]" />
            </div>

            <p className="text-center text-sm text-white/45">
                Pas encore de compte ?{' '}
                <Link
                    href={route('register')}
                    className="font-semibold text-[#FF8A3D] transition hover:text-[#FF6A00]"
                >
                    S'inscrire
                </Link>
            </p>
        </GuestLayout>
    );
}
