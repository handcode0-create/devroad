# DevRoad Laravel Sandbox

Image isolee utilisee par DevLab pour les exercices Laravel.

## Construire l'image

docker build -t devroad/laravel-sandbox:latest docker/laravel-sandbox

## Verifier l'image

docker run --rm devroad/laravel-sandbox:latest php artisan --version

L'image contient un projet Laravel 12 autonome avec SQLite, Composer, Node.js/npm et les dependances frontend installees.

Chaque session DevRoad utilise ensuite un volume Docker separe pour conserver les fichiers du workspace entre deux commandes.
