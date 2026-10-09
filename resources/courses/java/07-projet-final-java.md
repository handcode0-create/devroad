---
title: Projet final Java
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as vu le langage, la programmation objet, les collections, les streams, les exceptions, les tests et Spring Boot. Il est temps de tout assembler dans un **vrai projet**, de la conception à la livraison. Tu vas construire **DevTrack**, une API REST qui permet à des apprenants de suivre leur progression dans des parcours de formation.

À la fin du projet, tu seras capable de :

- traduire un besoin en spécifications et en modèle de données ;
- structurer une application Spring Boot en couches propres ;
- appliquer la validation, la gestion d'erreurs et une logique métier non triviale ;
- écrire des tests unitaires et d'intégration crédibles ;
- documenter, empaqueter et présenter ton travail comme un livrable professionnel.

Prérequis : les six chapitres précédents. Prévois **six heures**, réparties sur plusieurs sessions. Ce projet peut aller dans ton portfolio : soigne l'historique Git, le README et les tests.

## Le contexte

Une plateforme de formation (comme DevRoad) propose des **parcours**, composés de **modules**. Chaque apprenant peut s'inscrire à un parcours et marquer des modules comme terminés. L'équipe pédagogique veut une API qui répond à ces questions : où en est cet apprenant ? Quels sont les parcours les plus suivis ? Quels apprenants ont terminé un parcours ?

Tu joues le rôle du développeur back-end chargé de livrer la première version.

## Spécifications fonctionnelles

### Entités

| Entité | Attributs principaux |
| --- | --- |
| **Parcours** | id, titre (unique), description, niveau (DEBUTANT, INTERMEDIAIRE, PROFESSIONNEL) |
| **Module** | id, titre, position dans le parcours, durée en minutes, parcours |
| **Apprenant** | id, nom, email (unique), date d'inscription |
| **Inscription** | id, apprenant, parcours, date d'inscription |
| **Progression** | inscription, module, date de complétion |

### Règles métier

- un parcours doit avoir un titre unique de 3 à 80 caractères ;
- la position d'un module est unique au sein de son parcours ;
- la durée d'un module est comprise entre 5 et 240 minutes ;
- l'email d'un apprenant doit être valide et unique ;
- un apprenant ne peut pas s'inscrire deux fois au même parcours ;
- on ne peut marquer comme terminé qu'un module d'un parcours auquel l'apprenant est inscrit ;
- marquer deux fois le même module est une opération **idempotente** (aucune erreur, aucun doublon) ;
- le **pourcentage de progression** est le temps des modules terminés divisé par le temps total du parcours, arrondi à l'entier ;
- un parcours sans module a une progression de 0 %.

### Endpoints attendus

| Méthode et chemin | Description | Codes |
| --- | --- | --- |
| `POST /api/parcours` | créer un parcours | 201, 400, 409 |
| `GET /api/parcours` | lister, filtre optionnel `niveau` | 200 |
| `GET /api/parcours/{id}` | détail avec ses modules | 200, 404 |
| `POST /api/parcours/{id}/modules` | ajouter un module | 201, 400, 404, 409 |
| `POST /api/apprenants` | créer un apprenant | 201, 400, 409 |
| `POST /api/apprenants/{id}/inscriptions` | s'inscrire à un parcours | 201, 404, 409 |
| `PUT /api/apprenants/{id}/modules/{moduleId}/termine` | marquer un module terminé | 200, 404, 409 |
| `GET /api/apprenants/{id}/progression` | progression par parcours | 200, 404 |
| `GET /api/statistiques/parcours` | nombre d'inscrits et taux de complétion par parcours | 200 |

### Contraintes techniques

- Java 21, Maven, Spring Boot 3, Spring Data JPA, base H2 en développement ;
- DTO sous forme de **records**, jamais d'entité exposée ;
- erreurs au format `ProblemDetail` ;
- au moins **15 tests** dont des tests unitaires de services et des tests MockMvc ;
- un fichier `README.md` avec instructions de lancement et exemples `curl`.

## Architecture proposée

Organise le code **par fonctionnalité** :

```bash
src/main/java/fr/devroad/devtrack/
├── DevTrackApplication.java
├── parcours/     (Parcours, Module, Niveau, repositories, service, controller, DTO)
├── apprenant/    (Apprenant, Inscription, service, controller, DTO)
├── progression/  (Progression, ProgressionService, controller, calculs)
├── statistiques/ (StatistiquesService, controller)
└── commun/       (GestionnaireErreurs, exceptions métier)
```

