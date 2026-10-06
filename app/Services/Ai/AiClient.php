<?php

namespace App\Services\Ai;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Appel minimal aux API d'IA avec la clé de l'utilisateur (Anthropic, OpenAI, Google Gemini).
 * Les appels partent du serveur : la clé n'est jamais exposée au navigateur.
 */
class AiClient
{
    public const PROVIDERS = [
        'anthropic' => ['name' => 'Anthropic (Claude)', 'default_model' => 'claude-haiku-4-5', 'models' => ['claude-haiku-4-5', 'claude-sonnet-5-5'], 'keys_url' => 'https://console.anthropic.com/settings/keys'],
        'openai' => ['name' => 'OpenAI', 'default_model' => 'gpt-5-mini', 'models' => ['gpt-5-mini', 'gpt-5-nano', 'gpt-4.1-mini'], 'keys_url' => 'https://platform.openai.com/api-keys'],
        'gemini' => ['name' => 'Google Gemini', 'default_model' => 'gemini-2.5-flash', 'models' => ['gemini-2.5-flash', 'gemini-2.5-flash-lite'], 'keys_url' => 'https://aistudio.google.com/apikey'],
    ];

    /**
     * @throws AiException
     */
    public function complete(string $provider, string $apiKey, ?string $model, string $system, string $prompt, int $maxTokens = 1800, bool $allowEmpty = false): string
    {
        $model = $model ?: (self::PROVIDERS[$provider]['default_model'] ?? null);

        try {
            $response = match ($provider) {
                'anthropic' => Http::timeout(60)->withHeaders([
                    'x-api-key' => $apiKey,
                    'anthropic-version' => '2023-06-01',
                ])->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $prompt]],
                ]),
                'openai' => Http::timeout(60)->withToken($apiKey)->post('https://api.openai.com/v1/chat/completions', array_filter([
                    'model' => $model,
                    'messages' => [['role' => 'system', 'content' => $system], ['role' => 'user', 'content' => $prompt]],
                    // Les modèles de raisonnement comptent leur réflexion dans ce budget.
                    'max_completion_tokens' => $maxTokens * 3,
                    'reasoning_effort' => preg_match('/^(gpt-5|o\d)/', $model) ? 'low' : null,
                ])),
                'gemini' => Http::timeout(60)->withHeaders(['x-goog-api-key' => $apiKey])
                    ->post('https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model) . ':generateContent', [
                        'systemInstruction' => ['parts' => [['text' => $system]]],
                        'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['maxOutputTokens' => $maxTokens * 3],
                    ]),
                default => throw new AiException('Fournisseur d’IA inconnu.'),
            };
        } catch (ConnectionException) {
            throw new AiException('Impossible de joindre ' . $this->name($provider) . '. Vérifie ta connexion puis réessaie.');
        } catch (RequestException $exception) {
            // Certains intermédiaires (proxy) font lever l'erreur au lieu de renvoyer la réponse.
            $response = $exception->response;
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

    /** Message d'erreur compréhensible (jamais le corps brut, qui pourrait contenir des détails techniques). */
    private function explain(string $provider, Response $response, string $model): string
    {
        $name = $this->name($provider);
        $detail = strtolower((string) ($response->json('error.message') ?? $response->json('error.status') ?? ''));

        return match (true) {
            in_array($response->status(), [401, 403], true) || str_contains($detail, 'api key') => "Clé refusée par $name. Vérifie qu’elle est complète et toujours active.",
            $response->status() === 404 || str_contains($detail, 'model') => "Le modèle « $model » n’est pas disponible chez $name avec cette clé. Choisis-en un autre.",
            $response->status() === 429 || str_contains($detail, 'quota') || str_contains($detail, 'credit') => "Quota ou crédit épuisé chez $name. Vérifie ton compte, ou réessaie dans quelques minutes.",
            $response->status() === 400 && str_contains($detail, 'billing') => "$name demande d’activer la facturation sur ce compte.",
            $response->serverError() => "$name est momentanément indisponible. Réessaie dans un instant.",
            default => "$name a refusé la demande (erreur {$response->status()}).",
        };
    }

    public function name(string $provider): string
    {
        return self::PROVIDERS[$provider]['name'] ?? 'Le fournisseur d’IA';
    }
}
