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

    // Une source est re-synchronisée au déploiement si elle date de plus de N jours.
    'stale_after_days' => 7,

    'sources' => [
        'laravel' => ['provider' => 'laravel', 'name' => 'Laravel', 'technology' => 'laravel'],
        'php' => ['provider' => 'devdocs', 'slug' => 'php', 'name' => 'PHP', 'technology' => 'php'],
        'javascript' => ['provider' => 'devdocs', 'slug' => 'javascript', 'name' => 'JavaScript', 'technology' => 'javascript'],
        'html' => ['provider' => 'devdocs', 'slug' => 'html', 'name' => 'HTML', 'technology' => 'html'],
        'css' => ['provider' => 'devdocs', 'slug' => 'css', 'name' => 'CSS', 'technology' => 'css'],
        'dom' => ['provider' => 'devdocs', 'slug' => 'dom', 'name' => 'API Web (DOM)', 'technology' => 'javascript'],
        'react' => ['provider' => 'devdocs', 'slug' => 'react', 'name' => 'React', 'technology' => 'react'],
        'node' => ['provider' => 'devdocs', 'slug' => 'node', 'name' => 'Node.js', 'technology' => 'node'],
        'typescript' => ['provider' => 'devdocs', 'slug' => 'typescript', 'name' => 'TypeScript', 'technology' => 'typescript'],
        'tailwindcss' => ['provider' => 'devdocs', 'slug' => 'tailwindcss', 'name' => 'Tailwind CSS', 'technology' => 'tailwind'],
        'git' => ['provider' => 'devdocs', 'slug' => 'git', 'name' => 'Git', 'technology' => 'git'],
        'postgresql' => ['provider' => 'devdocs', 'slug' => 'postgresql', 'name' => 'PostgreSQL', 'technology' => 'postgresql'],
    ],
];
