<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Rotation de APP_KEY : après avoir mis l'ancienne clé dans APP_PREVIOUS_KEYS et la nouvelle
 * dans APP_KEY, cette commande rechiffre les clés d'IA des utilisateurs avec la nouvelle clé.
 */
class ReencryptAiKeys extends Command
{
    protected $signature = 'devroad:reencrypt-ai-keys';

    protected $description = 'Rechiffre les clés d’API des utilisateurs avec l’APP_KEY actuelle (rotation de clé).';

    public function handle(): int
    {
        $done = 0;
        $failed = 0;

        User::query()->whereNotNull('ai_api_key')->orderBy('id')->each(function (User $user) use (&$done, &$failed): void {
            try {
                $plain = $user->ai_api_key; // déchiffré avec APP_KEY ou, à défaut, APP_PREVIOUS_KEYS
                DB::table('users')->where('id', $user->id)->update(['ai_api_key' => Crypt::encryptString($plain)]);
                $done++;
            } catch (DecryptException) {
                // Clé illisible (ancienne APP_KEY absente de APP_PREVIOUS_KEYS) : on la retire proprement.
                DB::table('users')->where('id', $user->id)->update(['ai_api_key' => null, 'ai_key_hint' => null]);
                $failed++;
            }
        });

        $this->info("{$done} clé(s) rechiffrée(s).");

        if ($failed > 0) {
            $this->warn("{$failed} clé(s) illisible(s) supprimée(s) : ces utilisateurs devront ressaisir leur clé.");
        }

        return self::SUCCESS;
    }
}
