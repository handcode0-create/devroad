import { createContext, useCallback, useContext, useRef, useState } from 'react';
import ConfirmModal from './ConfirmModal';

const ConfirmContext = createContext(null);

/**
 * Une seule modale de confirmation pour toute l'application, utilisable comme window.confirm :
 *   const confirm = useConfirm();
 *   if (!(await confirm({ title: 'Supprimer ?', description: '…', confirmLabel: 'Supprimer' }))) return;
 */
export function ConfirmProvider({ children }) {
    const [options, setOptions] = useState({});
    const [show, setShow] = useState(false);
    const resolver = useRef(null);

    const confirm = useCallback((next) => new Promise((resolve) => {
        resolver.current?.(false);
        resolver.current = resolve;
        setOptions(next);
        setShow(true);
    }), []);

    const settle = (value) => {
        resolver.current?.(value);
        resolver.current = null;
        setShow(false);
    };

    return (
        <ConfirmContext.Provider value={confirm}>
            {children}
            <ConfirmModal
                show={show}
                title={options.title}
                description={options.description}
                confirmLabel={options.confirmLabel}
                cancelLabel={options.cancelLabel}
                tone={options.tone}
                onConfirm={() => settle(true)}
                onClose={() => settle(false)}
            />
        </ConfirmContext.Provider>
    );
}

export function useConfirm() {
    const confirm = useContext(ConfirmContext);

    // Sans fournisseur (tests, page isolée) : on retombe sur la boîte du navigateur plutôt que d'échouer.
    return confirm ?? ((options) => Promise.resolve(window.confirm(options.title)));
}
