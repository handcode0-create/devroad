<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Appel minimal aux API d'IA avec la clé de l'utilisateur (Anthropic, OpenAI, Google Gemini).
 * Les appels partent du serveur : la clé n'est jamais exposée au navigateur.
 */
class AiClient
{
    public const PROVIDERS = [
        'anthropic' => ['name' => 'Anthropic (Claude)', 'default_model' => 'claude-haiku-4-5', 'models' => ['claude-haiku-4-5', 'claude-sonnet-5-5'], 'keys_url' => 'https://console.anthropic.com/settings/keys'],
        'openai' => ['name' => 'OpenAI', 'default_model' => 'gpt-5-mini', 'models' => ['gpt-5-mini', 'gpt-5-nano', 'gpt-4.1-mini'], 'keys_url' => 'https://platform.openai.com/api-keys'],
        'gemini' => ['name' => 'Google Gemini', 'default_model' => 'gemini-3.5-flash-lite', 'models' => ['gemini-3.5-flash-lite', 'gemini-3.8-flash', 'gemini-3.6-flash', 'gemini-3.1-flash-lite'], 'keys_url' => 'https://aistudio.google.com/apikey'],
    ];

    /** Modèle réellement utilisé lors du dernier appel réussi quand un repli a eu lieu. */
    public ?string $lastModel = null;

