<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'devroad:vapid-keys';

    protected $description = 'Génère une paire de clés VAPID pour les notifications push';

    public function handle(): int
    {
        if (! class_exists(\Minishlink\WebPush\VAPID::class)) {
            $this->error('minishlink/web-push est absent : lance `composer require minishlink/web-push`.');

            return self::FAILURE;
        }

        $keys = \Minishlink\WebPush\VAPID::createVapidKeys();
        $this->line('VAPID_PUBLIC_KEY='.$keys['publicKey']);
        $this->line('VAPID_PRIVATE_KEY='.$keys['privateKey']);
        $this->comment('Ajoute ces deux lignes aux variables d\'environnement (Railway), sans jamais commiter la clé privée.');

        return self::SUCCESS;
    }
}
