import { Loader2 } from 'lucide-react';
import { buttonClass } from '@/Components/Ui/buttons';

// Bouton d'envoi des écrans d'authentification : variante « primary » du design
// système, en pleine largeur et à 48 px de haut. Le libellé change pendant l'envoi.
export default function AuthButton({ processing, children, busyLabel }) {
    return (
        <button
            type="submit"
            disabled={processing}
            aria-busy={processing}
            className={`${buttonClass('primary')} h-12 w-full text-base focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#FF6A00]/70 focus-visible:ring-offset-2 focus-visible:ring-offset-[#08111F]`}
        >
            {processing ? (
                <>
                    <Loader2 size={18} className="animate-spin motion-reduce:animate-none" aria-hidden="true" />
                    {busyLabel}
                </>
            ) : (
                children
            )}
        </button>
    );
}
