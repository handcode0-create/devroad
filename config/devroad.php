<?php

return [
    'technologies' => [
        'laravel' => 'Laravel',
        'nextjs' => 'Next.js',
        'react' => 'React',
        'javascript' => 'JavaScript',
        'typescript' => 'TypeScript',
        'php' => 'PHP',
        'html' => 'HTML',
        'css' => 'CSS',
        'tailwind' => 'Tailwind CSS',
        'node' => 'Node.js',
        'git' => 'Git',
        'github' => 'GitHub',
        'docker' => 'Docker',
        'mysql' => 'MySQL',
        'postgresql' => 'PostgreSQL',
    ],

    'catalog' => [
        'laravel' => [
            'description' => 'Framework PHP moderne pour construire des applications web structurées, sécurisées et maintenables.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://laravel.com/docs'],
                ['label' => 'Laravel Learn', 'url' => 'https://laracasts.com/series/30-days-to-learn-laravel'],
                ['label' => 'GitHub Laravel', 'url' => 'https://github.com/laravel/laravel'],
            ],
        ],
        'nextjs' => [
            'description' => 'Framework React full-stack pour construire des applications web modernes avec routing, rendu serveur et API.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://nextjs.org/docs'],
                ['label' => 'Tutoriel officiel', 'url' => 'https://nextjs.org/learn'],
                ['label' => 'GitHub Next.js', 'url' => 'https://github.com/vercel/next.js'],
            ],
        ],
        'react' => [
            'description' => 'Bibliothèque JavaScript pour construire des interfaces composées de composants réutilisables.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://react.dev/learn'],
                ['label' => 'API Reference', 'url' => 'https://react.dev/reference/react'],
                ['label' => 'GitHub React', 'url' => 'https://github.com/facebook/react'],
            ],
        ],
        'javascript' => [
            'description' => 'Langage de programmation du web utilisé pour construire des interfaces, des applications et des services.',
            'resources' => [
                ['label' => 'MDN JavaScript', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/JavaScript'],
                ['label' => 'JavaScript.info', 'url' => 'https://javascript.info/'],
                ['label' => 'ECMAScript', 'url' => 'https://tc39.es/ecma262/'],
            ],
        ],
        'typescript' => [
            'description' => 'Sur-ensemble typé de JavaScript qui améliore la sûreté et la maintenabilité des projets.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://www.typescriptlang.org/docs/'],
                ['label' => 'Handbook', 'url' => 'https://www.typescriptlang.org/docs/handbook/intro.html'],
                ['label' => 'GitHub TypeScript', 'url' => 'https://github.com/microsoft/TypeScript'],
            ],
        ],
        'php' => [
            'description' => 'Langage serveur généraliste utilisé pour le web, les APIs et les applications métier.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://www.php.net/docs.php'],
                ['label' => 'Manuel PHP', 'url' => 'https://www.php.net/manual/en/'],
                ['label' => 'PHP Standards Recommendations', 'url' => 'https://www.php-fig.org/psr/'],
            ],
        ],
        'html' => [
            'description' => 'Langage de balisage qui structure le contenu et la sémantique des pages web.',
            'resources' => [
                ['label' => 'MDN HTML', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/HTML'],
                ['label' => 'HTML Living Standard', 'url' => 'https://html.spec.whatwg.org/'],
                ['label' => 'Web accessibility', 'url' => 'https://www.w3.org/WAI/standards-guidelines/wcag/'],
            ],
        ],
        'css' => [
            'description' => 'Langage de style qui contrôle la présentation, le layout, les animations et le responsive design.',
            'resources' => [
                ['label' => 'MDN CSS', 'url' => 'https://developer.mozilla.org/en-US/docs/Web/CSS'],
                ['label' => 'CSS Specifications', 'url' => 'https://www.w3.org/Style/CSS/'],
                ['label' => 'web.dev CSS', 'url' => 'https://web.dev/learn/css'],
            ],
        ],
        'tailwind' => [
            'description' => 'Framework CSS utility-first pour composer rapidement des interfaces cohérentes et responsive.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://tailwindcss.com/docs'],
                ['label' => 'Tailwind UI', 'url' => 'https://tailwindui.com/'],
                ['label' => 'GitHub Tailwind CSS', 'url' => 'https://github.com/tailwindlabs/tailwindcss'],
            ],
        ],
        'node' => [
            'description' => 'Runtime JavaScript côté serveur basé sur V8, utilisé pour les APIs, outils CLI et applications backend.',
            'resources' => [
                ['label' => 'Documentation officielle', 'url' => 'https://nodejs.org/docs/latest/api/'],
                ['label' => 'Node.js Learn', 'url' => 'https://nodejs.org/en/learn'],
                ['label' => 'NPM Documentation', 'url' => 'https://docs.npmjs.com/'],
            ],
        ],
        'git' => [
            'description' => 'Système de contrôle de version distribué pour suivre les changements et collaborer sur du code.',
            'resources' => [
                ['label' => 'Documentation Git', 'url' => 'https://git-scm.com/docs'],
                ['label' => 'Pro Git', 'url' => 'https://git-scm.com/book/en/v2'],
                ['label' => 'Git Reference', 'url' => 'https://git-scm.com/docs/gittutorial'],
            ],
        ],
        'github' => [
            'description' => 'Plateforme de collaboration autour de Git pour héberger le code, gérer les pull requests et automatiser les workflows.',
            'resources' => [
                ['label' => 'GitHub Docs', 'url' => 'https://docs.github.com/'],
                ['label' => 'GitHub Skills', 'url' => 'https://skills.github.com/'],
                ['label' => 'Actions Documentation', 'url' => 'https://docs.github.com/en/actions'],
            ],
        ],
        'docker' => [
            'description' => 'Plateforme de conteneurisation pour empaqueter une application et ses dépendances dans des environnements reproductibles.',
            'resources' => [
                ['label' => 'Documentation Docker', 'url' => 'https://docs.docker.com/'],
                ['label' => 'Docker Get Started', 'url' => 'https://docs.docker.com/get-started/'],
                ['label' => 'Docker Compose', 'url' => 'https://docs.docker.com/compose/'],
            ],
        ],
        'mysql' => [
            'description' => 'Système de gestion de base de données relationnelle couramment utilisé pour les applications web.',
            'resources' => [
                ['label' => 'Documentation MySQL', 'url' => 'https://dev.mysql.com/doc/'],
                ['label' => 'Reference Manual', 'url' => 'https://dev.mysql.com/doc/refman/en/'],
                ['label' => 'MySQL Workbench', 'url' => 'https://dev.mysql.com/downloads/workbench/'],
            ],
        ],
        'postgresql' => [
            'description' => 'Système de gestion de base de données relationnelle avancé, extensible et riche en fonctionnalités SQL.',
            'resources' => [
                ['label' => 'Documentation PostgreSQL', 'url' => 'https://www.postgresql.org/docs/'],
                ['label' => 'Tutorial SQL', 'url' => 'https://www.postgresql.org/docs/current/tutorial.html'],
                ['label' => 'PostgreSQL Wiki', 'url' => 'https://wiki.postgresql.org/'],
            ],
        ],
    ],
];
