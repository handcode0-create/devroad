import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '@/Components/Auth/AuthLayout';
import AuthField from '@/Components/Auth/AuthField';
import AuthButton from '@/Components/Auth/AuthButton';
import { RouteArrival, RouteStop, RouteTrack } from '@/Components/Auth/RouteTrack';
import { isEmail, isPassword, isSame, routeProgress } from '@/Components/Auth/progress';

export default function ResetPassword({ token, email }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const done = {
        email: isEmail(data.email),
        password: isPassword(data.password),
        password_confirmation: isSame(data.password, data.password_confirmation),
    };
    const progress = routeProgress(Object.values(done));
    const stateOf = (field) => (errors[field] ? 'error' : done[field] ? 'done' : 'idle');

    const submit = (event) => {
        event.preventDefault();

        post(route('password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Choisis un nouveau mot de passe" subtitle="Tu pourras te reconnecter juste après.">
            <Head title="Nouveau mot de passe" />

            <form onSubmit={submit} noValidate>
                <RouteTrack progress={progress}>
                    <RouteStop state={stateOf('email')}>
                        <AuthField
                            id="email"
                            label="Adresse e-mail"
                            type="email"
                            value={data.email}
                            onChange={(value) => setData('email', value)}
                            error={errors.email}
                            autoComplete="username"
                            inputMode="email"
                        />
                    </RouteStop>

                    <RouteStop state={stateOf('password')}>
                        <AuthField
                            id="password"
                            label="Nouveau mot de passe"
                            type="password"
                            value={data.password}
                            onChange={(value) => setData('password', value)}
                            error={errors.password}
                            hint="8 caractères minimum."
                            autoComplete="new-password"
                            autoFocus
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
                        <AuthButton processing={processing} busyLabel="Enregistrement...">
                            Enregistrer le mot de passe
                        </AuthButton>
                    </RouteArrival>
                </RouteTrack>
            </form>
        </AuthLayout>
    );
}
