<?php

return [
    'academic_levels' => [
        'licence' => 'Licence',
        'engineering' => 'Cycle Ingénieur',
    ],

    'levels' => [
        'beginner' => 'Débutant',
        'intermediate' => 'Intermédiaire',
        'professional' => 'Professionnel',
    ],

    'technologies' => [
        'php' => 'PHP',
        'laravel' => 'Laravel',
        'javascript' => 'JavaScript',
        'typescript' => 'TypeScript',
        'react' => 'React',
        'nextjs' => 'Next.js',
        'node' => 'Node.js',
        'html' => 'HTML',
        'css' => 'CSS',
        'tailwind' => 'Tailwind CSS',
        'git' => 'Git',
        'github' => 'GitHub',
        'docker' => 'Docker',
        'mysql' => 'MySQL',
        'postgresql' => 'PostgreSQL',
    ],

    'goals' => [
        'frontend' => 'Développement frontend',
        'backend' => 'Développement backend',
        'fullstack' => 'Développement full-stack',
        'mobile' => 'Développement mobile',
        'devops' => 'DevOps / Cloud',
        'architecture' => 'Architecture logicielle',
        'security' => 'Cybersécurité',
        'data' => 'Data / Bases de données',
    ],

    'categories' => [
        'fundamentals' => [
            'label' => 'Fondamentaux',
            'questions' => [
                ['id' => 'fundamentals_1', 'question' => 'À quoi sert principalement une variable ?', 'options' => [
                    ['id' => 'a', 'label' => 'Stocker une valeur utilisable par le programme', 'score' => 2],
                    ['id' => 'b', 'label' => 'Uniquement afficher du texte', 'score' => 0],
                    ['id' => 'c', 'label' => 'Démarrer un serveur automatiquement', 'score' => 0],
                ]],
                ['id' => 'fundamentals_2', 'question' => 'Quel mécanisme permet de répéter une opération tant qu’une condition est vraie ?', 'options' => [
                    ['id' => 'a', 'label' => 'Une boucle', 'score' => 2],
                    ['id' => 'b', 'label' => 'Un commentaire', 'score' => 0],
                    ['id' => 'c', 'label' => 'Un import', 'score' => 0],
                ]],
                ['id' => 'fundamentals_3', 'question' => 'À quoi sert une fonction ?', 'options' => [
                    ['id' => 'a', 'label' => 'Regrouper une logique réutilisable', 'score' => 2],
                    ['id' => 'b', 'label' => 'Créer uniquement une base de données', 'score' => 0],
                    ['id' => 'c', 'label' => 'Remplacer le système d’exploitation', 'score' => 0],
                ]],
            ],
        ],
        'programming' => [
            'label' => 'Programmation',
            'questions' => [
                ['id' => 'programming_1', 'question' => 'Quel principe consiste à masquer les détails internes d’un objet derrière une interface claire ?', 'options' => [
                    ['id' => 'a', 'label' => 'Encapsulation', 'score' => 2],
                    ['id' => 'b', 'label' => 'Compilation', 'score' => 0],
                    ['id' => 'c', 'label' => 'Sérialisation', 'score' => 0],
                ]],
                ['id' => 'programming_2', 'question' => 'Pourquoi gérer explicitement les erreurs dans une application ?', 'options' => [
                    ['id' => 'a', 'label' => 'Pour contrôler les cas d’échec et éviter des comportements imprévisibles', 'score' => 2],
                    ['id' => 'b', 'label' => 'Pour accélérer systématiquement toutes les requêtes', 'score' => 0],
                    ['id' => 'c', 'label' => 'Pour supprimer les tests', 'score' => 0],
                ]],
                ['id' => 'programming_3', 'question' => 'Quel est l’objectif principal d’un test automatisé ?', 'options' => [
                    ['id' => 'a', 'label' => 'Vérifier automatiquement un comportement attendu', 'score' => 2],
                    ['id' => 'b', 'label' => 'Remplacer Git', 'score' => 0],
                    ['id' => 'c', 'label' => 'Générer automatiquement toutes les fonctionnalités', 'score' => 0],
                ]],
            ],
        ],
        'web' => [
            'label' => 'Web',
            'questions' => [
                ['id' => 'web_1', 'question' => 'Quel protocole est principalement utilisé pour les échanges entre navigateur et serveur web ?', 'options' => [
                    ['id' => 'a', 'label' => 'HTTP / HTTPS', 'score' => 2],
                    ['id' => 'b', 'label' => 'FTP uniquement', 'score' => 0],
                    ['id' => 'c', 'label' => 'SMTP uniquement', 'score' => 0],
                ]],
                ['id' => 'web_2', 'question' => 'Quelle méthode HTTP est généralement utilisée pour récupérer une ressource ?', 'options' => [
                    ['id' => 'a', 'label' => 'GET', 'score' => 2],
                    ['id' => 'b', 'label' => 'DELETE', 'score' => 0],
                    ['id' => 'c', 'label' => 'PATCH uniquement', 'score' => 0],
                ]],
                ['id' => 'web_3', 'question' => 'Quel est le rôle principal de CSS ?', 'options' => [
                    ['id' => 'a', 'label' => 'Décrire la présentation et le style d’une interface', 'score' => 2],
                    ['id' => 'b', 'label' => 'Gérer les utilisateurs côté serveur', 'score' => 0],
                    ['id' => 'c', 'label' => 'Remplacer une base SQL', 'score' => 0],
                ]],
            ],
        ],
        'data' => [
            'label' => 'Bases de données',
            'questions' => [
                ['id' => 'data_1', 'question' => 'Quelle commande SQL permet de lire des données ?', 'options' => [
                    ['id' => 'a', 'label' => 'SELECT', 'score' => 2],
                    ['id' => 'b', 'label' => 'PUSH', 'score' => 0],
                    ['id' => 'c', 'label' => 'PRINT', 'score' => 0],
                ]],
                ['id' => 'data_2', 'question' => 'À quoi sert principalement une clé étrangère ?', 'options' => [
                    ['id' => 'a', 'label' => 'Relier une ligne à une ligne d’une autre table', 'score' => 2],
                    ['id' => 'b', 'label' => 'Chiffrer automatiquement toute la base', 'score' => 0],
                    ['id' => 'c', 'label' => 'Remplacer un index', 'score' => 0],
                ]],
                ['id' => 'data_3', 'question' => 'Quel mécanisme permet notamment de garantir un ensemble d’opérations comme une unité cohérente ?', 'options' => [
                    ['id' => 'a', 'label' => 'Une transaction', 'score' => 2],
                    ['id' => 'b', 'label' => 'Un commentaire SQL', 'score' => 0],
                    ['id' => 'c', 'label' => 'Une vue CSS', 'score' => 0],
                ]],
            ],
        ],
        'tools_architecture' => [
            'label' => 'Outils & architecture',
            'questions' => [
                ['id' => 'tools_1', 'question' => 'Quel outil permet principalement de versionner le code source ?', 'options' => [
                    ['id' => 'a', 'label' => 'Git', 'score' => 2],
                    ['id' => 'b', 'label' => 'Figma uniquement', 'score' => 0],
                    ['id' => 'c', 'label' => 'Postman uniquement', 'score' => 0],
                ]],
                ['id' => 'tools_2', 'question' => 'Pourquoi utiliser Docker dans un projet ?', 'options' => [
                    ['id' => 'a', 'label' => 'Isoler et reproduire des environnements d’exécution', 'score' => 2],
                    ['id' => 'b', 'label' => 'Remplacer systématiquement Git', 'score' => 0],
                    ['id' => 'c', 'label' => 'Créer uniquement des maquettes UI', 'score' => 0],
                ]],
                ['id' => 'tools_3', 'question' => 'Quel principe vise à séparer les responsabilités d’un système pour faciliter son évolution ?', 'options' => [
                    ['id' => 'a', 'label' => 'Séparation des responsabilités', 'score' => 2],
                    ['id' => 'b', 'label' => 'Copier-coller systématique', 'score' => 0],
                    ['id' => 'c', 'label' => 'Couplage maximal', 'score' => 0],
                ]],
            ],
        ],
    ],
];
