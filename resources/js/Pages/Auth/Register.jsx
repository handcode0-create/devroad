import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowRight, Eye, EyeOff, LockKeyhole, Mail, UserRound } from 'lucide-react';
import { useState } from 'react';

export default function Register() {
    const [showPassword, setShowPassword] = useState(false);
    const [showPasswordConfirmation, setShowPasswordConfirmation] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout
            title="Crée ton espace DevRoad"
            description="Un compte suffit pour sauvegarder tes roadmaps, suivre ta progression et construire tes mémos."
        >
            <Head title="Créer un compte" />

            <form onSubmit={submit} className="space-y-4.5">
                <div>
                    <InputLabel
                        htmlFor="name"
                        value="Nom"
                        className="mb-2 text-sm font-medium text-slate-300"
                    />
                    <div className="relative">
                        <UserRound className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                        <TextInput
                            id="name"
                            name="name"
                            value={data.name}
                            className="block w-full rounded-2xl border-white/[0.08] bg-white/[0.04] py-3 pl-11 pr-4 text-sm text-white placeholder:text-slate-600 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="name"
                            isFocused={true}
                            placeholder="Ton nom"
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                    </div>
                    <InputError message={errors.name} className="mt-2" />
                </div>

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
                            placeholder="toi@exemple.com"
                            onChange={(e) => setData('email', e.target.value)}
                            required
                        />
                    </div>
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel
                        htmlFor="password"
                        value="Mot de passe"
                        className="mb-2 text-sm font-medium text-slate-300"
                    />
                    <div className="relative">
                        <LockKeyhole className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                        <TextInput
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={data.password}
                            className="block w-full rounded-2xl border-white/[0.08] bg-white/[0.04] py-3 pl-11 pr-12 text-sm text-white placeholder:text-slate-600 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="new-password"
                            placeholder="••••••••"
                            onChange={(e) => setData('password', e.target.value)}
                            required
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

                <div>
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirmer le mot de passe"
                        className="mb-2 text-sm font-medium text-slate-300"
                    />
                    <div className="relative">
                        <LockKeyhole className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-slate-500" />
                        <TextInput
                            id="password_confirmation"
                            type={showPasswordConfirmation ? 'text' : 'password'}
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="block w-full rounded-2xl border-white/[0.08] bg-white/[0.04] py-3 pl-11 pr-12 text-sm text-white placeholder:text-slate-600 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="new-password"
                            placeholder="••••••••"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required
                        />
                        <button
                            type="button"
                            onClick={() => setShowPasswordConfirmation((value) => !value)}
                            className="absolute right-3.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-slate-500 transition hover:text-slate-200"
                            aria-label={showPasswordConfirmation ? 'Masquer la confirmation' : 'Afficher la confirmation'}
                        >
                            {showPasswordConfirmation ? <EyeOff className="h-[18px] w-[18px]" /> : <Eye className="h-[18px] w-[18px]" />}
                        </button>
                    </div>
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="group flex w-full items-center justify-center gap-2 rounded-2xl bg-[#FF6A00] px-4 py-3.5 text-sm font-bold text-[#080B14] transition hover:bg-[#ff7a1a] focus:outline-none focus:ring-2 focus:ring-[#FF6A00]/40 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {processing ? 'Création...' : 'Créer mon compte'}
                    {!processing && <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />}
                </button>
            </form>

            <p className="mt-6 text-center text-sm text-slate-400">
                Tu as déjà un compte ?{' '}
                <Link
                    href={route('login')}
                    className="font-semibold text-[#FF8A3D] transition hover:text-[#FF6A00]"
                >
                    Se connecter
                </Link>
            </p>
        </GuestLayout>
    );
}
