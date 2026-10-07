<?php

// Clés VAPID : générer une fois avec `php artisan devroad:vapid-keys`, puis les mettre dans les variables d'environnement.
return [
    'public_key' => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject' => env('VAPID_SUBJECT', 'mailto:'.env('MAIL_FROM_ADDRESS', 'contact@devroad.app')),
    // Fuseau dans lequel l'étudiant règle l'heure de son rappel.
    'timezone' => env('REMINDER_TIMEZONE', 'Africa/Abidjan'),
];
