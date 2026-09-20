import { Head, useForm } from '@inertiajs/react';
import AuthLayout, { AuthLink } from '@/Components/Auth/AuthLayout';
import AuthField from '@/Components/Auth/AuthField';
import AuthButton from '@/Components/Auth/AuthButton';
import AuthNotice from '@/Components/Auth/AuthNotice';
import { RouteArrival, RouteAside, RouteStop, RouteTrack } from '@/Components/Auth/RouteTrack';
import { isEmail, routeProgress } from '@/Components/Auth/progress';

// TODO : passer à true quand un service d'e-mail est configuré en production
// (sans lui, le lien de réinitialisation ne peut pas être envoyé).
const SHOW_PASSWORD_RESET = false;

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const progress = routeProgress([isEmail(data.email), data.password.length > 0]);

    const submit = (event) => {
        event.preventDefault();

        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            swipeStep={1}
            title="Reprends ton parcours"
            subtitle="Retrouve ta roadmap là où tu l'as laissée."
            footer={
                <>
                    Pas encore de compte ? <AuthLink href={route('register')}>Créer un compte</AuthLink>
                </>
            }
        >
            <Head title="Se connecter" />

            {status && <AuthNotice>{status}</AuthNotice>}

            <form onSubmit={submit} noValidate>
                <RouteTrack progress={progress}>
                    <RouteStop state={errors.email ? 'error' : isEmail(data.email) ? 'done' : 'idle'}>
                        <AuthField
                            id="email"
                            label="Adresse e-mail"
                            type="email"
                            value={data.email}
                            onChange={(value) => setData('email', value)}
                            error={errors.email}
                            autoComplete="username"
                            inputMode="email"
                            placeholder="toi@exemple.com"
                            autoFocus
                        />
                    </RouteStop>

                    <RouteStop state={errors.password ? 'error' : data.password ? 'done' : 'idle'}>
                        <AuthField
                            id="password"
                            label="Mot de passe"
                            type="password"
                            value={data.password}
                            onChange={(value) => setData('password', value)}
                            error={errors.password}
                            autoComplete="current-password"
                        />
                    </RouteStop>

                    <RouteAside>
                        <div className="flex flex-wrap items-center justify-between gap-x-4">
                            <label className="flex min-h-[44px] cursor-pointer items-center gap-3 text-sm text-slate-300">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    checked={data.remember}
                                    onChange={(event) => setData('remember', event.target.checked)}
                                    className="h-5 w-5 rounded-md border-[#5A6A82] bg-[#101A2A] text-[#FF6A00] focus:ring-2 focus:ring-[#FF6A00]/40 focus:ring-offset-0"
                                />
                                Se souvenir de moi
                            </label>

                            {SHOW_PASSWORD_RESET && canResetPassword && (
                                <AuthLink href={route('password.request')}>Mot de passe oublié ?</AuthLink>
                            )}
                        </div>
                    </RouteAside>

                    <RouteArrival reached={progress === 1}>
                        <AuthButton processing={processing} busyLabel="Connexion...">
                            Se connecter
                        </AuthButton>
                    </RouteArrival>
                </RouteTrack>
            </form>
        </AuthLayout>
    );
}
