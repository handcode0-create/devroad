<?php

namespace App\Console\Commands;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendLearningReminders extends Command
{
    protected $signature = 'devroad:send-reminders';

    protected $description = "Envoie le rappel d'apprentissage (notification push) aux étudiants dont l'heure est arrivée";

    public function handle(): int
    {
        if (! class_exists(\Minishlink\WebPush\WebPush::class)) {
            $this->error('minishlink/web-push est absent : lance `composer require minishlink/web-push`.');

            return self::FAILURE;
        }

        $public = config('webpush.public_key');
        $private = config('webpush.private_key');
        if (! $public || ! $private) {
            $this->error('Clés VAPID manquantes (VAPID_PUBLIC_KEY / VAPID_PRIVATE_KEY).');

            return self::FAILURE;
        }

        $now = Carbon::now(config('webpush.timezone'));
        $today = $now->toDateString();
        $dayNumber = $now->dayOfWeekIso; // 1 = lundi … 7 = dimanche

        $users = User::query()
            ->where('learning_reminders', true)
            ->whereHas('pushSubscriptions')
            ->where('reminder_time', '<=', $now->format('H:i'))
            ->where(fn ($q) => $q->whereNull('last_reminder_on')->orWhere('last_reminder_on', '<', $today))
            ->get()
            // Fenêtre de 2 h : si le serveur était coupé, on ne réveille pas l'étudiant bien après l'heure choisie.
            ->filter(fn (User $u) => Carbon::createFromFormat('H:i', $u->reminder_time, $now->timezone)
                ->setDateFrom($now)->diffInMinutes($now) <= 120)
            ->filter(fn (User $u) => in_array($dayNumber, $u->reminder_days ?? [1, 2, 3, 4, 5, 6, 7], true));

        $webPush = new \Minishlink\WebPush\WebPush(['VAPID' => [
            'subject' => config('webpush.subject'),
            'publicKey' => $public,
            'privateKey' => $private,
        ]]);

        $sent = 0;
        foreach ($users as $user) {
            $payload = json_encode([
                'title' => 'DevRoad',
                'body' => "C'est l'heure : {$user->daily_goal_minutes} minutes pour avancer aujourd'hui.",
                'url' => route('dashboard', [], false),
            ]);

            foreach ($user->pushSubscriptions as $sub) {
                $webPush->queueNotification(
                    \Minishlink\WebPush\Subscription::create([
                        'endpoint' => $sub->endpoint,
                        'publicKey' => $sub->public_key,
                        'authToken' => $sub->auth_token,
                        'contentEncoding' => $sub->content_encoding,
                    ]),
                    $payload,
                );
            }

            $user->forceFill(['last_reminder_on' => $today])->save();
            $sent++;
        }

        foreach ($webPush->flush() as $report) {
            // Abonnement expiré ou désinstallé : on le supprime.
            if ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
            }
        }

        $this->info("{$sent} rappel(s) envoyé(s).");

        return self::SUCCESS;
    }
}