Le diagramme des relations est simple : un `Parcours` a plusieurs `Module` ; un `Apprenant` a plusieurs `Inscription` ; chaque `Inscription` relie un apprenant à un parcours et possède plusieurs `Progression`, chacune pointant un `Module`.

## Étape 1 : le modèle

Commence par les entités et les relations. Un exemple pour `Parcours` et `Module` :

```java
@Entity
public class Parcours {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    @Column(nullable = false, unique = true, length = 80)
    private String titre;

    private String description;

    @Enumerated(EnumType.STRING)
    private Niveau niveau;

    @OneToMany(mappedBy = "parcours", cascade = CascadeType.ALL, orphanRemoval = true)
    @OrderBy("position ASC")
    private List<Module> modules = new ArrayList<>();

    protected Parcours() {}

    public Parcours(String titre, String description, Niveau niveau) {
        this.titre = titre;
        this.description = description;
        this.niveau = niveau;
    }

    public void ajouterModule(Module module) {
        modules.add(module);
        module.setParcours(this);
    }

    public int dureeTotale() {
        return modules.stream().mapToInt(Module::getMinutes).sum();
    }

    // getters
}
```

Précisions importantes :

- stocke les enums avec `EnumType.STRING` : avec les valeurs ordinales, insérer une valeur au milieu corrompt les données ;
- pose des contraintes d'unicité en base (`unique = true`, `@Table(uniqueConstraints = ...)`) en plus des vérifications du service ;
- ajoute une contrainte d'unicité sur le couple (parcours, position) pour `Module`, et sur (apprenant, parcours) pour `Inscription`.

## Étape 2 : le cœur métier

La logique de progression est le morceau le plus intéressant. Isole-la dans une méthode pure, facile à tester sans base de données :

```java
public final class CalculProgression {

    private CalculProgression() {}

    public static int pourcentage(List<Module> modules, Set<Long> idsTermines) {
        int total = modules.stream().mapToInt(Module::getMinutes).sum();
        if (total == 0) {
            return 0;
        }
        int fait = modules.stream()
                .filter(m -> idsTermines.contains(m.getId()))
                .mapToInt(Module::getMinutes)
                .sum();
        return Math.round(fait * 100f / total);
    }
}
```

Ensuite, le service qui marque un module terminé doit respecter l'idempotence et les règles d'inscription :

```java
@Transactional
public ProgressionResponse terminerModule(Long apprenantId, Long moduleId) {
    var module = moduleRepository.findById(moduleId)
            .orElseThrow(() -> new RessourceIntrouvableException("Module", moduleId));

    var inscription = inscriptionRepository
            .findByApprenantIdAndParcoursId(apprenantId, module.getParcours().getId())
            .orElseThrow(() -> new RegleMetierException(
                    "L'apprenant n'est pas inscrit au parcours de ce module"));

    if (!progressionRepository.existsByInscriptionIdAndModuleId(inscription.getId(), moduleId)) {
        progressionRepository.save(new Progression(inscription, module, LocalDate.now()));
    }
    return calculer(inscription);
}
```

Observe l'ordre des vérifications : existence de la ressource (404), règle métier (409), puis action. Définis `RessourceIntrouvableException` et `RegleMetierException` dans le paquet `commun` et traduis-les dans le `@RestControllerAdvice`.

## Étape 3 : les statistiques avec les streams

Pour les statistiques, une requête JPA ou un traitement en mémoire avec les streams convient à cette taille de données. Voici une version avec streams, à comparer ensuite à une requête `@Query` :

```java
public List<StatistiqueParcours> statistiques() {
    return parcoursRepository.findAll().stream()
            .map(p -> {
                var inscriptions = inscriptionRepository.findByParcoursId(p.getId());
                long termines = inscriptions.stream()
                        .filter(i -> estComplet(i, p))
                        .count();
                int taux = inscriptions.isEmpty() ? 0
                        : (int) Math.round(termines * 100.0 / inscriptions.size());
                return new StatistiqueParcours(p.getTitre(), inscriptions.size(), taux);
            })
            .sorted(Comparator.comparingInt(StatistiqueParcours::inscrits).reversed())
            .toList();
}

public record StatistiqueParcours(String titre, int inscrits, int tauxCompletion) {}
```

