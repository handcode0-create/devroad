---
title: Formulaires
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Un site qui se contente d'afficher du contenu est une vitrine. Dès que l'utilisateur doit **te parler** (s'inscrire, se connecter, payer, envoyer un message), il te faut un formulaire. C'est aussi l'endroit où la plupart des sites perdent leurs visiteurs : un formulaire mal conçu décourage, sur mobile surtout. HTML fournit pourtant, sans une ligne de JavaScript, de quoi construire des formulaires solides, accessibles et validés.

À la fin du chapitre, tu seras capable de :

- construire un formulaire avec `form`, `label`, `input`, `select`, `textarea` et `button` ;
- relier chaque champ à son étiquette de façon accessible ;
- choisir le bon `type` d'entrée pour obtenir le bon clavier mobile ;
- regrouper des champs avec `fieldset` et `legend` ;
- utiliser la validation native (`required`, `pattern`, `min`, `max`…) ;
- comprendre comment les données partent vers le serveur (GET, POST, `name`) ;
- repérer les pièges de sécurité côté serveur.

Prérequis : les trois premiers chapitres. Prévois deux heures et demie.

## Le formulaire : form, action, method

Un formulaire est un conteneur `form` qui regroupe des champs et sait **où** et **comment** envoyer leurs valeurs :

```html
<form action="/inscription" method="post">
  <!-- les champs viennent ici -->
  <button type="submit">Créer mon compte</button>
</form>
```

- `action` est l'URL qui reçoit les données (côté serveur, par exemple une route Laravel) ;
- `method` est le verbe HTTP : `get` ou `post`.

Quelle différence ? Avec **GET**, les données voyagent dans l'URL (`/recherche?q=laravel`) : pratique pour une recherche, qu'on veut pouvoir partager ou mettre en favori. Avec **POST**, elles voyagent dans le corps de la requête : à utiliser pour tout ce qui modifie des données ou contient des informations sensibles (mot de passe, paiement). Un mot de passe ne doit **jamais** transiter par GET.

> **Attention** : sous Laravel, un formulaire POST doit inclure un jeton CSRF (`@csrf` dans Blade). Sans lui, le serveur refuse la requête avec une erreur 419. Le formulaire HTML ne le sait pas : c'est une protection ajoutée côté serveur.

## Les champs et leurs étiquettes

Le champ de base est l'élément `input`. Son comportement dépend de l'attribut `type`. Il doit toujours être accompagné d'un `label` :

```html
<label for="email">Adresse e-mail</label>
<input type="email" id="email" name="email" autocomplete="email" required>
```

Trois attributs travaillent ensemble :

- `id` : identifiant unique du champ ;
- `for` (sur le `label`) : doit être **identique** à l'`id` du champ. Cette liaison permet de cliquer sur l'étiquette pour activer le champ et fait annoncer son nom par le lecteur d'écran ;
- `name` : le **nom sous lequel la valeur est envoyée** au serveur. Sans `name`, le champ n'est tout simplement pas transmis !

Autre façon de relier, sans `id` : envelopper le champ dans le label.

```html
<label>
  Prénom
  <input type="text" name="prenom" autocomplete="given-name">
</label>
```

Un `placeholder` n'est **pas** un label. Il disparaît dès que l'utilisateur tape, il est souvent peu contrasté, et il n'est pas fiable pour les technologies d'assistance. Utilise-le seulement pour un exemple de format (`placeholder="07 00 00 00 00"`), toujours en complément d'un vrai `label`.

:::quiz
Que se passe-t-il si un champ de formulaire n'a pas d'attribut `name` ?
- [ ] Le navigateur lui en attribue un automatiquement
- [ ] Le formulaire ne peut plus être soumis
- [x] Sa valeur n'est pas envoyée au serveur
- [ ] Il n'est plus affiché
> C'est l'attribut name qui sert de clé lors de l'envoi. Sans lui, le champ est ignoré dans la soumission, même s'il est visible et rempli.
:::

## Choisir le bon type d'entrée

Le `type` fait bien plus que changer l'apparence : il déclenche la **validation** et le **clavier adapté** sur téléphone.

| Type | Usage | Effet |
| --- | --- | --- |
| `text` | Texte court | Clavier standard |
| `email` | Adresse e-mail | Clavier avec @, validation du format |
| `tel` | Téléphone | Pavé numérique |
| `password` | Mot de passe | Caractères masqués |
| `number` | Nombre | Flèches, `min`, `max`, `step` |
| `url` | Adresse web | Clavier avec / et .com |
| `date` | Date | Sélecteur de date natif |
| `search` | Recherche | Bouton d'effacement |
| `checkbox` | Case à cocher | Oui ou non |
| `radio` | Choix unique | Un seul dans un groupe |
| `file` | Fichier | Sélecteur de fichier |
| `range` | Curseur | Valeur dans un intervalle |
| `hidden` | Donnée cachée | Non affiché |

Sur un site pour l'Afrique francophone, pense particulièrement au `tel` : un utilisateur qui voit directement le pavé numérique remplit son numéro de Mobile Money beaucoup plus vite. Associe-le à `autocomplete="tel"` pour que le navigateur propose la valeur enregistrée.

### Cases à cocher et boutons radio

```html
<fieldset>
  <legend>Quel est ton niveau ?</legend>

  <input type="radio" id="niveau-debutant" name="niveau" value="debutant" checked>
  <label for="niveau-debutant">Débutant</label>

  <input type="radio" id="niveau-inter" name="niveau" value="intermediaire">
  <label for="niveau-inter">Intermédiaire</label>

  <input type="radio" id="niveau-pro" name="niveau" value="pro">
  <label for="niveau-pro">Professionnel</label>
</fieldset>

<input type="checkbox" id="cgu" name="cgu" value="1" required>
<label for="cgu">J'accepte les conditions d'utilisation</label>
```

Pour les boutons radio, c'est le **même `name`** qui les regroupe : un seul peut être choisi. L'attribut `value` définit ce qui sera envoyé. `fieldset` et `legend` forment un groupe nommé : le lecteur d'écran annonce « Quel est ton niveau ? » avant chaque option, ce qui donne leur sens aux choix.

### Listes déroulantes et zones de texte

```html
<label for="techno">Technologie préférée</label>
<select id="techno" name="techno">
  <option value="">-- Choisis --</option>
  <optgroup label="Back-end">
    <option value="laravel">Laravel</option>
    <option value="node">Node.js</option>
  </optgroup>
  <optgroup label="Front-end">
    <option value="react">React</option>
    <option value="vue">Vue</option>
  </optgroup>
</select>

<label for="message">Ton message</label>
<textarea id="message" name="message" rows="5" maxlength="500"></textarea>
```

Le texte affiché d'une `option` peut différer de sa `value`, qui est ce que reçoit le serveur. `textarea` accepte plusieurs lignes ; `rows` règle sa hauteur initiale et `maxlength` limite la saisie.

### Les boutons

```html
<button type="submit">Envoyer</button>
<button type="reset">Réinitialiser</button>
<button type="button">Action sans envoi</button>
```

Attention : par défaut, un `button` **dans un formulaire** est de type `submit`. Si tu veux un bouton qui ne soumet pas (par exemple pour afficher/masquer un mot de passe), précise `type="button"`. Évite `reset`, que les utilisateurs cliquent par erreur en perdant toute leur saisie.

## La validation native

Avant même d'envoyer quoi que ce soit, le navigateur peut vérifier les valeurs grâce à de simples attributs :

```html
<form action="/inscription" method="post">
  <label for="nom">Nom complet</label>
  <input type="text" id="nom" name="nom" required minlength="2" maxlength="60">

  <label for="age">Âge</label>
  <input type="number" id="age" name="age" min="13" max="99" step="1">

  <label for="tel">Téléphone</label>
  <input type="tel" id="tel" name="tel"
         pattern="[0-9 ]{10,14}"
         title="10 à 14 chiffres, espaces autorisés">

  <label for="mdp">Mot de passe (8 caractères minimum)</label>
  <input type="password" id="mdp" name="mdp" required minlength="8"
         autocomplete="new-password">

  <button type="submit">Créer mon compte</button>
</form>
```

Les attributs de validation à connaître :

- `required` : champ obligatoire ;
- `minlength` et `maxlength` : longueur du texte ;
- `min`, `max`, `step` : bornes d'un nombre ou d'une date ;
- `pattern` : expression régulière que la valeur doit respecter (le `title` explique le format attendu) ;
- le `type` lui-même (`email`, `url`) contrôle le format.

Si une valeur est invalide, le navigateur bloque l'envoi, met le champ en évidence et affiche une bulle d'erreur. CSS te permet de styler ces états avec les pseudo-classes `:valid`, `:invalid` et `:user-invalid`. Pour désactiver cette vérification temporairement (par exemple pour un bouton « Enregistrer le brouillon »), l'attribut `novalidate` sur le `form` ou `formnovalidate` sur le bouton existe.

> **Attention** : la validation HTML est une **aide à l'utilisateur**, pas une sécurité. N'importe qui peut la contourner en modifiant le code ou en envoyant la requête à la main. Le serveur doit **toujours revalider** toutes les données (avec les règles de validation de Laravel, par exemple).

:::quiz
Pourquoi faut-il valider aussi les données côté serveur alors que HTML valide déjà les champs ?
- [ ] Parce que HTML ne sait pas valider les e-mails
- [x] Parce qu'un utilisateur peut contourner la validation du navigateur
- [ ] Parce que la validation HTML est trop lente
- [ ] Parce que le serveur ne reçoit pas les données du formulaire
> La validation côté client améliore l'expérience mais ne protège pas. Seule la validation serveur garantit l'intégrité des données.
:::

## Faciliter la saisie : autocomplete, inputmode et autres aides

Quelques attributs font gagner un temps précieux, surtout sur mobile :

```html
<input type="text" id="ville" name="ville" autocomplete="address-level2">
<input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code">
<input type="text" id="recherche" name="q" list="suggestions">
<datalist id="suggestions">
  <option value="Abidjan">
  <option value="Bouaké">
  <option value="Yamoussoukro">
</datalist>
```

- `autocomplete` indique au navigateur la nature du champ pour pré-remplir (nom, adresse, code à usage unique reçu par SMS) ;
- `inputmode="numeric"` affiche le pavé numérique sans imposer le comportement de `type="number"` (utile pour un code PIN ou un code de paiement, qu'on ne veut pas voir arrondi ou incrémenté) ;
- `datalist` propose des suggestions tout en laissant la saisie libre ;
- `autofocus` place le curseur dans le premier champ, mais à utiliser avec parcimonie car il peut désorienter ;
- `disabled` désactive un champ (non envoyé) alors que `readonly` le rend non modifiable mais envoyé.

## Messages d'erreur et retours utilisateur

Les bulles par défaut sont fonctionnelles mais limitées. Un bon formulaire explique **ce qui ne va pas et comment corriger**, près du champ concerné :

```html
<div class="champ">
  <label for="email2">Adresse e-mail</label>
  <input type="email" id="email2" name="email" required
         aria-describedby="email2-aide email2-erreur">
  <p id="email2-aide" class="aide">Nous t'écrivons seulement pour confirmer ton compte.</p>
  <p id="email2-erreur" class="erreur" role="alert">
    L'adresse doit contenir un @, par exemple awa@exemple.com.
  </p>
</div>
```

`aria-describedby` relie le champ à ses textes d'aide, que le lecteur d'écran lit après le label. `role="alert"` annonce immédiatement un message qui apparaît dynamiquement. Rédige des erreurs précises (« Le mot de passe doit contenir au moins 8 caractères ») plutôt que « Champ invalide ».

### Quelques principes de conception

- Demande le **minimum** d'informations : chaque champ en moins augmente le taux de complétion.
- Une seule colonne, sur mobile comme sur ordinateur.
- Indique clairement les champs optionnels plutôt que de marquer les obligatoires par une étoile ambiguë.
- Conserve la saisie de l'utilisateur en cas d'erreur serveur (avec Laravel, la fonction `old()`).
- Ajoute un message de confirmation après l'envoi.

:::quiz
Tu veux un groupe de trois boutons radio dont un seul peut être choisi. Que faut-il impérativement faire ?
- [ ] Leur donner le même id
- [x] Leur donner le même attribut name
- [ ] Les placer dans une liste ul
- [ ] Leur donner le même value
> Le name commun regroupe les boutons radio : choisir l'un décoche les autres. Les value, elles, doivent être différentes.
:::

## Atelier guidé : le formulaire d'inscription DevRoad

Compte une heure et demie.

1. Crée `inscription.html` avec le squelette complet, un `main` et un `h1` « Crée ton compte DevRoad ».
2. Ajoute un `form` avec `action="/inscription"` et `method="post"`.
3. Ajoute les champs nom, e-mail et téléphone, chacun avec `label`, `id`, `name`, le bon `type` et le bon `autocomplete`.
4. Ajoute un mot de passe avec `minlength="8"` et `required`, plus un texte d'aide relié par `aria-describedby`.
5. Ajoute un `fieldset` avec `legend` « Ton niveau » et trois boutons radio (débutant, intermédiaire, professionnel).
6. Ajoute un `select` regroupant les roadmaps par catégorie avec `optgroup`.
7. Ajoute une case à cocher obligatoire pour accepter les conditions, avec un lien vers la page des conditions.
8. Ajoute un `textarea` optionnel « Ton objectif » limité à 300 caractères.
9. Termine par un bouton `submit`. Teste la validation en laissant des champs vides, en tapant un faux e-mail, puis un mot de passe trop court.
10. Ouvre les outils de développement sur mobile, vérifie que le clavier numérique apparaît pour le téléphone, et navigue entièrement au clavier avec Tab.
11. Remplace `action` par `https://httpbin.org/post` et envoie : lis les données reçues et vérifie que chaque `name` apparaît.

Auto-évaluation : chaque champ est-il cliquable via son label ? Le formulaire est-il utilisable sans souris ? Sais-tu expliquer pourquoi le serveur doit tout revalider ?

## Erreurs fréquentes

- **Oublier `name`** : le champ n'est pas envoyé.
- **Utiliser `placeholder` à la place de `label`.** Le champ devient inaccessible et ambigu.
- **Un `for` différent de l'`id`.** La liaison est rompue sans message d'erreur.
- **Dupliquer des `id`.** Les labels pointent vers le mauvais champ.
- **Choisir `type="text"` pour tout.** Tu perds validation et clavier adapté.
- **Faire confiance à la validation HTML seule.** Elle se contourne en quelques secondes.
- **Envoyer un mot de passe en GET.** Il apparaît dans l'URL, l'historique et les journaux.
- **Un bouton sans `type` dans un formulaire.** Il soumet alors que tu ne le voulais pas.

## Bonnes pratiques

- Un `label` visible pour chaque champ, associé par `for` et `id`.
- Le type d'entrée le plus précis possible, avec `autocomplete` correct.
- Regroupe les choix liés avec `fieldset` et `legend`.
- Écris des messages d'erreur clairs, proches du champ, annoncés aux lecteurs d'écran.
- Valide toujours côté serveur et protège les formulaires (CSRF, limitation de tentatives).
- Rends les boutons assez grands pour le doigt (au moins 44 pixels de haut).
- Minimise le nombre de champs et garde la saisie en cas d'erreur.
- Teste sur un vrai téléphone, pas seulement dans le simulateur.

## À retenir

- Un `form` possède `action` (où envoyer) et `method` (GET pour lire, POST pour modifier).
- Chaque champ a un `label` associé, un `name` pour l'envoi et un `type` adapté.
- `fieldset` et `legend` regroupent les choix liés ; les boutons radio partagent un même `name`.
- La validation native (`required`, `minlength`, `pattern`, `min`, `max`) améliore l'expérience mais **ne remplace pas** la validation serveur.
- `autocomplete`, `inputmode` et `datalist` accélèrent la saisie, surtout sur mobile.
- Les messages d'erreur doivent être précis, reliés au champ (`aria-describedby`) et annoncés (`role="alert"`).
