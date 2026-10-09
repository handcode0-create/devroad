---
title: Programmation orientée objet
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Tu sais déjà stocker des données dans des dictionnaires et les traiter avec des fonctions. Cela fonctionne bien jusqu'au moment où un programme manipule des dizaines de « choses » (clients, comptes, commandes) qui ont chacune des données **et** des comportements. La programmation orientée objet (POO) regroupe les deux au même endroit : c'est la façon dont sont construits Django, FastAPI (via Pydantic) et la plupart des bibliothèques Python.

À la fin du chapitre, tu seras capable de :

- définir une **classe** avec `__init__`, des attributs et des méthodes ;
- créer des **instances** et comprendre le rôle de `self` ;
- distinguer attributs d'instance et attributs de classe ;
- utiliser les **propriétés** (`@property`) pour contrôler l'accès aux données ;
- réutiliser du code avec l'**héritage** et le **polymorphisme** ;
- personnaliser tes objets avec les méthodes spéciales `__repr__`, `__str__` et `__eq__` ;
- simplifier tes classes de données avec `@dataclass`.

Prérequis : les chapitres 1 à 4, surtout les fonctions et les dictionnaires. Prévois deux heures trente.

## Pourquoi des classes ?

Représentons un compte Mobile Money avec un dictionnaire :

```python
compte = {"titulaire": "Aminata", "solde": 25000}

def deposer(compte, montant):
    compte["solde"] += montant
```

Rien n'empêche quelqu'un d'écrire `compte["solde"] = -9999` ou d'oublier la clé `"titulaire"`. Les données et les règles qui les protègent sont séparées. Une **classe** les réunit :

```python
class CompteMobileMoney:
    def __init__(self, titulaire: str, solde: int = 0):
        self.titulaire = titulaire
        self.solde = solde

    def deposer(self, montant: int) -> None:
        if montant <= 0:
            raise ValueError("Le montant doit être positif")
        self.solde += montant

    def retirer(self, montant: int) -> None:
        if montant > self.solde:
            raise ValueError("Solde insuffisant")
        self.solde -= montant
```

Une classe est un **plan de construction**. Un **objet** (ou instance) est une construction réalisée à partir de ce plan :

```python
compte_a = CompteMobileMoney("Aminata", 25000)
compte_b = CompteMobileMoney("Yao")

compte_a.deposer(5000)
compte_a.retirer(10000)
print(compte_a.solde)   # 20000
print(compte_b.solde)   # 0
```

Chaque instance possède ses propres données : modifier `compte_a` ne touche pas `compte_b`.

## Le constructeur et le paramètre self

Deux éléments déroutent souvent les débutants.

La méthode `__init__` est le **constructeur** : Python l'appelle automatiquement quand tu écris `CompteMobileMoney(...)`. Elle initialise les attributs de l'objet.

Le paramètre `self` représente **l'objet courant**. Quand tu écris `compte_a.deposer(5000)`, Python exécute en réalité `CompteMobileMoney.deposer(compte_a, 5000)` : `self` reçoit `compte_a`. C'est pourquoi chaque méthode d'instance prend `self` en premier paramètre, et pourquoi on écrit `self.solde` pour atteindre une donnée de l'objet.

> **Erreur fréquente** : oublier `self.` devant un attribut. Écrire `solde = solde` dans `__init__` crée une simple variable locale qui disparaît à la fin de la méthode, l'objet n'a alors pas d'attribut.

:::quiz
Dans `compte.deposer(5000)`, que représente `self` à l'intérieur de la méthode ?
- [ ] La classe `CompteMobileMoney`
- [ ] Le nombre 5000
- [x] L'objet `compte` sur lequel la méthode est appelée
- [ ] Le module courant
> Python passe automatiquement l'instance comme premier argument. `self` désigne donc l'objet précis sur lequel on travaille.
:::

## Attributs de classe et d'instance

Un attribut défini dans `__init__` appartient à **chaque instance**. Un attribut défini directement dans le corps de la classe est **partagé** par toutes les instances :

```python
class CompteMobileMoney:
    plafond_retrait = 500000      # attribut de classe : le même pour tous
    nombre_comptes = 0

    def __init__(self, titulaire: str, solde: int = 0):
        self.titulaire = titulaire    # attribut d'instance : propre à chaque compte
        self.solde = solde
        CompteMobileMoney.nombre_comptes += 1
```

Les attributs de classe conviennent pour des constantes (taux, plafonds) et des compteurs. Pour tout ce qui varie d'un objet à l'autre, utilise des attributs d'instance.

