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
            title="Bon retour sur DevRoad"
            description="Connecte-toi pour reprendre ton apprentissage là où tu l’as laissé."
        >
            <Head title="Connexion" />

            {status && (
                <div className="mb-5 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-300">
                    {status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <InputLabel
                        htmlFor="email"
                        value="Adresse e-mail"
                        className="mb-2 text-sm font-medium text-slate-300"
                    />
                    <div className="relative">
                        <Mail className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="block w-full rounded-2xl border-white/[0.08] bg-white/[0.04] py-3 pl-11 pr-4 text-sm text-white placeholder:text-slate-600 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="username"
                            isFocused={true}
                            placeholder="toi@exemple.com"
                            onChange={(e) => setData('email', e.target.value)}
                        />
                    </div>
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <div className="mb-2 flex items-center justify-between">
                        <InputLabel
                            htmlFor="password"
                            value="Mot de passe"
                            className="text-sm font-medium text-slate-300"
                        />
                        {canResetPassword && (
                            <Link
                                href={route('password.request')}
                                className="text-xs font-medium text-[#FF8A3D] transition hover:text-[#FF6A00]"
                            >
                                Mot de passe oublié ?
                            </Link>
                        )}
                    </div>

                    <div className="relative">
                        <LockKeyhole className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                        <TextInput
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={data.password}
                            className="block w-full rounded-2xl border-white/[0.08] bg-white/[0.04] py-3 pl-11 pr-12 text-sm text-white placeholder:text-slate-600 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="current-password"
                            placeholder="••••••••"
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        <button
                            type="button"
                            onClick={() => setShowPassword((value) => !value)}
                            className="absolute right-3.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-500 transition hover:text-slate-200"
                            aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
                        >
                            {showPassword ? <EyeOff className="h-[18px] w-[18px]" /> : <Eye className="h-[18px] w-[18px]" />}
                        </button>
                    </div>
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <label className="flex cursor-pointer items-center gap-2.5">
                    <Checkbox
                        name="remember"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                    />
                    <span className="text-sm text-slate-400">Se souvenir de moi</span>
                </label>

                <button
                    type="submit"
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-2 rounded-2xl bg-[#FF6A00] px-4 py-3.5 text-sm font-bold text-[#080B14] transition hover:bg-[#ff7a1a] focus:outline-none focus:ring-2 focus:ring-[#FF6A00]/40 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {processing ? 'Connexion...' : 'Se connecter'}
                    {!processing && <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />}
                </button>
            </form>

            <div className="my-6 flex items-center gap-3">
                <div className="h-px flex-1 bg-white/[0.07]" />
                <span className="text-xs text-slate-600">OU</span>
                <div className="h-px flex-1 bg-white/[0.07]" />
            </div>

            <p className="text-center text-sm text-slate-400">
                Pas encore de compte ?{' '}
                <Link
                    href={route('register')}
                    className="font-semibold text-[#FF8A3D] transition hover:text-[#FF6A00]"
                >
                    Créer un compte
                </Link>
            </p>
        </GuestLayout>
    );
}
