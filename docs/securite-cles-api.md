# Sécurité des clés d'API des utilisateurs (assistant IA)

## Ce qui est en place
- La clé de chaque utilisateur est stockée **chiffrée** (AES-256, cast `encrypted` de Laravel) avec `APP_KEY`.
- Elle est masquée dans toutes les sorties (`$hidden`), jamais renvoyée au navigateur (seuls les 4 derniers caractères).
- Les appels au fournisseur partent du serveur ; la clé n'est jamais écrite dans les logs.
- La route d'enregistrement est limitée à 10 tentatives par minute ; les échecs sont consignés (sans la clé).
- Le formulaire recommande de créer une **clé dédiée avec un plafond de dépense**.

## Règles d'exploitation
1. `APP_KEY` n'existe que dans les variables d'environnement de l'hébergeur (Railway), jamais dans Git ni dans une capture.
2. La base de données n'est pas exposée publiquement ; l'application utilise un compte dédié aux droits limités.
3. Les sauvegardes de base sont stockées séparément de `APP_KEY`.
4. En cas de fuite, faire une rotation (ci-dessous) et demander aux utilisateurs de révoquer leurs clés chez le fournisseur.

## Rotation de APP_KEY
1. Générer une nouvelle clé : `php artisan key:generate --show`.
2. Dans l'environnement : mettre l'ancienne valeur dans `APP_PREVIOUS_KEYS` et la nouvelle dans `APP_KEY`.
3. Déployer, puis lancer `php artisan devroad:reencrypt-ai-keys` pour rechiffrer les clés stockées.
4. Une fois la commande terminée, retirer l'ancienne valeur de `APP_PREVIOUS_KEYS`.

Attention : la rotation invalide aussi les sessions et cookies chiffrés existants (les utilisateurs devront se reconnecter).

## Sécurité HTTP (en-têtes et limitations)

- `SecurityHeaders` (middleware web) envoie : `X-Content-Type-Options`, `X-Frame-Options: SAMEORIGIN`,
  `Referrer-Policy`, `Permissions-Policy` (caméra, micro, géolocalisation… coupés), `Cross-Origin-Opener-Policy`,
  `Strict-Transport-Security` (en HTTPS) et une `Content-Security-Policy` (hors environnement local).
- En production : liens et assets forcés en HTTPS, cookie de session `Secure` par défaut.
- Limitation de débit : inscription, connexion, mot de passe oublié/réinitialisation, confirmation et changement de mot de passe.
- À vérifier côté Railway : `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` définie, HTTPS actif sur le domaine.
