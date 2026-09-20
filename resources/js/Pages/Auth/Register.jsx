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
            title="Rejoignez DevRoad"
            description="Commencez dès maintenant. Créez votre espace et construisez votre progression."
        >
            <Head title="Créer un compte" />

            <form onSubmit={submit} className="space-y-3.5">
                <div>
                    <InputLabel htmlFor="name" value="Nom" className="sr-only" />
                    <div className="relative">
                        <UserRound className="pointer-events-none absolute left-4 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-white/35" />
                        <TextInput
                            id="name"
                            name="name"
                            value={data.name}
                            className="block w-full rounded-2xl border border-white/[0.15] bg-white/[0.035] py-3.5 pl-11 pr-4 text-sm text-white placeholder:text-white/35 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="name"
                            isFocused={true}
                            placeholder="Nom complet"
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                    </div>
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Adresse e-mail" className="sr-only" />
                    <div className="relative">
                        <Mail className="pointer-events-none absolute left-4 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-white/35" />
                        <TextInput
                            id="email"
                            type="email"
                            name="email"
                            value={data.email}
                            className="block w-full rounded-2xl border border-white/[0.15] bg-white/[0.035] py-3.5 pl-11 pr-4 text-sm text-white placeholder:text-white/35 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="username"
                            placeholder="Adresse e-mail"
                            onChange={(e) => setData('email', e.target.value)}
                            required
                        />
                    </div>
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Mot de passe" className="sr-only" />
                    <div className="relative">
                        <LockKeyhole className="pointer-events-none absolute left-4 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-white/35" />
                        <TextInput
                            id="password"
                            type={showPassword ? 'text' : 'password'}
                            name="password"
                            value={data.password}
                            className="block w-full rounded-2xl border border-white/[0.15] bg-white/[0.035] py-3.5 pl-11 pr-12 text-sm text-white placeholder:text-white/35 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="new-password"
                            placeholder="Mot de passe"
                            onChange={(e) => setData('password', e.target.value)}
                            required
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

                <div>
                    <InputLabel htmlFor="password_confirmation" value="Confirmer le mot de passe" className="sr-only" />
                    <div className="relative">
                        <LockKeyhole className="pointer-events-none absolute left-4 top-1/2 h-[17px] w-[17px] -translate-y-1/2 text-white/35" />
                        <TextInput
                            id="password_confirmation"
                            type={showPasswordConfirmation ? 'text' : 'password'}
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="block w-full rounded-2xl border border-white/[0.15] bg-white/[0.035] py-3.5 pl-11 pr-12 text-sm text-white placeholder:text-white/35 focus:border-[#FF6A00] focus:ring-[#FF6A00]/20"
                            autoComplete="new-password"
                            placeholder="Confirmer le mot de passe"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required
                        />
                        <button
                            type="button"
                            onClick={() => setShowPasswordConfirmation((value) => !value)}
                            className="absolute right-3.5 top-1/2 -translate-y-1/2 rounded-lg p-1 text-white/35 transition hover:text-white"
                            aria-label={showPasswordConfirmation ? 'Masquer la confirmation' : 'Afficher la confirmation'}
                        >
                            {showPasswordConfirmation ? <EyeOff className="h-[17px] w-[17px]" /> : <Eye className="h-[17px] w-[17px]" />}
                        </button>
                    </div>
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="group mt-1 flex w-full items-center justify-center gap-3 rounded-2xl bg-[#FF6A00] px-4 py-3.5 text-sm font-bold text-white shadow-[0_10px_35px_rgba(255,106,0,.22)] transition hover:bg-[#ff7a1a] focus:outline-none focus:ring-2 focus:ring-[#FF6A00]/40 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    {processing ? 'Création...' : 'Créer un compte'}
                    {!processing && <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />}
                </button>
            </form>

            <div className="my-5 flex items-center gap-3">
                <div className="h-px flex-1 bg-white/[0.09]" />
                <span className="text-[10px] font-medium uppercase tracking-[0.16em] text-white/30">Ou</span>
                <div className="h-px flex-1 bg-white/[0.09]" />
            </div>

            <p className="text-center text-sm text-white/45">
                Vous avez déjà un compte ?{' '}
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
