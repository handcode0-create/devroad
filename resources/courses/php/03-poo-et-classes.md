---
title: POO et classes
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Jusqu'ici, tu as manipulé des tableaux et des fonctions. Cela fonctionne pour de petits scripts, mais dès que le projet grossit, on perd le fil : quelles clés contient ce tableau ? Quelle fonction peut le modifier ? La **programmation orientée objet** (POO) répond à ce problème en regroupant les données et le comportement dans une même unité : l'objet. C'est la base de Laravel, de Symfony et de tout PHP moderne.

À la fin du chapitre, tu seras capable de :

- définir une classe avec des propriétés et des méthodes, et créer des objets ;
- utiliser le constructeur et la promotion de propriétés (PHP 8) ;
- contrôler la visibilité avec `public`, `protected` et `private` ;
- écrire des propriétés `readonly` et des énumérations (`enum`) ;
- faire de l'héritage, des classes abstraites et des interfaces ;
- réutiliser du code avec les traits ;
- lever et attraper des exceptions ;
- expliquer le principe d'injection de dépendances.

Prérequis : les chapitres « Fondamentaux PHP » et « Tableaux et fonctions ». Prévois deux heures et demie.

## Pourquoi des objets ?

Voici une roadmap représentée par un tableau :

```php
$roadmap = ["titre" => "Laravel", "minutes" => 180];
$roadmap["minuts"] = 200; // faute de frappe : aucune erreur, juste un bug silencieux
```

Rien n'empêche d'écrire une clé fausse, un nombre négatif ou une chaîne là où l'on attend un entier. Un objet apporte trois garanties : les propriétés sont **déclarées** et **typées**, le **comportement** (les méthodes) vit avec les données, et on peut **interdire** l'accès direct à ce qui doit rester interne.

## Ta première classe

Une **classe** est un plan de construction ; un **objet** est une instance concrète fabriquée à partir de ce plan.

```php
<?php

declare(strict_types=1);

class Roadmap
{
    public string $titre;
    public int $minutes;

    public function __construct(string $titre, int $minutes)
    {
        $this->titre = $titre;
        $this->minutes = $minutes;
    }

    public function duree(): string
    {
        return intdiv($this->minutes, 60) . " h " . ($this->minutes % 60) . " min";
    }
}

$laravel = new Roadmap("Laravel", 150);
echo $laravel->titre;     // Laravel
echo $laravel->duree();   // 2 h 30 min
```

Points clés :

- `new Roadmap(...)` crée un objet et appelle le **constructeur** `__construct` ;
- `$this` désigne l'objet courant, à l'intérieur de la classe ;
- on accède aux propriétés et aux méthodes avec la **flèche** `->` ;
- chaque objet a ses propres valeurs : deux roadmaps sont indépendantes.

### La promotion de propriétés

PHP 8 permet de déclarer et d'affecter les propriétés directement dans la signature du constructeur. Le code précédent devient :

```php
class Roadmap
{
    public function __construct(
        public string $titre,
        public int $minutes,
    ) {
    }

    public function duree(): string
    {
        return intdiv($this->minutes, 60) . " h " . ($this->minutes % 60) . " min";
    }
}
```

Moins de répétition, même résultat. C'est l'écriture qu'on retrouve dans les projets récents.

## La visibilité et l'encapsulation

Que se passe-t-il si quelqu'un écrit `$laravel->minutes = -50;` ? L'objet devient incohérent. L'**encapsulation** consiste à protéger l'état interne et à n'exposer que des méthodes contrôlées. Trois niveaux de visibilité existent :

| Mot-clé | Accessible depuis |
| --- | --- |
| `public` | N'importe où |
| `protected` | La classe et ses classes filles |
| `private` | La classe seule |

```php
class CompteurLecons
{
    private int $terminees = 0;

    public function __construct(private int $total)
    {
    }

    public function terminer(): void
    {
        if ($this->terminees >= $this->total) {
            throw new LogicException("Toutes les leçons sont déjà terminées.");
        }
        $this->terminees++;
    }

    public function progression(): float
    {
        return round($this->terminees / $this->total * 100, 1);
    }
}

$c = new CompteurLecons(4);
$c->terminer();
echo $c->progression(); // 25
// $c->terminees = 99;  // Erreur : propriété privée
```

La progression ne peut plus dépasser 100 %, car **seule** la méthode `terminer()` modifie le compteur. Le code qui utilise la classe n'a pas besoin de connaître ses détails internes.

> **À retenir** : mets tes propriétés en `private` par défaut et n'ouvre l'accès que par des méthodes quand c'est nécessaire.

### readonly et enum

Pour un objet qui ne doit jamais changer après sa création (une « valeur »), utilise `readonly` :

```php
final class Prix
{
    public function __construct(
        public readonly int $montant,
        public readonly string $devise = "XOF",
    ) {
    }

    public function avecRemise(int $pourcent): self
    {
        return new self((int) round($this->montant * (100 - $pourcent) / 100), $this->devise);
    }
}

$p = new Prix(10000);
$moins = $p->avecRemise(20); // un nouvel objet : $p reste inchangé
```

