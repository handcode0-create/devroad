<?php

/*
 * Messages de validation en français (sous-ensemble utilisé par DevRoad).
 * Les clés absentes retombent sur l'anglais (APP_FALLBACK_LOCALE).
 */
return [
    'accepted' => 'Le champ :attribute doit être accepté.',
    'array' => 'Le champ :attribute doit être une liste.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'in' => 'La valeur choisie pour :attribute est invalide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'lowercase' => 'Le champ :attribute doit être en minuscules.',
    'max' => [
        'array' => 'Le champ :attribute ne peut pas contenir plus de :max éléments.',
        'file' => 'Le fichier :attribute ne peut pas dépasser :max Ko.',
        'numeric' => 'Le champ :attribute ne peut pas dépasser :max.',
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],
    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le fichier :attribute doit faire au moins :min Ko.',
        'numeric' => 'Le champ :attribute doit être au moins égal à :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'present' => 'Le champ :attribute doit être présent.',
    'required' => 'Le champ :attribute est obligatoire.',
    'same' => 'Les champs :attribute et :other doivent être identiques.',
    'string' => 'Le champ :attribute doit être un texte.',
    'unique' => 'La valeur de :attribute est déjà utilisée.',

    'custom' => [
        'email' => [
            'unique' => 'Cette adresse e-mail a déjà un compte. Connecte-toi ou utilise une autre adresse.',
        ],
    ],

    'attributes' => [
        'name' => 'nom',
        'email' => 'adresse e-mail',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'current_password' => 'mot de passe actuel',
        'title' => 'titre',
        'description' => 'description',
        'content' => 'contenu',
        'status' => 'statut',
        'path' => 'chemin',
        'tags' => 'tags',
    ],
];
