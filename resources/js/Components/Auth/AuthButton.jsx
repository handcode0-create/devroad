import LoadingSpinner from '@/Components/Ui/LoadingSpinner';
import { buttonClass } from '@/Components/Ui/buttons';

// Bouton d'envoi des écrans d'authentification : variante « primary » du design
// système, en pleine largeur et à 48 px de haut. Le libellé change pendant l'envoi.
export default function AuthButton({ processing, children, busyLabel, disabled = false }) {
    return (
        <button
            type="submit"
            disabled={processing || disabled}
            aria-busy={processing}
            className={`${buttonClass('primary')} h-12 w-full text-base focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/70 focus-visible:ring-offset-2 focus-visible:ring-offset-[#08111F] disabled:cursor-not-allowed disabled:opacity-40`}
        >
            {processing ? (
                <>
                    <LoadingSpinner size={18} label={busyLabel} />
                </>
            ) : (
                children
            )}
        </button>
    );
}