    /**
     * @throws AiException
     */
    public function complete(string $provider, string $apiKey, ?string $model, string $system, string $prompt, int $maxTokens = 1800, bool $allowEmpty = false, bool $allowFallback = true): string
    {
        $model = $model ?: (self::PROVIDERS[$provider]['default_model'] ?? null);

        try {
            $response = match ($provider) {
                'anthropic' => $this->http()->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]),
                'openai' => $this->http()->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', array_filter([
                    'model' => $model,
                    'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $prompt]],
                    // Les modèles de raisonnement comptent leur réflexion dans ce budget.
                    'max_completion_tokens' => $maxTokens * 3,
                    'reasoning_effort' => preg_match('/^(gpt-5|o\d)/', $model) ? 'low' : null,
                ])),
                'gemini' => $this->http()->withHeaders(['x-goog-api-key' => $apiKey])
                    ->post('https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent', [
                        'systemInstruction' => ['parts' => [['text' => $system]]],
                        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['maxOutputTokens' => $maxTokens * 3],
                    ]),
                default => throw new AiException('Fournisseur d’IA inconnu.'),
            };
        } catch (ConnectionException $exception) {
            throw new AiException($this->explainConnection($provider, $exception));
        } catch (RequestException $exception) {
            // Certains intermédiaires (proxy) font lever l'erreur au lieu de renvoyer la réponse.
            $response = $exception->response;
        }

        // Gemini : si le modèle demandé n'existe pas pour cette clé, on retombe sur un modèle
        // réellement proposé par le compte (liste officielle des modèles de la clé).
        if ($response->failed() && $provider === 'gemini' && $response->status() === 404 && $allowFallback) {
            foreach ($this->geminiFallbackModels($apiKey, $model) as $alternative) {
                try {
                    $text = $this->complete($provider, $apiKey, $alternative, $system, $prompt, $maxTokens, $allowEmpty, false);
                } catch (AiException) {
                    continue;
                }

                $this->lastModel = $alternative;

                return $text;
            }
        }

        if ($response->failed()) {
            throw new AiException($this->explain($provider, $response, $model));
        }

        $text = match ($provider) {
            'anthropic' => collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode(''),
            'openai' => (string) $response->json('choices.0.message.content', ''),
            'gemini' => collect($response->json('candidates.0.content.parts', []))->pluck('text')->implode(''),
        };

        if (trim($text) === '' && ! $allowEmpty) {
            throw new AiException($this->name($provider) . ' a renvoyé une réponse vide. Réessaie, ou choisis un autre modèle.');
        }

        return trim($text);
    }

    /**
     * Choisit automatiquement le modèle à utiliser d'après la liste réellement proposée par la clé.
     * Retourne le modèle par défaut s'il est disponible, sinon le meilleur équivalent ;
     * null si la liste est inaccessible (l'appelant garde alors le modèle par défaut).
     */
    public function resolveModel(string $provider, string $apiKey): ?string
    {
        $default = self::PROVIDERS[$provider]['default_model'] ?? null;

        return Cache::remember('ai.model.'.$provider.'.'.hash('sha256', $apiKey), 3600, function () use ($provider, $apiKey, $default) {
            $models = $this->availableModels($provider, $apiKey);

            if ($models === null || $models === []) {
                return null;
            }

            if ($default !== null && in_array($default, $models, true)) {
                return $default;
            }

            $candidates = match ($provider) {
                'gemini' => collect($models)
                    ->filter(fn ($name) => preg_match('/^gemini-[\d.]+-flash(-lite)?$/', $name) === 1)
                    ->sortByDesc(fn ($name) => (float) preg_replace('/^gemini-([\d.]+).*/', '$1', $name) * 10 + (str_contains($name, 'lite') ? 0 : 1)),
                'openai' => collect($models)
                    ->filter(fn ($name) => preg_match('/^(gpt-5-mini|gpt-4\.1-mini|gpt-4o-mini|gpt-5-nano)$/', $name) === 1),
                'anthropic' => collect($models)
                    ->filter(fn ($name) => str_starts_with($name, 'claude-') && str_contains($name, 'haiku'))
                    ->sortDesc(),
                default => collect(),
            };

            return $candidates->values()->first() ?? $models[0];
        });
    }

    /** Modèles de texte accessibles avec cette clé (null si le fournisseur ne répond pas). */
    private function availableModels(string $provider, string $apiKey): ?array
    {
        try {
            $response = match ($provider) {
                'gemini' => $this->http()->withHeaders(['x-goog-api-key' => $apiKey])
                    ->get('https://generativelanguage.googleapis.com/v1beta/models', ['pageSize' => 200]),
                'openai' => $this->http()->withToken($apiKey)->get('https://api.openai.com/v1/models'),
                'anthropic' => $this->http()->withHeaders(['x-api-key' => $apiKey, 'anthropic-version' => '2023-06-01'])
                    ->get('https://api.anthropic.com/v1/models', ['limit' => 100]),
                default => null,
            };
        } catch (\Throwable) {
            return null;
        }

        if ($response === null || $response->failed()) {
            return null;
        }

        return match ($provider) {
            'gemini' => collect($response->json('models', []))
                ->filter(fn ($model) => in_array('generateContent', $model['supportedGenerationMethods'] ?? [], true))
                ->map(fn ($model) => preg_replace('#^models/#', '', (string) ($model['name'] ?? '')))
                ->filter(fn ($name) => str_starts_with($name, 'gemini-') && ! preg_match('/(embedding|tts|image|live|audio|exp|preview)/', $name))
                ->values()->all(),
            'openai', 'anthropic' => collect($response->json('data', []))->pluck('id')->filter()->values()->all(),
            default => null,
        };
    }

    /**
     * Modèles Gemini actuels recommandés par Google pour les nouveaux projets (les séries 2.x sont
     * réservées aux comptes qui les utilisaient déjà ou arrêtées : gemini-2.0-flash est fermé).
     */
    private const GEMINI_CURRENT = ['gemini-3.5-flash-lite', 'gemini-3.8-flash', 'gemini-3.6-flash', 'gemini-3.1-flash-lite'];

    /** Modèles Gemini à essayer, dans l'ordre, quand celui demandé est introuvable pour cette clé. */
    private function geminiFallbackModels(string $apiKey, string $failedModel): array
    {
        $listed = $this->resolveModel('gemini', $apiKey);

        return collect([$listed, ...self::GEMINI_CURRENT])
            ->filter(fn ($model) => $model && $model !== $failedModel)
            ->unique()
            ->values()
            ->all();
    }

    /** Client HTTP commun : délai, et bundle de certificats optionnel (AI_CA_BUNDLE) pour les PHP locaux mal configurés. */
    private function http(): PendingRequest
    {
        $request = Http::timeout(60);
        $bundle = config('devroad.ai_ca_bundle');

        if (is_string($bundle) && $bundle !== '' && is_file($bundle)) {
            $request = $request->withOptions(['verify' => $bundle]);
        }

        return $request;
    }

    /**
     * Diagnostic d'un échec de connexion : la cause réelle (certificat, DNS, délai…) est
     * consignée dans les logs et résumée en une action concrète pour l'utilisateur.
     */
    private function explainConnection(string $provider, ConnectionException $exception): string
    {
        $name = $this->name($provider);
        $detail = $exception->getMessage();

        Log::warning('Connexion au fournisseur d’IA impossible', ['provider' => $provider, 'message' => Str::limit($detail, 300)]);

        return match (true) {
            str_contains($detail, 'cURL error 60') || str_contains($detail, 'cURL error 77') || stripos($detail, 'SSL certificate') !== false
                => "Ton PHP local ne reconnaît pas le certificat de $name (erreur SSL). Télécharge https://curl.se/ca/cacert.pem, enregistre-le (ex. C:\\xampp\\php\\extras\\ssl\\cacert.pem), puis renseigne-le dans php.ini (curl.cainfo et openssl.cafile) ou dans le fichier .env avec AI_CA_BUNDLE=chemin\\cacert.pem, et redémarre le serveur.",
            str_contains($detail, 'cURL error 6') || stripos($detail, 'resolve host') !== false
                => "Ton ordinateur ne parvient pas à résoudre l'adresse de $name (DNS). Vérifie ta connexion Internet ou ton VPN, puis réessaie.",
            str_contains($detail, 'cURL error 28') || stripos($detail, 'timed out') !== false
                => "$name met trop de temps à répondre. Vérifie ta connexion (ou ton pare-feu / antivirus), puis réessaie.",
            default => 'Impossible de joindre '.$name.'. Vérifie ta connexion puis réessaie. Détail technique : '.Str::limit($detail, 140),
        };
    }

    /** Message d'erreur compréhensible (jamais le corps brut, qui pourrait contenir des détails techniques). */
    /**
     * Message d'erreur compréhensible et actionnable. La réponse brute du fournisseur
     * est consignée dans les logs (jamais la clé), et citée seulement quand le cas est inconnu.
     */
    private function explain(string $provider, Response $response, string $model): string
    {
        $name = $this->name($provider);
        $status = $response->status();
        $message = (string) ($response->json('error.message') ?? $response->json('message') ?? '');
        $code = strtolower((string) ($response->json('error.code') ?? $response->json('error.type') ?? $response->json('error.status') ?? ''));
        $detail = strtolower($message);

        Log::warning('Erreur du fournisseur d’IA', ['provider' => $provider, 'model' => $model, 'status' => $status, 'code' => $code, 'message' => Str::limit($message, 300)]);

        $noCredit = str_contains($detail, 'credit balance') || str_contains($detail, 'insufficient credit') || $code === 'insufficient_quota'
            || str_contains($detail, 'billing') || str_contains($detail, 'exceeded your current quota') || str_contains($detail, 'purchase credits');
        $badKey = in_array($status, [401, 403], true) || in_array($code, ['authentication_error', 'invalid_api_key', 'permission_denied', 'permission_error'], true)
            || str_contains($detail, 'api key not valid') || str_contains($detail, 'invalid x-api-key') || str_contains($detail, 'incorrect api key') || str_contains($detail, 'invalid api key');
        $badModel = $status === 404 || $code === 'model_not_found' || $code === 'not_found_error'
            || (str_contains($detail, 'model') && (str_contains($detail, 'not found') || str_contains($detail, 'does not exist') || str_contains($detail, 'not supported') || str_contains($detail, 'invalid model')));
        $freeTierExhausted = $provider === 'gemini' && ($status === 429 || $code === 'resource_exhausted');

        return match (true) {
            $noCredit && $provider === 'anthropic' => 'Ton compte Anthropic n’a pas de crédit disponible. Ajoute du crédit dans la console Anthropic (Billing), ou choisis Google Gemini qui propose un quota gratuit.',
            $noCredit && $provider === 'openai' => 'Ton compte OpenAI n’a pas de crédit disponible. Ajoute un moyen de paiement ou du crédit sur platform.openai.com (Billing), ou choisis Google Gemini qui propose un quota gratuit.',
            $noCredit => "$name demande d’activer la facturation sur ce compte.",
            $badKey => "Clé refusée par $name. Vérifie qu’elle est complète, qu’elle vient bien de $name et qu’elle est toujours active.",
            $badModel => "Le modèle « $model » n’est pas disponible chez $name avec cette clé. Laisse le champ Modèle vide pour utiliser celui par défaut.",
            $freeTierExhausted => 'Quota gratuit de Google Gemini atteint pour le moment. Réessaie un peu plus tard (il se renouvelle chaque jour).',
            $status === 429 => "Trop de demandes envoyées à $name en peu de temps. Réessaie dans une minute.",
            $status === 529 || $response->serverError() => "$name est momentanément surchargé ou indisponible. Réessaie dans un instant.",
            default => "$name a refusé la demande (erreur $status)" . ($message !== '' ? ' : « ' . Str::limit($message, 160) . ' »' : '.'),
        };
    }

    public function name(string $provider): string
    {
        return self::PROVIDERS[$provider]['name'] ?? 'Le fournisseur d’IA';
    }
}
