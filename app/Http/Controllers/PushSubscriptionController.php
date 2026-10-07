<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:2000'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'in:aesgcm,aes128gcm'],
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $data['endpoint'])],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $data['endpoint'],
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aesgcm',
            ],
        );

        return response()->json(['ok' => true]);
    }

    /** Notification immédiate pour vérifier que l'appareil reçoit bien les rappels. */
    public function test(Request $request, PushNotifier $notifier): JsonResponse
    {
        $user = $request->user();

        if (! $notifier->configured()) {
            return response()->json(['message' => 'Les notifications ne sont pas encore configurées sur le serveur.'], 503);
        }

        if (! $user->pushSubscriptions()->exists()) {
            return response()->json(['message' => 'Aucun appareil activé : clique d’abord sur « Activer sur cet appareil ».'], 422);
        }

        $delivered = $notifier->send(
            $user,
            'DevRoad',
            'Test réussi : tes rappels arriveront comme ceci.',
            route('dashboard', [], false),
        );

        if ($delivered === 0) {
            return response()->json(['message' => 'Envoi échoué : réactive les notifications sur cet appareil et réessaie.'], 502);
        }

        return response()->json(['ok' => true, 'delivered' => $delivered]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => ['required', 'url', 'max:2000']]);

        PushSubscription::where('user_id', $request->user()->id)
            ->where('endpoint_hash', hash('sha256', $data['endpoint']))
            ->delete();

        return response()->json(['ok' => true]);
    }
}
