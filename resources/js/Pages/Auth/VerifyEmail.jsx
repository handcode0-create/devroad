import { Head, Link, useForm } from '@inertiajs/react';
import AuthLayout from '@/Components/Auth/AuthLayout';
import AuthButton from '@/Components/Auth/AuthButton';
import AuthNotice from '@/Components/Auth/AuthNotice';

export default function VerifyEmail({ status }) {
    const { post, processing } = useForm({});

    const submit = (event) => {
        event.preventDefault();

        post(route('verification.send'));
    };

    return (
        <AuthLayout
            title="Vérifie ton adresse e-mail"
            subtitle="On vient de t'envoyer un lien de confirmation. Clique dessus pour commencer. Rien reçu ? On peut te le renvoyer."
        >
            <Head title="Vérifier l'adresse e-mail" />

            {status === 'verification-link-sent' && (
                <AuthNotice>Un nouveau lien vient d'être envoyé à l'adresse indiquée à l'inscription.</AuthNotice>
            )}

            <form onSubmit={submit}>
                <AuthButton processing={processing} busyLabel="Envoi...">
                    Renvoyer l'e-mail
                </AuthButton>
            </form>

            <Link
                href={route('logout')}
                method="post"
                as="button"
                className="mt-5 min-h-[44px] rounded-lg text-sm font-semibold text-[#FF6A00] underline-offset-4 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/60"
            >
                Se déconnecter
            </Link>
        </AuthLayout>
    );
}