> **Attention** : n'utilise jamais une liste ou un dictionnaire comme attribut de classe pour stocker des données propres à chaque objet. Tous les objets partageraient la même liste. Crée-la dans `__init__` avec `self.historique = []`.

## Protéger les données avec les propriétés

Python n'a pas d'attributs vraiment « privés », mais il a une convention : un nom qui commence par un underscore (`_solde`) signale « usage interne, ne touche pas directement ». Pour exposer la valeur de manière contrôlée, on utilise le décorateur `@property` :

```python
class CompteMobileMoney:
    def __init__(self, titulaire: str, solde: int = 0):
        self.titulaire = titulaire
        self._solde = solde

    @property
    def solde(self) -> int:
        return self._solde

    @solde.setter
    def solde(self, valeur: int) -> None:
        if valeur < 0:
            raise ValueError("Le solde ne peut pas être négatif")
        self._solde = valeur
```

Pour l'extérieur, rien ne change : on écrit toujours `compte.solde` pour lire et `compte.solde = 100` pour modifier. Mais la validation s'exécute à chaque affectation. Une propriété sans `setter` est en lecture seule, parfait pour des valeurs calculées.

## Méthodes spéciales

Les noms entourés de doubles underscores (« dunder ») permettent à tes objets de se comporter comme les types natifs. Les trois premières à connaître :

```python
class CompteMobileMoney:
    def __init__(self, titulaire: str, solde: int = 0):
        self.titulaire = titulaire
        self.solde = solde

    def __repr__(self) -> str:
        return f"CompteMobileMoney(titulaire={self.titulaire!r}, solde={self.solde})"

    def __str__(self) -> str:
        return f"{self.titulaire} : {self.solde} FCFA"

    def __eq__(self, autre: object) -> bool:
        if not isinstance(autre, CompteMobileMoney):
            return NotImplemented
        return (self.titulaire, self.solde) == (autre.titulaire, autre.solde)
```

- `__repr__` est destiné au développeur : il s'affiche dans la console interactive et les messages d'erreur. Idéalement, il ressemble au code qui recrée l'objet.
- `__str__` est destiné à l'utilisateur : c'est ce que renvoie `print(compte)`.
- `__eq__` définit ce que signifie `==` pour tes objets. Sans elle, deux comptes identiques sont considérés comme différents.

## L'héritage

Un compte épargne ressemble à un compte normal, avec en plus un taux d'intérêt. Plutôt que de copier tout le code, on **hérite** :

```python
class CompteEpargne(CompteMobileMoney):
    def __init__(self, titulaire: str, solde: int = 0, taux: float = 0.03):
        super().__init__(titulaire, solde)
        self.taux = taux

    def appliquer_interets(self) -> None:
        self.solde += int(self.solde * self.taux)

    def retirer(self, montant: int) -> None:
        if montant > 100000:
            raise ValueError("Retrait limité à 100 000 FCFA sur un compte épargne")
        super().retirer(montant)
```

Que se passe-t-il ici ?

- `CompteEpargne(CompteMobileMoney)` : la classe fille **reçoit** tous les attributs et méthodes de la classe mère.
- `super()` donne accès à la classe mère : on l'utilise pour réutiliser son `__init__` ou une méthode qu'on redéfinit.
- `retirer` est **redéfinie** (*override*) : on ajoute une règle, puis on délègue le travail restant à la version de la mère.

Pour vérifier les liens entre classes, Python offre `isinstance(objet, Classe)` et `issubclass(Fille, Mere)`.

### Le polymorphisme

Comme `CompteEpargne` est aussi un `CompteMobileMoney`, on peut les traiter de la même façon :

```python
comptes = [CompteMobileMoney("Yao", 8000), CompteEpargne("Aminata", 50000)]

for compte in comptes:
    compte.retirer(1000)      # chaque classe applique sa propre version
    print(compte)
```

Le code appelant n'a pas besoin de savoir de quel type exact est chaque compte : il appelle `retirer` et la bonne méthode s'exécute. C'est le **polymorphisme**, et il évite des cascades de `if type(...) == ...`.

> **Astuce** : choisis l'héritage quand la relation se dit « est un » (un compte épargne **est un** compte). Si la relation se dit « a un » (un compte **a un** historique), utilise la **composition** : l'historique est un objet stocké dans un attribut.

:::quiz
Quand préférer la composition à l'héritage ?
- [ ] Quand on veut gagner quelques lignes de code
- [ ] Quand les deux classes ont exactement les mêmes méthodes
- [x] Quand la relation est de type « a un » plutôt que « est un »
- [ ] Python interdit l'héritage dans ce cas
> L'héritage modélise une spécialisation (« est un »). Pour une simple possession ou collaboration (« a un »), on place un objet dans un attribut, ce qui garde les classes indépendantes.
:::

