import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { AnimatePresence, m, softSpring } from '@/Components/Ui/Motion';
import { ICON, Svg, ui } from '@/Components/Ui/Design';

/**
 * Paramètres → Assistant IA. La clé est testée par le serveur avant d'être enregistrée,
 * stockée chiffrée, et n'est plus jamais renvoyée au navigateur (seuls ses 4 derniers caractères).
 */
export default function AiSettings({ settings }) {
    const providers = settings?.providers ?? [];
    const [editing, setEditing] = useState(!settings?.enabled);
    const [reveal, setReveal] = useState(false);
    const [confirmRemove, setConfirmRemove] = useState(false);
    const form = useForm({
        provider: settings?.provider ?? providers[0]?.key ?? 'anthropic',
        model: settings?.model ?? '',
        api_key: '',
    });

    const provider = providers.find((item) => item.key === form.data.provider) ?? providers[0];
    const sameProvider = settings?.enabled && settings.provider === form.data.provider;

    function chooseProvider(key) {
        form.setData((data) => ({ ...data, provider: key, model: '', api_key: '' }));
        form.clearErrors();
    }

    function submit(event) {
        event.preventDefault();
        form.put('/profile/ai', {
            preserveScroll: true,
            onSuccess: () => { form.reset('api_key'); setEditing(false); setReveal(false); },
        });
    }

    function remove() {
        router.delete('/profile/ai', {
            preserveScroll: true,
            onSuccess: () => { setConfirmRemove(false); setEditing(true); form.reset(); },
        });
    }

    const active = providers.find((item) => item.key === settings?.provider);

    return (
        <div className="mt-5 flex flex-col gap-5">
            <AnimatePresence initial={false} mode="wait">
                {settings?.enabled && !editing ? (
                    <m.div key="on" initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -6 }} transition={softSpring} className="flex flex-col gap-4 rounded-2xl border border-[var(--dr-border)] bg-[var(--dr-field)] p-4 sm:flex-row sm:items-center">
                        <span aria-hidden="true" className="flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] bg-[var(--dr-accent)] text-[var(--dr-ink)]"><Svg d={ICON.check} size={20} stroke={2.8} /></span>
                        <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                            <span className="text-[15px] font-semibold text-[var(--dr-text)]">Assistant activé · {active?.name}</span>
                            <span className="truncate text-sm text-[var(--dr-text-2)]">Modèle {settings.model} · clé ••••{settings.hint}</span>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <button type="button" onClick={() => setEditing(true)} className={ui.secondary + ' h-10'}>Modifier</button>
                            <button type="button" onClick={() => setConfirmRemove(true)} className={ui.danger + ' h-10'}>Supprimer la clé</button>
                        </div>
                    </m.div>
                ) : (
                    <m.form key="form" onSubmit={submit} initial={{ opacity: 0, y: 6 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: -6 }} transition={softSpring} className="flex flex-col gap-4" noValidate>
                        <fieldset className="m-0 flex flex-col gap-2 border-0 p-0">
                            <legend className="mb-2 text-sm font-semibold text-[var(--dr-text)]">Fournisseur</legend>
                            <div role="radiogroup" className="grid gap-2 sm:grid-cols-3">
                                {providers.map((item) => {
                                    const checked = form.data.provider === item.key;
                                    return (
                                        <button key={item.key} type="button" role="radio" aria-checked={checked} onClick={() => chooseProvider(item.key)} className={'relative flex flex-col items-start gap-0.5 rounded-2xl border-2 px-3.5 py-3 text-left transition-colors ' + ui.focus + ' ' + (checked ? 'border-[var(--dr-accent)] bg-[var(--dr-accent-soft)]' : 'border-[var(--dr-border)] bg-[var(--dr-surface)] hover:border-[var(--dr-border-2)]')}>
                                            <span className="text-[15px] font-semibold text-[var(--dr-text)]">{item.name}</span>
                                            <span className="text-xs text-[var(--dr-text-3)]">Par défaut : {item.default_model}</span>
                                        </button>
                                    );
                                })}
                            </div>
                        </fieldset>

                        <label className="flex flex-col gap-2">
                            <span className="flex flex-wrap items-baseline justify-between gap-2 text-sm font-semibold text-[var(--dr-text)]">
                                Clé d’API {provider?.name}
                                {provider?.keys_url && <a href={provider.keys_url} target="_blank" rel="noopener noreferrer" className="text-[13px] font-semibold text-[var(--dr-accent-text)] underline underline-offset-2">Obtenir une clé</a>}
                            </span>
                            <span className="relative flex">
                                <input
                                    data-dr-native
                                    type={reveal ? 'text' : 'password'}
                                    value={form.data.api_key}
                                    onChange={(event) => form.setData('api_key', event.target.value)}
                                    autoComplete="off"
                                    spellCheck={false}
                                    placeholder={sameProvider ? `Laisse vide pour garder la clé ••••${settings.hint}` : 'Colle ta clé ici'}
                                    aria-invalid={Boolean(form.errors.api_key)}
                                    aria-describedby="ai-key-help"
                                    className={ui.field + ' pr-12 font-[\'JetBrains_Mono\',ui-monospace,monospace] text-sm ' + (form.errors.api_key ? 'border-[var(--dr-danger)]' : '')}
                                />
                                <button type="button" onClick={() => setReveal((value) => !value)} aria-label={reveal ? 'Masquer la clé' : 'Afficher la clé'} className="absolute right-1.5 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-lg text-[var(--dr-text-3)] hover:text-[var(--dr-text)]">
                                    <Svg d={reveal ? ['M3 3l18 18', 'M10.6 10.6a2 2 0 002.8 2.8', 'M9.9 5.1A10 10 0 0122 12a13 13 0 01-2.1 3M6.1 6.1A13 13 0 002 12s3.6 7 10 7a9.7 9.7 0 004-.9'] : ['M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z', 'M12 9a3 3 0 100 6 3 3 0 000-6z']} size={17} />
                                </button>
                            </span>
                            {form.errors.api_key
                                ? <span role="alert" className="text-sm text-[var(--dr-danger)]">{form.errors.api_key}</span>
                                : <span id="ai-key-help" className="text-[13px] leading-5 text-[var(--dr-text-3)]">Testée avant d’être enregistrée, puis chiffrée. Elle ne sera plus jamais affichée en entier. Conseil : crée une clé réservée à DevRoad avec un plafond de dépense chez ton fournisseur, et supprime-la ici ou chez lui si tu veux la révoquer.</span>}
                        </label>

                        <label className="flex flex-col gap-2">
                            <span className="text-sm font-semibold text-[var(--dr-text)]">Modèle <span className="font-normal text-[var(--dr-text-3)]">(facultatif : laisse vide pour utiliser celui de ta clé)</span></span>
                            <input data-dr-native list={`ai-models-${provider?.key}`} value={form.data.model} onChange={(event) => form.setData('model', event.target.value)} placeholder="Automatique : choisi selon ta clé" className={ui.field + ' text-sm'} />
                            <datalist id={`ai-models-${provider?.key}`}>{provider?.models.map((model) => <option key={model} value={model} />)}</datalist>
                            {form.errors.model && <span role="alert" className="text-sm text-[var(--dr-danger)]">{form.errors.model}</span>}
                        </label>

                        <div className="flex flex-wrap items-center gap-2">
                            <button type="submit" disabled={form.processing} aria-busy={form.processing} className={ui.primary}>
                                {form.processing && <span aria-hidden="true" className="h-4 w-4 animate-spin rounded-full border-2 border-[var(--dr-ink)] border-t-transparent" />}
                                {form.processing ? 'Test de la clé…' : 'Tester et enregistrer'}
                            </button>
                            {settings?.enabled && <button type="button" onClick={() => { setEditing(false); form.reset(); form.clearErrors(); }} className={ui.ghost}>Annuler</button>}
                        </div>
                    </m.form>
                )}
            </AnimatePresence>

            <AnimatePresence>
                {confirmRemove && (
                    <m.div role="alertdialog" aria-labelledby="ai-remove-title" initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} exit={{ opacity: 0, height: 0 }} className="overflow-hidden">
                        <div className="flex flex-col gap-3 rounded-2xl border border-[var(--dr-border-2)] p-4 sm:flex-row sm:items-center">
                            <span id="ai-remove-title" className="flex-1 text-sm text-[var(--dr-text)]">Supprimer la clé ? Il faudra la coller à nouveau pour réactiver l’assistant.</span>
                            <div className="flex gap-2">
                                <button type="button" onClick={remove} className={ui.primary + ' h-10 bg-[var(--dr-danger)]'}>Supprimer</button>
                                <button type="button" onClick={() => setConfirmRemove(false)} className={ui.ghost + ' h-10'}>Garder</button>
                            </div>
                        </div>
                    </m.div>
                )}
            </AnimatePresence>

            <ul className="m-0 grid list-none gap-2 p-0 text-[13px] leading-5 text-[var(--dr-text-2)] sm:grid-cols-3">
                <li className="rounded-[14px] bg-[var(--dr-field)] p-3"><strong className="block text-[var(--dr-text)]">Ton compte, ta facture</strong>Les questions sont facturées par ton fournisseur selon son tarif (certains ont un quota gratuit).</li>
                <li className="rounded-[14px] bg-[var(--dr-field)] p-3"><strong className="block text-[var(--dr-text)]">Ce qui est envoyé</strong>Ta question et les extraits de documentation trouvés. Jamais tes fiches ni tes données de compte.</li>
                <li className="rounded-[14px] bg-[var(--dr-field)] p-3"><strong className="block text-[var(--dr-text)]">Sans clé</strong>La recherche classique de la documentation reste gratuite et complète.</li>
            </ul>
        </div>
    );
}