Pour un ensemble fini de valeurs, PHP 8.1 propose les **énumérations** :

```php
enum Niveau: string
{
    case Debutant = "beginner";
    case Intermediaire = "intermediate";
    case Professionnel = "professional";

    public function libelle(): string
    {
        return match ($this) {
            self::Debutant => "Débutant",
            self::Intermediaire => "Intermédiaire",
            self::Professionnel => "Professionnel",
        };
    }
}

echo Niveau::Debutant->libelle();           // Débutant
echo Niveau::from("professional")->name;    // Professionnel
echo Niveau::tryFrom("inconnu")?->name ?? "?"; // ?
```

Une enum rend impossible une valeur invalide comme `"debutnat"` : le type `Niveau` suffit à garantir la cohérence.

:::quiz
Quelle visibilité empêche tout accès à une propriété depuis l'extérieur de la classe, y compris depuis une classe fille ?
- [ ] public
- [ ] protected
- [x] private
- [ ] readonly
> `private` limite l'accès à la classe qui déclare la propriété. `protected` autorise aussi les classes filles, `public` autorise tout le monde. `readonly` ne concerne pas la visibilité mais la modification.
:::

## Méthodes et propriétés statiques

Un membre `static` appartient à la **classe** et non à un objet. On l'appelle avec `::` :

```php
class Duree
{
    public const MINUTES_PAR_HEURE = 60;

    public static function enMinutes(int $heures, int $minutes = 0): int
    {
        return $heures * self::MINUTES_PAR_HEURE + $minutes;
    }
}

echo Duree::enMinutes(2, 30); // 150
```

Les constructeurs nommés, comme `Roadmap::depuisTableau($donnees)`, utilisent souvent ce mécanisme. Évite néanmoins l'état statique modifiable : il joue le rôle d'une variable globale cachée.

## L'héritage

Une classe peut **hériter** d'une autre avec `extends` : elle reprend ses propriétés et ses méthodes, et peut les redéfinir.

```php
abstract class Contenu
{
    public function __construct(
        protected string $titre,
        protected int $minutes,
    ) {
    }

    abstract public function type(): string;

    public function resume(): string
    {
        return "[{$this->type()}] {$this->titre} ({$this->minutes} min)";
    }
}

class Cours extends Contenu
{
    public function type(): string
    {
        return "Cours";
    }
}

class Atelier extends Contenu
{
    public function __construct(string $titre, int $minutes, private int $etapes)
    {
        parent::__construct($titre, $minutes);
    }

    public function type(): string
    {
        return "Atelier";
    }

    public function resume(): string
    {
        return parent::resume() . " - {$this->etapes} étapes";
    }
}

echo (new Atelier("Premier bot", 90, 8))->resume();
// [Atelier] Premier bot (90 min) - 8 étapes
```

- une classe **abstraite** ne peut pas être instanciée ; elle impose à ses filles d'implémenter les méthodes `abstract` ;
- `parent::` appelle la version de la classe mère ;
- `protected` rend les propriétés visibles aux filles.

L'héritage est puissant mais crée un lien fort. Ne l'utilise que pour une vraie relation « est un » : un `Atelier` **est un** `Contenu`.

## Les interfaces

Une **interface** définit un contrat : la liste des méthodes qu'une classe doit proposer, sans dire comment.

```php
interface Exportable
{
    public function versTableau(): array;
}

interface Affichable
{
    public function resume(): string;
}

class Badge implements Exportable, Affichable
{
    public function __construct(private string $nom, private int $points)
    {
    }

    public function versTableau(): array
    {
        return ["nom" => $this->nom, "points" => $this->points];
    }

    public function resume(): string
    {
        return "{$this->nom} ({$this->points} pts)";
    }
}

function exporter(Exportable $objet): string
{
    return json_encode($objet->versTableau(), JSON_UNESCAPED_UNICODE);
}
```

Une classe peut implémenter plusieurs interfaces, alors qu'elle n'hérite que d'une seule classe. La fonction `exporter` accepte **n'importe quel objet** exportable : c'est le **polymorphisme**.

## Les traits

Un **trait** est un lot de méthodes réutilisables qu'on insère dans plusieurs classes, sans lien d'héritage :

```php
trait Horodate
{
    private ?DateTimeImmutable $creeLe = null;

    public function marquerCree(): void
    {
        $this->creeLe = new DateTimeImmutable();
    }

    public function creeLe(): ?DateTimeImmutable
    {
        return $this->creeLe;
    }
}

class Note
{
    use Horodate;
}
```

Laravel utilise massivement les traits (`HasFactory`, `Notifiable`). N'en abuse pas : un trait trop gros cache des dépendances.

:::quiz
Quelle est la différence principale entre une interface et une classe abstraite ?
- [ ] Une interface peut contenir des propriétés privées
- [x] Une classe peut implémenter plusieurs interfaces mais n'hériter que d'une seule classe
- [ ] Une classe abstraite ne peut pas avoir de méthodes
- [ ] Il n'y a aucune différence
> Une interface décrit un contrat et une classe peut en implémenter autant qu'elle veut. L'héritage, lui, est unique : une classe n'a qu'un seul parent.
:::

