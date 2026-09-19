import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Trash2 } from 'lucide-react';

import ConfirmModal from '@/Components/Ui/ConfirmModal';
import { inputClass, Field } from '@/Components/Ui/Field';

export default function DeleteUserForm({ className = '' }) {
    const [confirming, setConfirming] = useState(false);

    const { data, setData, delete: destroy, processing, reset, errors } = useForm({
        password: '',
    });

    function submit() {
        destroy(route('profile.destroy'), {
            preserveScroll: true,
            onFinish: () => {
                setConfirming(false);
                reset();
            },
        });
    }

    return (
        <section className={className}>
            <div className="rounded-2xl border border-red-500/15 bg-red-500/[0.04] p-5">
                <div className="flex items-start gap-3">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                        <Trash2 size={18} />
                    </div>

                    <div className="min-w-0">
                        <h2 className="text-base font-bold text-white">
                            Supprimer le compte
                        </h2>
                        <p className="mt-1 text-xs leading-5 text-slate-500">
                            Cette action supprime définitivement ton compte et ses données.
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    onClick={() => setConfirming(true)}
                    className="mt-5 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-2.5 text-sm font-bold text-red-300 transition hover:bg-red-500/15"
                >
                    Supprimer mon compte
                </button>
            </div>

            <ConfirmModal
                show={confirming}
                title="Supprimer définitivement ton compte ?"
                description="Cette action est irréversible. Saisis ton mot de passe pour confirmer."
                confirmLabel="Supprimer définitivement"
                onClose={() => {
                    setConfirming(false);
                    reset();
                }}
                onConfirm={submit}
                processing={processing}
            >
                <Field
                    label="Mot de passe"
                    htmlFor="delete_password"
                    error={errors.password}
                >
                    <input
                        id="delete_password"
                        type="password"
                        className={inputClass}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="current-password"
                    />
                </Field>
            </ConfirmModal>
        </section>
    );
}
