import { Head, useForm } from '@inertiajs/react';
import AuthLayout, { AuthLink } from '@/Components/Auth/AuthLayout';
import AuthField from '@/Components/Auth/AuthField';
import AuthButton from '@/Components/Auth/AuthButton';
import { RouteArrival, RouteStop, RouteTrack } from '@/Components/Auth/RouteTrack';
import { isEmail, isName, isPassword, isSame, routeProgress } from '@/Components/Auth/progress';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const done = {
        name: isName(data.name),
        email: isEmail(data.email),
        password: isPassword(data.password),
        password_confirmation: isSame(data.password, data.password_confirmation),
    };
    const progress = routeProgress(Object.values(done));
    const stateOf = (field) => (errors[field] ? 'error' : done[field] ? 'done' : 'idle');

    const submit = (event) => {
        event.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout
            swipeStep={2}
            title="Commence ton parcours"
            subtitle="Crée ton compte pour planifier ce que tu veux apprendre."
            footer={
                <>
                    Déjà un compte ? <AuthLink href={route('login')}>Se connecter</AuthLink>
                </>
            }
        >
            <Head title="Créer un compte" />

            <form onSubmit={submit} noValidate>
                <RouteTrack progress={progress}>
                    <RouteStop state={stateOf('name')}>
                        <AuthField
                            id="name"
                            label="Nom complet"
                            value={data.name}
                            onChange={(value) => setData('name', value)}
                            error={errors.name}
                            autoComplete="name"
                            autoFocus
                        />
                    </RouteStop>

                    <RouteStop state={stateOf('email')}>
                        <AuthField
                            id="email"
                            label="Adresse e-mail"
                            type="email"
                            value={data.email}
                            onChange={(value) => setData('email', value)}
                            error={errors.email}
                            autoComplete="email"
                            inputMode="email"
                            placeholder="toi@exemple.com"
                        />
                    </RouteStop>

                    <RouteStop state={stateOf('password')}>
                        <AuthField
                            id="password"
                            label="Mot de passe"
                            type="password"
                            value={data.password}
                            onChange={(value) => setData('password', value)}
                            error={errors.password}
                            hint="8 caractères minimum."
                            autoComplete="new-password"
                        />
                    </RouteStop>

                    <RouteStop state={stateOf('password_confirmation')}>
                        <AuthField
                            id="password_confirmation"
                            label="Confirmer le mot de passe"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(value) => setData('password_confirmation', value)}
                            error={errors.password_confirmation}
                            autoComplete="new-password"
                        />
                    </RouteStop>

                    <RouteArrival reached={progress === 1}>
                        <AuthButton processing={processing} busyLabel="Création du compte...">
                            Créer mon compte
                        </AuthButton>
                    </RouteArrival>
                </RouteTrack>
            </form>
        </AuthLayout>
    );
}