## Les exceptions

Quand quelque chose se passe mal, on **lève** une exception avec `throw` et on l'**attrape** avec `try / catch` :

```php
class LeconIntrouvable extends RuntimeException
{
}

function trouverLecon(array $lecons, int $id): array
{
    return $lecons[$id] ?? throw new LeconIntrouvable("La leçon $id n'existe pas.");
}

try {
    $lecon = trouverLecon([1 => ["titre" => "PHP"]], 5);
} catch (LeconIntrouvable $e) {
    echo "Erreur : " . $e->getMessage();
} finally {
    echo "\nFin du traitement.";
}
```

Crée tes propres classes d'exception pour distinguer les cas métier. `finally` s'exécute toujours, utile pour fermer une ressource.

## L'injection de dépendances

Une classe ne devrait pas fabriquer elle-même tout ce dont elle a besoin. Compare :

```php
class RapportMauvais
{
    public function generer(): string
    {
        $db = new PDO("sqlite:app.db"); // dépendance cachée et figée
        // ...
    }
}

class Rapport
{
    public function __construct(private PDO $db)
    {
    }
}
```

Dans la seconde version, la dépendance est **fournie de l'extérieur** par le constructeur. On peut ainsi lui passer une base de test, ou changer de base sans modifier la classe. C'est le principe que Laravel applique avec son conteneur de services, et qui rend le code testable.

## Atelier guidé : le module Parcours

Compte une heure et demie. Tu vas modéliser le suivi d'un apprenant DevRoad.

1. Crée un dossier `parcours` avec un fichier `index.php` (`declare(strict_types=1);` en première ligne).
2. Crée l'enum `Niveau` (trois cas, avec une méthode `libelle()`).
3. Crée la classe `Lecon` avec un constructeur à propriétés promues : `titre`, `minutes`, `niveau` (de type `Niveau`). Mets les propriétés en `readonly`.
4. Crée la classe `Parcours` avec une propriété privée `array $lecons` et les méthodes `ajouter(Lecon $lecon): void`, `dureeTotale(): int` et `parNiveau(Niveau $n): array`.
5. Crée une interface `Exportable` avec `versTableau(): array` et fais-la implémenter par `Lecon` et `Parcours`.
6. Lève une `InvalidArgumentException` dans le constructeur de `Lecon` si `minutes` est inférieur ou égal à zéro.
7. Dans `index.php`, crée cinq leçons, ajoute-les à un parcours, affiche la durée totale et exporte le parcours en JSON.
8. Entoure la création d'une leçon invalide d'un `try / catch` et affiche le message d'erreur.
9. Essaie volontairement d'écrire `$lecon->minutes = 5;` et lis le message d'erreur de PHP.

Pour t'auto-évaluer : explique à voix haute pourquoi `Parcours::lecons` est privé, et ce que tu gagnes à passer par `ajouter()`.

## Erreurs fréquentes

- **Oublier `$this->`** pour accéder à une propriété dans une méthode : PHP cherche une variable locale.
- **Écrire `$objet.titre`** : en PHP, la flèche `->` remplace le point, et le point sert à concaténer.
- **Mettre toutes les propriétés en `public`** : l'encapsulation disparaît.
- **Appeler `new` sur une classe abstraite ou une interface** : erreur fatale.
- **Oublier d'appeler `parent::__construct()`** dans une classe fille qui définit son propre constructeur.
- **Abuser de l'héritage** : une hiérarchie de cinq niveaux est presque toujours un signe de mauvaise conception. Préfère la composition.
- **Attraper `Exception` partout** puis ignorer l'erreur : tu masques les bugs.

## Bonnes pratiques

- Une classe, une responsabilité, un nom au singulier en `PascalCase`.
- Propriétés `private` ou `readonly` par défaut, méthodes courtes et typées.
- Utilise la promotion de propriétés dans le constructeur.
- Remplace les chaînes magiques (`"beginner"`) par des `enum`.
- Préfère la **composition** (un objet qui en utilise un autre) à l'héritage.
- Programme vers des interfaces : tes fonctions acceptent `Exportable`, pas `Badge`.
- Fournis les dépendances par le constructeur au lieu de les créer à l'intérieur.
- Lève des exceptions explicites plutôt que de renvoyer `false` ou `null` en cas d'erreur.

## À retenir

- Une classe est un plan, un objet en est une instance ; `$this` désigne l'objet courant et `->` accède à ses membres.
- La visibilité (`public`, `protected`, `private`) permet d'encapsuler l'état et de garder le code cohérent.
- `readonly` et `enum` rendent les données immuables et les valeurs possibles limitées.
- `extends` crée une relation « est un », `implements` signe un contrat, `use` insère un trait.
- Les exceptions signalent les erreurs métier ; `try / catch / finally` les gère.
- L'injection de dépendances consiste à fournir à une classe ce dont elle a besoin plutôt que de le lui laisser créer.