## Les dataclasses

Beaucoup de classes ne servent qu'à transporter des données et obligent à écrire `__init__`, `__repr__` et `__eq__` à la main. Le décorateur `@dataclass` génère tout cela :

```python
from dataclasses import dataclass, field


@dataclass
class Produit:
    nom: str
    prix: int
    quantite: int = 0
    tags: list[str] = field(default_factory=list)

    @property
    def valeur(self) -> int:
        return self.prix * self.quantite


p = Produit("Attiéké", 500, 12)
print(p)            # Produit(nom='Attiéké', prix=500, quantite=12, tags=[])
print(p.valeur)     # 6000
print(p == Produit("Attiéké", 500, 12))   # True
```

Trois points à noter : les annotations de type définissent les champs, une valeur par défaut les rend facultatifs, et pour un champ mutable (liste, dictionnaire) on utilise `field(default_factory=list)` pour que chaque objet ait sa propre liste. Avec `@dataclass(frozen=True)`, l'objet devient immuable.

## Atelier guidé : une mini-boutique orientée objet

Compte une heure et demie.

1. Crée un fichier `boutique.py` avec une dataclass `Produit` (nom, prix en FCFA, stock).
2. Ajoute une méthode `retirer_stock(quantite)` qui lève `ValueError` si le stock est insuffisant.
3. Crée une classe `LignePanier` (produit, quantité) avec une propriété `sous_total`.
4. Crée une classe `Panier` qui contient une liste de lignes (créée dans `__init__`) et des méthodes `ajouter(produit, quantite)` et `total()`.
5. Ajoute `__len__` au panier pour que `len(panier)` retourne le nombre de lignes.
6. Ajoute `__str__` pour afficher un récapitulatif lisible avec le total en FCFA.
7. Crée une classe `PanierPromo` qui hérite de `Panier` et applique 10 % de remise au-delà de 50 000 FCFA, en réutilisant `super().total()`.
8. Écris une boucle qui traite un panier normal et un panier promo avec le même code, et observe le polymorphisme.
9. Fais en sorte que `Panier.ajouter` refuse une quantité négative ou nulle.

Pour t'auto-évaluer : explique à voix haute pourquoi `liste_lignes = []` dans le corps de la classe serait une erreur, et ce qui change entre `__str__` et `__repr__`.

## Erreurs fréquentes

- **Oublier `self` dans la signature d'une méthode.** Python lève un `TypeError` sur le nombre d'arguments.
- **Oublier `self.` devant un attribut.** La valeur reste dans une variable locale.
- **Utiliser une liste en attribut de classe.** Elle est partagée entre toutes les instances.
- **Oublier `super().__init__(...)`.** Les attributs de la classe mère ne sont jamais créés.
- **Utiliser une valeur par défaut mutable** (`def f(x=[])` ou un champ de dataclass `= []`). Passe par `None` ou `default_factory`.
- **Abuser de l'héritage.** Des hiérarchies de quatre niveaux deviennent illisibles : préfère la composition.
- **Comparer des objets avec `==` sans `__eq__`.** Le résultat est `False` même pour des données identiques.

## Bonnes pratiques

- Nomme les classes en `PascalCase` et les méthodes en `snake_case`.
- Une classe, une responsabilité : si tu l'expliques avec un « et », découpe.
- Valide les données à la frontière (`__init__`, propriétés) pour ne jamais stocker un état invalide.
- Définis toujours `__repr__` : il te fera gagner un temps précieux au débogage.
- Utilise `@dataclass` pour les objets qui portent surtout des données.
- Annote les types des paramètres et des retours : ton éditeur t'aidera davantage.
- Privilégie la composition quand tu hésites avec l'héritage.

## À retenir

- Une classe regroupe **données et comportements** ; une instance est un objet créé depuis la classe.
- `__init__` initialise l'objet, `self` désigne l'objet courant.
- Les attributs de classe sont partagés, les attributs d'instance sont propres à chaque objet.
- `@property` permet de contrôler la lecture et l'écriture d'un attribut sans changer la syntaxe.
- L'héritage exprime « est un », `super()` réutilise la classe mère, le polymorphisme permet de traiter des objets variés de la même façon.
- `__repr__`, `__str__` et `__eq__` personnalisent le comportement des objets.
- `@dataclass` supprime le code répétitif des classes de données.
