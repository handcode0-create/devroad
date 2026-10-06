#!/usr/bin/env sh
set -eu

php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache

# Index de la page Documentation (DevDocs + docs Laravel) : seulement s'il manque ou date
# de plus de 7 jours. Une source indisponible ne doit jamais bloquer le déploiement.
php artisan docs:sync --if-stale || echo "docs:sync a échoué — la documentation sera synchronisée au prochain déploiement."