> **Attention** : ce code déclenche une requête par parcours (le problème « N+1 »). C'est acceptable pour ce projet, mais note-le dans ton README et propose une amélioration (requête agrégée avec `@Query`, ou `JOIN FETCH`).

:::quiz
Pourquoi stocker un enum JPA avec EnumType.STRING plutôt qu'avec les valeurs ordinales ?
- [ ] Les chaînes sont plus rapides à lire
- [x] Insérer ou réordonner des valeurs dans l'enum ne corrompt pas les données existantes
- [ ] JPA interdit les ordinaux
- [ ] Cela réduit la taille de la base
> Les ordinaux dépendent de la position des constantes dans l'enum. Ajouter une valeur au milieu change le sens des données déjà enregistrées, alors que le nom reste stable.
:::

## Étape 4 : les erreurs et la validation

Tous les endpoints doivent renvoyer des erreurs homogènes. Étends le gestionnaire du chapitre précédent :

```java
@RestControllerAdvice
public class GestionnaireErreurs {

    @ExceptionHandler(RessourceIntrouvableException.class)
    public ProblemDetail introuvable(RessourceIntrouvableException e) {
        return ProblemDetail.forStatusAndDetail(HttpStatus.NOT_FOUND, e.getMessage());
    }

    @ExceptionHandler(RegleMetierException.class)
    public ProblemDetail conflit(RegleMetierException e) {
        return ProblemDetail.forStatusAndDetail(HttpStatus.CONFLICT, e.getMessage());
    }

    @ExceptionHandler(DataIntegrityViolationException.class)
    public ProblemDetail integrite(DataIntegrityViolationException e) {
        return ProblemDetail.forStatusAndDetail(HttpStatus.CONFLICT,
                "Cette donnée existe déjà ou viole une contrainte");
    }
}
```

Et la validation dans les DTO :

```java
public record ApprenantRequest(
        @NotBlank @Size(max = 80) String nom,
        @NotBlank @Email String email) {}

public record ModuleRequest(
        @NotBlank @Size(max = 100) String titre,
        @Min(1) int position,
        @Min(5) @Max(240) int minutes) {}
```

## Étape 5 : les tests

Vise trois niveaux de tests :

1. **Tests unitaires purs** : `CalculProgression` (liste vide, tout terminé, mi-parcours, durées inégales) sans aucun Spring ;
2. **Tests de service** avec dépôts simulés par Mockito : idempotence de `terminerModule`, refus d'une double inscription, apprenant non inscrit ;
3. **Tests d'API** avec MockMvc : codes HTTP, corps JSON et messages d'erreur.

Un exemple de test unitaire :

```java
class CalculProgressionTest {

    private Module module(long id, int minutes) {
        var m = new Module("Module " + id, (int) id, minutes);
        ReflectionTestUtils.setField(m, "id", id);
        return m;
    }

    @Test
    void renvoieZeroSansModule() {
        assertEquals(0, CalculProgression.pourcentage(List.of(), Set.of()));
    }

    @Test
    void pondereParLaDuree() {
        var modules = List.of(module(1, 30), module(2, 90));
        assertEquals(25, CalculProgression.pourcentage(modules, Set.of(1L)));
    }

    @Test
    void renvoieCentQuandToutEstTermine() {
        var modules = List.of(module(1, 30), module(2, 90));
        assertEquals(100, CalculProgression.pourcentage(modules, Set.of(1L, 2L)));
    }
}
```

Pour les tests d'intégration, prépare un jeu de données dans une méthode `@BeforeEach`, et nettoie la base entre les tests pour qu'ils restent indépendants.

## Livrables et critères d'acceptation

Ton projet est terminé quand **tous** les points ci-dessous sont vrais.

Fonctionnalités :

- les neuf endpoints répondent avec les codes HTTP indiqués dans le tableau ;
- un parcours et un apprenant avec un titre ou un email déjà utilisé renvoient 409 ;
- un module hors parcours d'inscription renvoie 409 ;
- marquer deux fois un module terminé ne crée pas de doublon et renvoie 200 ;
- le pourcentage de progression est pondéré par la durée des modules ;
- le filtre `niveau` fonctionne et un niveau inconnu renvoie 400.

Qualité du code :

- aucune entité n'est exposée en JSON ;
- les contrôleurs ne contiennent aucune logique métier ;
- l'injection de dépendances se fait par constructeur ;
- aucune exception n'est avalée dans un `catch` vide ;
- les noms de classes, méthodes et paquets sont cohérents et explicites.

