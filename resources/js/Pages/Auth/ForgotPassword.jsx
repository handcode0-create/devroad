import { Head, useForm } from '@inertiajs/react';
import AuthLayout, { AuthLink } from '@/Components/Auth/AuthLayout';
import AuthField from '@/Components/Auth/AuthField';
import AuthButton from '@/Components/Auth/AuthButton';
import AuthNotice from '@/Components/Auth/AuthNotice';
import { RouteArrival, RouteStop, RouteTrack } from '@/Components/Auth/RouteTrack';
import { isEmail, routeProgress } from '@/Components/Auth/progress';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const valid = isEmail(data.email);

    const submit = (event) => {
        event.preventDefault();

        post(route('password.email'));
    };

    return (
        <AuthLayout
            title="Mot de passe oublié"
            subtitle="Indique ton adresse e-mail. On t'envoie un lien pour en choisir un nouveau."
            footer={
                <>
                    Tu t'en souviens ? <AuthLink href={route('login')}>Se connecter</AuthLink>
                </>
            }
        >
            <Head title="Mot de passe oublié" />

            {status && <AuthNotice>{status}</AuthNotice>}

            <form onSubmit={submit} noValidate>
                <RouteTrack progress={routeProgress([valid])}>
                    <RouteStop state={errors.email ? 'error' : valid ? 'done' : 'idle'}>
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
                            autoFocus
                        />
                    </RouteStop>

                    <RouteArrival reached={valid}>
                        <AuthButton processing={processing} busyLabel="Envoi...">
                            Envoyer le lien
                        </AuthButton>
                    </RouteArrival>
                </RouteTrack>
            </form>
        </AuthLayout>
    );
}
