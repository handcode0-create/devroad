<?php

return [
    'levels' => [
        'beginner' => [
            'label' => 'Débutant',
            'categories' => [
                'fundamentals' => [
                    'label' => 'Fondamentaux',
                    'questions' => [
                        ['id' => 'beginner_fundamentals_1', 'question' => 'À quoi sert principalement une variable ?', 'options' => [
                            ['id' => 'a', 'label' => 'Stocker une valeur utilisable par le programme', 'score' => 2],
                            ['id' => 'b', 'label' => 'Uniquement afficher une page web', 'score' => 0],
                            ['id' => 'c', 'label' => 'Démarrer automatiquement un serveur', 'score' => 0],
                        ]],
                        ['id' => 'beginner_fundamentals_2', 'question' => 'À quoi sert une fonction ?', 'options' => [
                            ['id' => 'a', 'label' => 'Regrouper une logique réutilisable', 'score' => 2],
                            ['id' => 'b', 'label' => 'Créer obligatoirement une base de données', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer le système d’exploitation', 'score' => 0],
                        ]],
                        ['id' => 'beginner_fundamentals_3', 'question' => 'Que permet principalement une condition if ?', 'options' => [
                            ['id' => 'a', 'label' => Exécuter une partie du code selon une condition', 'score' => 2],
                            ['id' => 'b', 'label' => 'Installer une dépendance', 'score' => 0],
                            ['id' => 'c', 'label' => 'Compresser automatiquement les fichiers', 'score' => 0],
                        ]],
                    ],
                ],
                'programming' => [
                    'label' => 'Programmation',
                    'questions' => [
                        ['id' => 'beginner_programming_1', 'question' => 'Quel mécanisme permet de répéter une opération ?', 'options' => [
                            ['id' => 'a', 'label' => 'Une boucle', 'score' => 2],
                            ['id' => 'b', 'label' => 'Un commentaire', 'score' => 0],
                            ['id' => 'c', 'label' => 'Un import', 'score' => 0],
                        ]],
                        ['id' => 'beginner_programming_2', 'question' => 'Que représente généralement un tableau (array) ?', 'options' => [
                            ['id' => 'a', 'label' => 'Une collection de plusieurs valeurs', 'score' => 2],
                            ['id' => 'b', 'label' => 'Un serveur distant', 'score' => 0],
                            ['id' => 'c', 'label' => 'Une extension de fichier', 'score' => 0],
                        ]],
                        ['id' => 'beginner_programming_3', 'question' => 'Pourquoi commenter du code ?', 'options' => [
                            ['id' => 'a', 'label' => 'Pour expliquer une intention ou une logique utile', 'score' => 2],
                            ['id' => 'b', 'label' => 'Pour rendre toutes les fonctions plus rapides', 'score' => 0],
                            ['id' => 'c', 'label' => 'Pour remplacer les tests', 'score' => 0],
                        ]],
                    ],
                ],
                'web' => [
                    'label' => 'Web',
                    'questions' => [
                        ['id' => 'beginner_web_1', 'question' => 'Quel protocole est principalement utilisé entre navigateur et serveur web ?', 'options' => [
                            ['id' => 'a', 'label' => 'HTTP / HTTPS', 'score' => 2],
                            ['id' => 'b', 'label' => 'SMTP', 'score' => 0],
                            ['id' => 'c', 'label' => 'FTP uniquement', 'score' => 0],
                        ]],
                        ['id' => 'beginner_web_2', 'question' => 'Quel langage décrit principalement la structure d’une page web ?', 'options' => [
                            ['id' => 'a', 'label' => 'HTML', 'score' => 2],
                            ['id' => 'b', 'label' => 'SQL', 'score' => 0],
                            ['id' => 'c', 'label' => 'Dockerfile', 'score' => 0],
                        ]],
                        ['id' => 'beginner_web_3', 'question' => 'Quel est le rôle principal de CSS ?', 'options' => [
                            ['id' => 'a', 'label' => 'Décrire la présentation et le style', 'score' => 2],
                            ['id' => 'b', 'label' => 'Gérer les utilisateurs côté serveur', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer une base SQL', 'score' => 0],
                        ]],
                    ],
                ],
                'data' => [
                    'label' => 'Bases de données',
                    'questions' => [
                        ['id' => 'beginner_data_1', 'question' => 'Quelle commande SQL permet de lire des données ?', 'options' => [
                            ['id' => 'a', 'label' => 'SELECT', 'score' => 2],
                            ['id' => 'b', 'label' => 'PRINT', 'score' => 0],
                            ['id' => 'c', 'label' => 'PUSH', 'score' => 0],
                        ]],
                        ['id' => 'beginner_data_2', 'question' => 'Qu’est-ce qu’une table SQL ?', 'options' => [
                            ['id' => 'a', 'label' => 'Une structure qui organise des données en lignes et colonnes', 'score' => 2],
                            ['id' => 'b', 'label' => 'Un fichier CSS', 'score' => 0],
                            ['id' => 'c', 'label' => 'Un serveur web', 'score' => 0],
                        ]],
                        ['id' => 'beginner_data_3', 'question' => 'À quoi sert principalement un identifiant (id) ?', 'options' => [
                            ['id' => 'a', 'label' => 'Identifier une ligne de manière unique', 'score' => 2],
                            ['id' => 'b', 'label' => 'Changer la couleur d’une ligne', 'score' => 0],
                            ['id' => 'c', 'label' => 'Lancer une requête HTTP', 'score' => 0],
                        ]],
                    ],
                ],
                'tools_architecture' => [
                    'label' => 'Outils & architecture',
                    'questions' => [
                        ['id' => 'beginner_tools_1', 'question' => 'Quel outil permet principalement de versionner le code source ?', 'options' => [
                            ['id' => 'a', 'label' => 'Git', 'score' => 2],
                            ['id' => 'b', 'label' => 'Figma', 'score' => 0],
                            ['id' => 'c', 'label' => 'Postman', 'score' => 0],
                        ]],
                        ['id' => 'beginner_tools_2', 'question' => 'À quoi sert principalement un package manager ?', 'options' => [
                            ['id' => 'a', 'label' => 'Installer et gérer les dépendances d’un projet', 'score' => 2],
                            ['id' => 'b', 'label' => 'Dessiner une interface', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer Git', 'score' => 0],
                        ]],
                        ['id' => 'beginner_tools_3', 'question' => 'Pourquoi utiliser un fichier README ?', 'options' => [
                            ['id' => 'a', 'label' => 'Documenter l’utilisation et le contexte du projet', 'score' => 2],
                            ['id' => 'b', 'label' => 'Stocker les mots de passe', 'score' => 0],
                            ['id' => 'c', 'label' => 'Compiler automatiquement PHP', 'score' => 0],
                        ]],
                    ],
                ],
            ],
        ],

        'intermediate' => [
            'label' => 'Intermédiaire',
            'categories' => [
                'fundamentals' => [
                    'label' => 'Fondamentaux',
                    'questions' => [
                        ['id' => 'intermediate_fundamentals_1', 'question' => 'Quelle est une conséquence fréquente d’un état mutable partagé entre plusieurs parties d’une application ?', 'options' => [
                            ['id' => 'a', 'label' => 'Des effets de bord plus difficiles à raisonner', 'score' => 2],
                            ['id' => 'b', 'label' => 'Une compilation toujours plus rapide', 'score' => 0],
                            ['id' => 'c', 'label' => 'La suppression automatique des bugs', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_fundamentals_2', 'question' => 'Pourquoi séparer les responsabilités dans une application ?', 'options' => [
                            ['id' => 'a', 'label' => 'Réduire le couplage et faciliter l’évolution du code', 'score' => 2],
                            ['id' => 'b', 'label' => 'Éviter toute dépendance externe', 'score' => 0],
                            ['id' => 'c', 'label' => 'Supprimer le besoin de tests', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_fundamentals_3', 'question' => 'Quel est l’intérêt principal d’une abstraction ?', 'options' => [
                            ['id' => 'a', 'label' => 'Exposer une interface utile tout en masquant des détails d’implémentation', 'score' => 2],
                            ['id' => 'b', 'label' => 'Rendre toutes les classes publiques', 'score' => 0],
                            ['id' => 'c', 'label' => 'Empêcher toute réutilisation', 'score' => 0],
                        ]],
                    ],
                ],
                'programming' => [
                    'label' => 'Programmation',
                    'questions' => [
                        ['id' => 'intermediate_programming_1', 'question' => 'Pourquoi gérer explicitement les erreurs dans une application ?', 'options' => [
                            ['id' => 'a', 'label' => 'Contrôler les cas d’échec et préserver un comportement prévisible', 'score' => 2],
                            ['id' => 'b', 'label' => 'Supprimer tous les tests', 'score' => 0],
                            ['id' => 'c', 'label' => 'Garantir zéro erreur réseau', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_programming_2', 'question' => 'Quel est l’objectif principal d’un test automatisé ?', 'options' => [
                            ['id' => 'a', 'label' => 'Vérifier automatiquement un comportement attendu', 'score' => 2],
                            ['id' => 'b', 'label' => 'Remplacer Git', 'score' => 0],
                            ['id' => 'c', 'label' => 'Générer toute l’application', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_programming_3', 'question' => 'Pourquoi éviter une fonction qui fait trop de choses ?', 'options' => [
                            ['id' => 'a', 'label' => 'Elle devient plus difficile à tester, comprendre et faire évoluer', 'score' => 2],
                            ['id' => 'b', 'label' => 'Elle ne peut jamais être appelée deux fois', 'score' => 0],
                            ['id' => 'c', 'label' => 'Elle empêche toujours la compilation', 'score' => 0],
                        ]],
                    ],
                ],
                'web' => [
                    'label' => 'Web',
                    'questions' => [
                        ['id' => 'intermediate_web_1', 'question' => 'Quelle méthode HTTP est généralement utilisée pour récupérer une ressource ?', 'options' => [
                            ['id' => 'a', 'label' => 'GET', 'score' => 2],
                            ['id' => 'b', 'label' => 'DELETE', 'score' => 0],
                            ['id' => 'c', 'label' => 'PATCH uniquement', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_web_2', 'question' => 'Pourquoi valider les données reçues côté serveur même si le frontend les valide déjà ?', 'options' => [
                            ['id' => 'a', 'label' => 'Le serveur doit rester la frontière de confiance', 'score' => 2],
                            ['id' => 'b', 'label' => 'Le navigateur ne peut jamais envoyer de formulaire', 'score' => 0],
                            ['id' => 'c', 'label' => 'Cela rend le CSS plus rapide', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_web_3', 'question' => 'Quel est un avantage d’un rendu côté serveur ou hybride comme Inertia ?', 'options' => [
                            ['id' => 'a', 'label' => 'Conserver le routage et les données côté serveur tout en offrant une expérience SPA', 'score' => 2],
                            ['id' => 'b', 'label' => 'Supprimer définitivement le backend', 'score' => 0],
                            ['id' => 'c', 'label' => 'Éviter toute requête HTTP', 'score' => 0],
                        ]],
                    ],
                ],
                'data' => [
                    'label' => 'Bases de données',
                    'questions' => [
                        ['id' => 'intermediate_data_1', 'question' => 'À quoi sert principalement une clé étrangère ?', 'options' => [
                            ['id' => 'a', 'label' => 'Maintenir une relation entre des lignes de tables', 'score' => 2],
                            ['id' => 'b', 'label' => 'Chiffrer automatiquement les données', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer tous les index', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_data_2', 'question' => 'Quel mécanisme permet de garantir un ensemble d’opérations comme une unité cohérente ?', 'options' => [
                            ['id' => 'a', 'label' => 'Une transaction', 'score' => 2],
                            ['id' => 'b', 'label' => 'Un commentaire SQL', 'score' => 0],
                            ['id' => 'c', 'label' => 'Une variable CSS', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_data_3', 'question' => 'Pourquoi ajouter un index sur une colonne fréquemment recherchée ?', 'options' => [
                            ['id' => 'a', 'label' => 'Accélérer certaines recherches au prix d’un coût supplémentaire en écriture et stockage', 'score' => 2],
                            ['id' => 'b', 'label' => 'Garantir que toutes les requêtes seront instantanées', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer une contrainte de clé étrangère', 'score' => 0],
                        ]],
                    ],
                ],
                'tools_architecture' => [
                    'label' => 'Outils & architecture',
                    'questions' => [
                        ['id' => 'intermediate_tools_1', 'question' => 'Pourquoi utiliser Docker dans un projet ?', 'options' => [
                            ['id' => 'a', 'label' => 'Isoler et reproduire des environnements d’exécution', 'score' => 2],
                            ['id' => 'b', 'label' => 'Remplacer systématiquement Git', 'score' => 0],
                            ['id' => 'c', 'label' => 'Créer uniquement des maquettes UI', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_tools_2', 'question' => 'Quel est l’intérêt principal d’une CI ?', 'options' => [
                            ['id' => 'a', 'label' => 'Automatiser des vérifications et validations à chaque changement', 'score' => 2],
                            ['id' => 'b', 'label' => 'Déployer sans jamais tester', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer la revue de code dans tous les cas', 'score' => 0],
                        ]],
                        ['id' => 'intermediate_tools_3', 'question' => 'Quel principe aide à limiter le couplage entre composants ?', 'options' => [
                            ['id' => 'a', 'label' => 'Dépendre d’interfaces ou contrats stables plutôt que d’implémentations concrètes', 'score' => 2],
                            ['id' => 'b', 'label' => 'Partager toutes les variables globales', 'score' => 0],
                            ['id' => 'c', 'label' => 'Copier-coller chaque logique', 'score' => 0],
                        ]],
                    ],
                ],
            ],
        ],

        'professional' => [
            'label' => 'Professionnel',
            'categories' => [
                'fundamentals' => [
                    'label' => 'Fondamentaux avancés',
                    'questions' => [
                        ['id' => 'professional_fundamentals_1', 'question' => 'Quel problème le principe d’inversion des dépendances cherche-t-il notamment à réduire ?', 'options' => [
                            ['id' => 'a', 'label' => 'Le couplage entre la logique métier et des détails d’infrastructure', 'score' => 2],
                            ['id' => 'b', 'label' => 'Le nombre de commits Git', 'score' => 0],
                            ['id' => 'c', 'label' => 'La taille des fichiers CSS uniquement', 'score' => 0],
                        ]],
                        ['id' => 'professional_fundamentals_2', 'question' => 'Pourquoi préférer une frontière claire entre domaine et infrastructure ?', 'options' => [
                            ['id' => 'a', 'label' => 'Pour rendre le cœur métier plus testable et remplaçable', 'score' => 2],
                            ['id' => 'b', 'label' => 'Pour supprimer toutes les interfaces', 'score' => 0],
                            ['id' => 'c', 'label' => 'Pour empêcher toute évolution', 'score' => 0],
                        ]],
                        ['id' => 'professional_fundamentals_3', 'question' => 'Quel compromis est généralement associé à une architecture plus modulaire ?', 'options' => [
                            ['id' => 'a', 'label' => 'Plus de frontières et d’abstractions à maintenir en échange d’un meilleur découplage', 'score' => 2],
                            ['id' => 'b', 'label' => 'Aucun coût de complexité', 'score' => 0],
                            ['id' => 'c', 'label' => 'La disparition des tests', 'score' => 0],
                        ]],
                    ],
                ],
                'programming' => [
                    'label' => 'Programmation avancée',
                    'questions' => [
                        ['id' => 'professional_programming_1', 'question' => 'Quel est l’intérêt principal de rendre une opération idempotente ?', 'options' => [
                            ['id' => 'a', 'label' => 'Pouvoir la rejouer sans produire plusieurs fois un effet métier non désiré', 'score' => 2],
                            ['id' => 'b', 'label' => 'Garantir une exécution plus rapide', 'score' => 0],
                            ['id' => 'c', 'label' => 'Éviter toute validation', 'score' => 0],
                        ]],
                        ['id' => 'professional_programming_2', 'question' => 'Pourquoi isoler les effets de bord dans une application ?', 'options' => [
                            ['id' => 'a', 'label' => 'Pour rendre la logique plus prévisible, testable et composable', 'score' => 2],
                            ['id' => 'b', 'label' => 'Pour empêcher les appels réseau', 'score' => 0],
                            ['id' => 'c', 'label' => 'Pour éviter toute persistance', 'score' => 0],
                        ]],
                        ['id' => 'professional_programming_3', 'question' => 'Quel est un objectif important de l’observabilité applicative ?', 'options' => [
                            ['id' => 'a', 'label' => 'Comprendre le comportement réel d’un système via logs, métriques et traces', 'score' => 2],
                            ['id' => 'b', 'label' => 'Remplacer les tests unitaires', 'score' => 0],
                            ['id' => 'c', 'label' => 'Masquer toutes les erreurs aux développeurs', 'score' => 0],
                        ]],
                    ],
                ],
                'web' => [
                    'label' => 'Web avancé',
                    'questions' => [
                        ['id' => 'professional_web_1', 'question' => 'Pourquoi appliquer une politique de limitation de débit (rate limiting) ?', 'options' => [
                            ['id' => 'a', 'label' => 'Limiter les abus et protéger les ressources d’un service', 'score' => 2],
                            ['id' => 'b', 'label' => 'Garantir une latence nulle', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer l’authentification', 'score' => 0],
                        ]],
                        ['id' => 'professional_web_2', 'question' => 'Quel risque cherche notamment à réduire une politique CSP bien configurée ?', 'options' => [
                            ['id' => 'a', 'label' => 'Certaines formes d’exécution de contenu script non autorisé', 'score' => 2],
                            ['id' => 'b', 'label' => 'Les pannes de disque dur', 'score' => 0],
                            ['id' => 'c', 'label' => 'La perte de paquets réseau sur Internet', 'score' => 0],
                        ]],
                        ['id' => 'professional_web_3', 'question' => 'Pourquoi une API doit-elle versionner ou faire évoluer ses contrats avec prudence ?', 'options' => [
                            ['id' => 'a', 'label' => 'Des clients existants peuvent dépendre du comportement actuel', 'score' => 2],
                            ['id' => 'b', 'label' => 'Le HTTP interdit toute évolution', 'score' => 0],
                            ['id' => 'c', 'label' => 'Les bases SQL ne peuvent jamais changer', 'score' => 0],
                        ]],
                    ],
                ],
                'data' => [
                    'label' => 'Bases de données avancées',
                    'questions' => [
                        ['id' => 'professional_data_1', 'question' => 'Quel est le principal risque d’une transaction trop longue ?', 'options' => [
                            ['id' => 'a', 'label' => 'Conserver des verrous ou ressources trop longtemps et réduire la concurrence', 'score' => 2],
                            ['id' => 'b', 'label' => 'Améliorer toujours les performances', 'score' => 0],
                            ['id' => 'c', 'label' => 'Supprimer automatiquement les index', 'score' => 0],
                        ]],
                        ['id' => 'professional_data_2', 'question' => 'Pourquoi analyser le plan d’exécution d’une requête ?', 'options' => [
                            ['id' => 'a', 'label' => 'Comprendre comment le moteur accède aux données et identifier des coûts importants', 'score' => 2],
                            ['id' => 'b', 'label' => 'Modifier directement les données', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer les contraintes SQL', 'score' => 0],
                        ]],
                        ['id' => 'professional_data_3', 'question' => 'Quel problème une stratégie de pagination par curseur peut-elle mieux gérer qu’un OFFSET profond ?', 'options' => [
                            ['id' => 'a', 'label' => 'Parcourir efficacement de grands volumes selon une clé ordonnée', 'score' => 2],
                            ['id' => 'b', 'label' => 'Supprimer les besoins d’index', 'score' => 0],
                            ['id' => 'c', 'label' => 'Garantir des données immuables', 'score' => 0],
                        ]],
                    ],
                ],
                'tools_architecture' => [
                    'label' => 'Architecture & production',
                    'questions' => [
                        ['id' => 'professional_tools_1', 'question' => 'Quel est l’objectif d’un déploiement canary ?', 'options' => [
                            ['id' => 'a', 'label' => Exposer progressivement une nouvelle version à une partie du trafic', 'score' => 2],
                            ['id' => 'b', 'label' => 'Désactiver définitivement la CI', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer les sauvegardes', 'score' => 0],
                        ]],
                        ['id' => 'professional_tools_2', 'question' => 'Pourquoi définir des health checks en production ?', 'options' => [
                            ['id' => 'a', 'label' => 'Détecter automatiquement un service indisponible ou non fonctionnel', 'score' => 2],
                            ['id' => 'b', 'label' => 'Garantir zéro incident', 'score' => 0],
                            ['id' => 'c', 'label' => 'Remplacer les logs', 'score' => 0],
                        ]],
                        ['id' => 'professional_tools_3', 'question' => 'Quel est le rôle d’un mécanisme de rollback ?', 'options' => [
                            ['id' => 'a', 'label' => 'Revenir rapidement à une version connue comme fonctionnelle', 'score' => 2],
                            ['id' => 'b', 'label' => 'Supprimer toutes les données de production', 'score' => 0],
                            ['id' => 'c', 'label' => 'Empêcher tout futur déploiement', 'score' => 0],
                        ]],
                    ],
                ],
            ],
        ],
    ],
];
