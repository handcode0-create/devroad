<?php

/*
 * Documentations proposées dans la page « Documentation ».
 *
 * - provider « devdocs » : documents pré-générés et téléchargeables publiés par
 *   DevDocs (projet open source, MPL-2.0), avec la licence d'origine de chaque doc.
 * - provider « laravel » : docs officielles Laravel (Markdown, licence MIT) sur GitHub.
 *
 * « slug » DevDocs : on prend le slug exact s'il existe, sinon la version la plus
 * récente qui commence par « slug~ » (ex. node → node~22_lts).
 */
return [
    'devdocs' => [
        'manifest_url' => env('DEVDOCS_MANIFEST_URL', 'https://devdocs.io/docs.json'),
        'documents_url' => env('DEVDOCS_DOCUMENTS_URL', 'https://documents.devdocs.io'),
    ],

    'laravel' => [
        'branch' => env('LARAVEL_DOCS_BRANCH', '12.x'),
        'raw_url' => 'https://raw.githubusercontent.com/laravel/docs',
        'site_url' => 'https://laravel.com/docs',
    ],

    /*
     * Versions françaises officielles (« fr » dans chaque source) :
     * - mdn   : traduction de la communauté MDN (github.com/mdn/translated-content, CC-BY-SA 2.5)
     * - react : fr.react.dev (github.com/reactjs/fr.react.dev, CC-BY 4.0)
     * - php   : manuel PHP en français (php.net/manual/fr, CC-BY 3.0)
     * Sans version française, la page s'affiche en anglais avec la traduction par l'IA.
     */
    'french' => [
        'mdn_raw_url' => 'https://raw.githubusercontent.com/mdn/translated-content/main/files/fr',
        'mdn_site_url' => 'https://developer.mozilla.org/fr/docs',
        'react_raw_url' => 'https://raw.githubusercontent.com/reactjs/fr.react.dev/main/src',
        'react_site_url' => 'https://fr.react.dev',
        'php_url' => 'https://www.php.net/manual/fr',
    ],

    /*
     * Traduction française des pages anglaises (Laravel, Node.js, TypeScript, Tailwind, Git, PostgreSQL…).
     * Un élève sans clé d'IA est servi avec la clé de DevRoad : chaque page n'est traduite qu'une
     * fois, puis gardée en base pour tous les lecteurs. Sans clé DevRoad, seule la clé de
     * l'utilisateur (Paramètres → Assistant IA) permet de traduire.
     * Fournisseurs : anthropic | openai | gemini (Gemini Flash propose un quota gratuit).
     */
    'translation' => [
        'provider' => env('DEVROAD_TRANSLATE_PROVIDER'),
        'api_key' => env('DEVROAD_TRANSLATE_API_KEY'),
        'model' => env('DEVROAD_TRANSLATE_MODEL'),
        // Plafond de morceaux traduits par utilisateur et par jour avec la clé DevRoad (anti-abus).
        'daily_chunks_per_user' => (int) env('DEVROAD_TRANSLATE_DAILY_CHUNKS', 150),
    ],

    // Une source est re-synchronisée au déploiement si elle date de plus de N jours.
    'stale_after_days' => 7,

    'sources' => [
        'laravel' => ['provider' => 'laravel', 'name' => 'Laravel', 'technology' => 'laravel'],
        'php' => ['provider' => 'devdocs', 'slug' => 'php', 'name' => 'PHP', 'technology' => 'php', 'fr' => ['provider' => 'php']],
        'javascript' => ['provider' => 'devdocs', 'slug' => 'javascript', 'name' => 'JavaScript', 'technology' => 'javascript', 'fr' => ['provider' => 'mdn', 'prefix' => 'Web/JavaScript/Reference']],
        'html' => ['provider' => 'devdocs', 'slug' => 'html', 'name' => 'HTML', 'technology' => 'html', 'fr' => ['provider' => 'mdn', 'prefix' => 'Web/HTML']],
        'css' => ['provider' => 'devdocs', 'slug' => 'css', 'name' => 'CSS', 'technology' => 'css', 'fr' => ['provider' => 'mdn', 'prefix' => 'Web/CSS/Reference']],
        'dom' => ['provider' => 'devdocs', 'slug' => 'dom', 'name' => 'API Web (DOM)', 'technology' => 'javascript', 'fr' => ['provider' => 'mdn', 'prefix' => 'Web/API']],
        'react' => ['provider' => 'devdocs', 'slug' => 'react', 'name' => 'React', 'technology' => 'react', 'fr' => ['provider' => 'react']],
        'node' => ['provider' => 'devdocs', 'slug' => 'node', 'name' => 'Node.js', 'technology' => 'node'],
        'typescript' => ['provider' => 'devdocs', 'slug' => 'typescript', 'name' => 'TypeScript', 'technology' => 'typescript'],
        'tailwindcss' => ['provider' => 'devdocs', 'slug' => 'tailwindcss', 'name' => 'Tailwind CSS', 'technology' => 'tailwind'],
        'git' => ['provider' => 'devdocs', 'slug' => 'git', 'name' => 'Git', 'technology' => 'git'],
        'postgresql' => ['provider' => 'devdocs', 'slug' => 'postgresql', 'name' => 'PostgreSQL', 'technology' => 'postgresql'],
    ],
];