Tests et livraison :

- au moins 15 tests, tous verts avec `mvn test` ;
- `mvn package` produit un JAR qui démarre avec `java -jar` ;
- le README décrit le projet, le lancement, les endpoints et trois exemples `curl` ;
- l'historique Git comporte des commits clairs (au moins un par étape).

## Atelier guidé : plan de réalisation

Voici l'ordre conseillé, avec le temps indicatif de chaque étape.

1. **(20 min)** Génère le projet sur start.spring.io, crée la structure de paquets, ajoute `.gitignore` et fais le premier commit.
2. **(40 min)** Écris les entités, l'enum `Niveau`, les dépôts et les contraintes d'unicité. Lance l'application et vérifie les tables dans la console H2.
3. **(40 min)** Implémente les endpoints de parcours et de modules (création, liste, détail) avec leurs DTO et leur validation.
4. **(30 min)** Implémente les apprenants et les inscriptions, avec les règles d'unicité et les codes 409.
5. **(40 min)** Écris `CalculProgression` en TDD : commence par les tests, puis le code.
6. **(40 min)** Implémente `terminerModule` et l'endpoint de progression, avec l'idempotence.
7. **(30 min)** Ajoute les statistiques et le tri par nombre d'inscrits.
8. **(30 min)** Centralise toutes les erreurs dans `GestionnaireErreurs` et vérifie chaque code HTTP du tableau avec `curl`.
9. **(50 min)** Complète les tests jusqu'à atteindre les 15 tests et les critères d'acceptation.
10. **(40 min)** Rédige le README, relis le code, supprime le code mort et fais un dernier passage sur les noms.

Pour t'auto-évaluer, repasse sur la checklist d'acceptation, puis réponds à voix haute : pourquoi l'idempotence est-elle utile pour une API ? Qu'est-ce que le problème N+1 ? Pourquoi tester `CalculProgression` sans Spring ? Si une réponse est floue, relis la section correspondante.

### Pistes pour aller plus loin

- remplace H2 par PostgreSQL avec Docker Compose et des migrations Flyway ;
- ajoute une authentification avec Spring Security et des jetons JWT ;
- pagine les listes avec `Pageable` ;
- documente l'API avec OpenAPI et Swagger UI ;
- publie le projet sur GitHub avec une intégration continue qui lance `mvn test`.

## Erreurs fréquentes

- **Commencer par les controllers.** Sans modèle ni règles claires, tu réécris tout. Pars du domaine.
- **Dupliquer les règles dans plusieurs couches.** Centralise-les dans le service, et garde la base comme filet de sécurité avec des contraintes.
- **Oublier les cas limites.** Parcours sans module, division par zéro, liste vide.
- **Tests qui dépendent de l'ordre d'exécution.** Nettoie les données avant chaque test.
- **Renvoyer 500 pour une erreur utilisateur.** Une entrée invalide doit donner 400 ou 409, jamais une erreur serveur.
- **Boucles de sérialisation JSON.** Exposer des entités avec relations bidirectionnelles fait boucler Jackson : utilise des DTO.
- **README absent ou vague.** Un projet impossible à lancer en cinq minutes perd son intérêt pour un recruteur.

## Bonnes pratiques

- Avance par petits incréments : une fonctionnalité, ses tests, un commit.
- Écris le test avant le code pour la logique métier complexe.
- Garde les méthodes courtes et les noms parlants ; extrais la logique pure en classes statiques ou en objets simples.
- Valide aux frontières (DTO) et protège l'intégrité en base (contraintes).
- Journalise les événements utiles sans jamais écrire de données sensibles.
- Relis ton code comme si c'était celui d'un collègue : y a-t-il un nom ambigu, une méthode trop longue, un test manquant ?
- Mesure : lance `mvn test` souvent, et ne fusionne jamais du code qui ne passe pas.

## À retenir

- Un projet réussi commence par des **spécifications claires**, un modèle de données et des règles métier explicites.
- Une architecture **en couches** et **par fonctionnalité** garde le code lisible et testable.
- La logique métier pure (comme le calcul de progression) se teste sans framework.
- Une API professionnelle renvoie des codes HTTP justes et des erreurs homogènes.
- Les contraintes de base de données complètent, sans les remplacer, les validations du code.
- Tests, README et historique Git font partie du livrable autant que le code.
- Tu sais désormais passer d'un besoin à une API Java complète, testée et prête à être présentée.
