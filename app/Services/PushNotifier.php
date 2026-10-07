<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class PushNotifier
{
    public function configured(): bool
    {
        return class_exists(WebPush::class)
            && config('webpush.public_key')
            && config('webpush.private_key');
    }

    /** Envoie une notification à tous les appareils de l'utilisateur et renvoie le nombre de livraisons réussies. */
    public function send(User $user, string $title, string $body, string $url): int
    {
        $webPush = new WebPush(['VAPID' => [
            'subject' => config('webpush.subject'),
            'publicKey' => config('webpush.public_key'),
            'privateKey' => config('webpush.private_key'),
        ]]);

        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url]);

        foreach ($user->pushSubscriptions as $sub) {
            $webPush->queueNotification(Subscription::create([
                'endpoint' => $sub->endpoint,
                'publicKey' => $sub->public_key,
                'authToken' => $sub->auth_token,
                'contentEncoding' => $sub->content_encoding,
            ]), $payload);
        }

        $delivered = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $delivered++;
            } elseif ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
            }
        }

        return $delivered;
    }
}
