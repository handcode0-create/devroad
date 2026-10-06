<?php

namespace App\Http\Controllers;

use App\Services\Ai\AiClient;
use App\Services\Ai\AiException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** Paramètres → Assistant IA : l'utilisateur apporte sa clé, testée avant d'être enregistrée. */
class AiSettingsController extends Controller
{
    public function update(Request $request, AiClient $ai): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(AiClient::PROVIDERS))],
            'model' => ['nullable', 'string', 'max:80', 'regex:/^[\w.\-:\/]+$/'],
            // Vide = on garde la clé déjà enregistrée (changement de modèle seulement).
            'api_key' => ['nullable', 'string', 'min:10', 'max:300'],
        ], [
            'model.regex' => 'Nom de modèle invalide.',
            'api_key.min' => 'Cette clé semble incomplète.',
        ]);

        $key = trim((string) ($data['api_key'] ?? ''));
        if ($key === '' && (! $user->ai_api_key || $user->ai_provider !== $data['provider'])) {
            throw ValidationException::withMessages(['api_key' => 'Colle ta clé d’API pour ce fournisseur.']);
        }
        $key = $key !== '' ? $key : $user->ai_api_key;
        $model = ($data['model'] ?? null) ?: AiClient::PROVIDERS[$data['provider']]['default_model'];

        // Vérification réelle (très courte) avant d'enregistrer.
        try {
            $ai->complete($data['provider'], $key, $model, 'Réponds uniquement par OK.', 'Test de connexion DevRoad.', 16, allowEmpty: true);
        } catch (AiException $exception) {
            throw ValidationException::withMessages(['api_key' => $exception->getMessage()]);
        }

        $user->forceFill([
            'ai_provider' => $data['provider'],
            'ai_model' => $model,
            'ai_api_key' => $key,
            'ai_key_hint' => substr($key, -4),
        ])->save();

        return back()->with('success', 'Assistant IA activé avec ' . $ai->name($data['provider']) . '.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['ai_provider' => null, 'ai_model' => null, 'ai_api_key' => null, 'ai_key_hint' => null])->save();

        return back()->with('success', 'Clé supprimée : l’assistant IA est désactivé.');
    }
}
