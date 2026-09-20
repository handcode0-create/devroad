import { Head, useForm } from '@inertiajs/react';
import AuthLayout from '@/Components/Auth/AuthLayout';
import AuthField from '@/Components/Auth/AuthField';
import AuthButton from '@/Components/Auth/AuthButton';
import { RouteArrival, RouteStop, RouteTrack } from '@/Components/Auth/RouteTrack';
import { routeProgress } from '@/Components/Auth/progress';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
    });

    const filled = data.password.length > 0;

    const submit = (event) => {
        event.preventDefault();

        post(route('password.confirm'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <AuthLayout
            title="Confirme ton mot de passe"
            subtitle="Cette zone est protégée. Saisis ton mot de passe pour continuer."
        >
            <Head title="Confirmer le mot de passe" />

            <form onSubmit={submit} noValidate>
                <RouteTrack progress={routeProgress([filled])}>
                    <RouteStop state={errors.password ? 'error' : filled ? 'done' : 'idle'}>
                        <AuthField
                            id="password"
                            label="Mot de passe"
                            type="password"
                            value={data.password}
                            onChange={(value) => setData('password', value)}
                            error={errors.password}
                            autoComplete="current-password"
                            autoFocus
                        />
                    </RouteStop>

                    <RouteArrival reached={filled}>
                        <AuthButton processing={processing} busyLabel="Vérification...">
                            Confirmer
                        </AuthButton>
                    </RouteArrival>
                </RouteTrack>
            </form>
        </AuthLayout>
    );
}
