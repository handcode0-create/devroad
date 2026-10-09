---
title: Introduction à Spring Boot
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

**Spring Boot** est le framework Java le plus utilisé pour construire des API web et des services back-end. Il fournit un serveur intégré, une configuration automatique et un écosystème complet (accès aux bases de données, sécurité, tests). Là où tu écrirais des centaines de lignes de configuration, Spring Boot te permet de démarrer une API en quelques minutes.

À la fin du chapitre, tu seras capable de :

- créer un projet Spring Boot 3 avec Maven ;
- expliquer l'**injection de dépendances** et le rôle du conteneur Spring ;
- exposer une API REST avec `@RestController` ;
- structurer ton code en couches : contrôleur, service, dépôt ;
- valider les données reçues et gérer proprement les erreurs ;
- persister des données avec **Spring Data JPA** ;
- tester un contrôleur avec MockMvc.

Prérequis : les chapitres précédents, en particulier les interfaces, les records, les exceptions et JUnit. Savoir ce qu'est une requête HTTP (GET, POST) et du JSON t'aidera. Prévois trois heures. Il te faut un JDK 21 et Maven.

## Le problème que Spring résout

Dans une application, les objets collaborent : un contrôleur appelle un service, qui appelle un dépôt, qui parle à la base. Sans framework, tu les crées toi-même :

```java
var depot = new DepotRoadmap(new ConnexionBase("jdbc:..."));
var service = new ServiceRoadmap(depot);
var controleur = new ControleurRoadmap(service);
```

Chaque classe est **couplée** aux classes concrètes qu'elle utilise, ce qui complique les tests et le changement d'implémentation. Spring applique l'**inversion de contrôle** : tu ne crées plus les objets, tu déclares ce dont chaque classe a besoin, et le **conteneur** Spring les fabrique et les relie. Cette liaison s'appelle l'**injection de dépendances**.

## Créer le projet

Rends-toi sur start.spring.io, ou écris directement le `pom.xml`. Choisis Maven, Java 21, Spring Boot 3.x et les dépendances Web, Validation, Data JPA, H2 Database.

```xml
<parent>
  <groupId>org.springframework.boot</groupId>
  <artifactId>spring-boot-starter-parent</artifactId>
  <version>3.3.4</version>
</parent>

<properties>
  <java.version>21</java.version>
</properties>

<dependencies>
  <dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-web</artifactId>
  </dependency>
  <dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-validation</artifactId>
  </dependency>
  <dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-data-jpa</artifactId>
  </dependency>
  <dependency>
    <groupId>com.h2database</groupId>
    <artifactId>h2</artifactId>
    <scope>runtime</scope>
  </dependency>
  <dependency>
    <groupId>org.springframework.boot</groupId>
    <artifactId>spring-boot-starter-test</artifactId>
    <scope>test</scope>
  </dependency>
</dependencies>

<build>
  <plugins>
    <plugin>
      <groupId>org.springframework.boot</groupId>
      <artifactId>spring-boot-maven-plugin</artifactId>
    </plugin>
  </plugins>
</build>
```

Un **starter** est un lot de dépendances cohérentes : `spring-boot-starter-web` apporte Spring MVC, Jackson (JSON) et un serveur Tomcat intégré. Le `parent` gère les versions pour que tout soit compatible.

La classe principale démarre l'application :

```java
package fr.devroad.api;

import org.springframework.boot.SpringApplication;
import org.springframework.boot.autoconfigure.SpringBootApplication;

@SpringBootApplication
public class ApiApplication {
    public static void main(String[] args) {
        SpringApplication.run(ApiApplication.class, args);
    }
}
```

`@SpringBootApplication` active la **configuration automatique** et l'analyse des classes du paquet courant et de ses sous-paquets. Lance avec `mvn spring-boot:run` : le serveur écoute sur le port 8080.

## Les beans et l'injection de dépendances

Un **bean** est un objet géré par le conteneur Spring. Les annotations de stéréotype déclarent des beans : `@Component` (générique), `@Service` (logique métier), `@Repository` (accès aux données), `@RestController` (API).

```java
@Service
public class ServiceSalutation {
    public String saluer(String nom) {
        return "Bonjour " + nom;
    }
}

@RestController
public class SalutationController {

    private final ServiceSalutation service;

    public SalutationController(ServiceSalutation service) {   // injection par constructeur
        this.service = service;
    }

    @GetMapping("/salut")
    public String salut(@RequestParam(defaultValue = "monde") String nom) {
        return service.saluer(nom);
    }
}
```

Spring voit que le contrôleur a besoin d'un `ServiceSalutation`, l'instancie, et le passe au constructeur. Utilise toujours l'**injection par constructeur** : les dépendances sont explicites, les attributs peuvent être `final`, et tu peux instancier la classe dans un test sans Spring.

Teste dans un terminal :

```bash
curl "http://localhost:8080/salut?nom=Awa"
# Bonjour Awa
```

:::quiz
Quelle forme d'injection de dépendances est recommandée avec Spring ?
- [ ] L'injection sur un attribut privé avec reflexion
- [x] L'injection par constructeur
- [ ] La création manuelle avec new dans la méthode
- [ ] Un attribut static
> L'injection par constructeur rend les dépendances explicites, autorise les attributs final et facilite les tests unitaires sans conteneur Spring.
:::

## Une API REST en couches

Construisons une API pour gérer des roadmaps. L'architecture classique sépare les responsabilités :

1. le **contrôleur** reçoit la requête HTTP et renvoie la réponse ;
2. le **service** contient la logique métier ;
3. le **dépôt** (repository) accède à la base de données.

Chaque couche ne parle qu'à celle du dessous, ce qui rend le code testable et évolutif.

### L'entité et le dépôt

Une **entité JPA** est une classe liée à une table. Spring Data JPA génère l'implémentation du dépôt à partir d'une simple interface :

```java
package fr.devroad.api.roadmap;

import jakarta.persistence.Entity;
import jakarta.persistence.GeneratedValue;
import jakarta.persistence.GenerationType;
import jakarta.persistence.Id;

@Entity
public class Roadmap {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    private Long id;

    private String titre;
    private int minutes;

    protected Roadmap() {}   // requis par JPA

    public Roadmap(String titre, int minutes) {
        this.titre = titre;
        this.minutes = minutes;
    }

    public Long getId() { return id; }
    public String getTitre() { return titre; }
    public int getMinutes() { return minutes; }

    public void modifier(String titre, int minutes) {
        this.titre = titre;
        this.minutes = minutes;
    }
}
```

```java
import org.springframework.data.jpa.repository.JpaRepository;
import java.util.List;

public interface RoadmapRepository extends JpaRepository<Roadmap, Long> {

    List<Roadmap> findByTitreContainingIgnoreCase(String extrait);

    List<Roadmap> findByMinutesLessThanEqual(int minutes);
}
```

Sans écrire une seule ligne de SQL, tu obtiens `save`, `findById`, `findAll`, `deleteById`, `count`, etc. Les méthodes dont le nom suit une convention (`findByTitreContainingIgnoreCase`) sont **traduites automatiquement en requêtes**.

La base H2 en mémoire se configure dans `src/main/resources/application.properties` :

```bash
spring.datasource.url=jdbc:h2:mem:devroad
spring.jpa.hibernate.ddl-auto=update
spring.jpa.show-sql=true
spring.h2.console.enabled=true
server.port=8080
```

`ddl-auto=update` crée les tables au démarrage : pratique en développement, à proscrire en production, où l'on utilise des migrations (Flyway, Liquibase).

### Les DTO : ne jamais exposer l'entité

Expose des objets dédiés à l'API plutôt que tes entités. Les **records** conviennent parfaitement, et Bean Validation y pose les contraintes :

```java
import jakarta.validation.constraints.Min;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.Size;

public record RoadmapRequest(
        @NotBlank(message = "Le titre est obligatoire")
        @Size(max = 100, message = "100 caractères maximum")
        String titre,

        @Min(value = 1, message = "La durée doit être positive")
        int minutes) {}

public record RoadmapResponse(Long id, String titre, int minutes) {

    public static RoadmapResponse de(Roadmap r) {
        return new RoadmapResponse(r.getId(), r.getTitre(), r.getMinutes());
    }
}
```

Cela évite de dévoiler la structure interne de la base, et permet de faire évoluer l'API et le modèle indépendamment.

### Le service

```java
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;
import java.util.List;

@Service
public class RoadmapService {

    private final RoadmapRepository depot;

    public RoadmapService(RoadmapRepository depot) {
        this.depot = depot;
    }

    @Transactional(readOnly = true)
    public List<RoadmapResponse> lister() {
        return depot.findAll().stream().map(RoadmapResponse::de).toList();
    }

    @Transactional(readOnly = true)
    public RoadmapResponse trouver(Long id) {
        return depot.findById(id)
                .map(RoadmapResponse::de)
                .orElseThrow(() -> new RoadmapIntrouvableException(id));
    }

    @Transactional
    public RoadmapResponse creer(RoadmapRequest requete) {
        var roadmap = depot.save(new Roadmap(requete.titre(), requete.minutes()));
        return RoadmapResponse.de(roadmap);
    }

    @Transactional
    public RoadmapResponse modifier(Long id, RoadmapRequest requete) {
        var roadmap = depot.findById(id).orElseThrow(() -> new RoadmapIntrouvableException(id));
        roadmap.modifier(requete.titre(), requete.minutes());
        return RoadmapResponse.de(roadmap);
    }

    @Transactional
    public void supprimer(Long id) {
        if (!depot.existsById(id)) {
            throw new RoadmapIntrouvableException(id);
        }
        depot.deleteById(id);
    }
}
```

`@Transactional` regroupe les opérations dans une transaction de base de données : tout réussit, ou rien n'est enregistré. Dans `modifier`, l'entité est suivie par JPA, donc la modification est enregistrée sans appeler `save`.

### Le contrôleur

```java
import jakarta.validation.Valid;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.*;
import java.util.List;

@RestController
@RequestMapping("/api/roadmaps")
public class RoadmapController {

    private final RoadmapService service;

    public RoadmapController(RoadmapService service) {
        this.service = service;
    }

    @GetMapping
    public List<RoadmapResponse> lister() {
        return service.lister();
    }

    @GetMapping("/{id}")
    public RoadmapResponse trouver(@PathVariable Long id) {
        return service.trouver(id);
    }

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public RoadmapResponse creer(@Valid @RequestBody RoadmapRequest requete) {
        return service.creer(requete);
    }

    @PutMapping("/{id}")
    public RoadmapResponse modifier(@PathVariable Long id, @Valid @RequestBody RoadmapRequest requete) {
        return service.modifier(id, requete);
    }

    @DeleteMapping("/{id}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void supprimer(@PathVariable Long id) {
        service.supprimer(id);
    }
}
```

Les annotations traduisent HTTP en Java :

| Annotation | Rôle |
| --- | --- |
| `@GetMapping`, `@PostMapping`, `@PutMapping`, `@DeleteMapping` | associe une méthode à un verbe HTTP |
| `@PathVariable` | lit une partie de l'URL (`/api/roadmaps/3`) |
| `@RequestParam` | lit un paramètre de requête (`?q=java`) |
| `@RequestBody` | convertit le JSON reçu en objet |
| `@Valid` | déclenche la validation des contraintes |
| `@ResponseStatus` | fixe le code HTTP de la réponse |

Les valeurs de retour sont automatiquement converties en JSON par Jackson. Les codes HTTP sont soignés : **201** à la création, **204** à la suppression, **404** quand la ressource n'existe pas, **400** quand les données sont invalides.

## Gérer les erreurs globalement

Définis l'exception métier, puis un gestionnaire central qui la traduit en réponse HTTP propre :

```java
public class RoadmapIntrouvableException extends RuntimeException {
    public RoadmapIntrouvableException(Long id) {
        super("Roadmap introuvable : " + id);
    }
}
```

```java
import org.springframework.http.HttpStatus;
import org.springframework.http.ProblemDetail;
import org.springframework.web.bind.MethodArgumentNotValidException;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.RestControllerAdvice;
import java.util.stream.Collectors;

@RestControllerAdvice
public class GestionnaireErreurs {

    @ExceptionHandler(RoadmapIntrouvableException.class)
    public ProblemDetail introuvable(RoadmapIntrouvableException e) {
        return ProblemDetail.forStatusAndDetail(HttpStatus.NOT_FOUND, e.getMessage());
    }

    @ExceptionHandler(MethodArgumentNotValidException.class)
    public ProblemDetail invalide(MethodArgumentNotValidException e) {
        String detail = e.getBindingResult().getFieldErrors().stream()
                .map(f -> f.getField() + " : " + f.getDefaultMessage())
                .collect(Collectors.joining(" ; "));
        return ProblemDetail.forStatusAndDetail(HttpStatus.BAD_REQUEST, detail);
    }
}
```

`ProblemDetail` suit le standard RFC 7807 pour décrire les erreurs d'API. Le contrôleur reste propre : il lance des exceptions, le gestionnaire les transforme.

> **Astuce** : essaie ton API avec `curl`, par exemple `curl -X POST localhost:8080/api/roadmaps -H "Content-Type: application/json" -d '{"titre":"Java","minutes":180}'`.

:::quiz
Quel est le rôle de l'annotation @RestControllerAdvice combinée à @ExceptionHandler ?
- [ ] Elle démarre le serveur Tomcat
- [ ] Elle crée les tables en base de données
- [x] Elle centralise la traduction des exceptions en réponses HTTP
- [ ] Elle injecte les dépendances dans le contrôleur
> Un gestionnaire global intercepte les exceptions lancées par les contrôleurs et les services, et produit une réponse HTTP cohérente avec le bon code d'état.
:::

## Tester l'API

Spring Boot Test et MockMvc simulent des requêtes HTTP sans lancer de serveur réel :

```java
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.boot.test.autoconfigure.web.servlet.AutoConfigureMockMvc;
import org.springframework.boot.test.context.SpringBootTest;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;

import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.*;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.*;

@SpringBootTest
@AutoConfigureMockMvc
class RoadmapControllerTest {

    @Autowired
    private MockMvc mvc;

    @Test
    void creeUneRoadmap() throws Exception {
        mvc.perform(post("/api/roadmaps")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("""
                                {"titre": "Java", "minutes": 180}
                                """))
                .andExpect(status().isCreated())
                .andExpect(jsonPath("$.titre").value("Java"));
    }

    @Test
    void refuseUnTitreVide() throws Exception {
        mvc.perform(post("/api/roadmaps")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content("{\"titre\": \"\", \"minutes\": 10}"))
                .andExpect(status().isBadRequest());
    }

    @Test
    void renvoie404SiInconnue() throws Exception {
        mvc.perform(get("/api/roadmaps/999"))
                .andExpect(status().isNotFound());
    }
}
```

Pour les tests unitaires du service, tu n'as pas besoin de Spring : crée le service avec un dépôt simulé (Mockito, inclus dans le starter de test) et vérifie la logique métier seule.

## Configuration et profils

Les paramètres vivent dans `application.properties`, jamais dans le code. Pour séparer développement et production, crée `application-prod.properties` et active le profil au lancement :

```bash
java -jar target/api-1.0.0.jar --spring.profiles.active=prod
```

Les secrets (mots de passe de base, clés d'API) passent par des **variables d'environnement** : `spring.datasource.password=${DB_PASSWORD}`. Tu ne les commits jamais dans Git.

`mvn package` produit un JAR exécutable contenant le serveur : un seul fichier à déployer sur n'importe quel serveur doté de Java 21, ou à placer dans une image Docker.

## Atelier guidé : l'API des tâches

Compte deux heures. Crée un projet Spring Boot `api-taches` avec les dépendances ci-dessus.

1. Crée l'entité `Tache` avec `id`, `titre`, `terminee` (booléen) et `creeLe` (date).
2. Crée `TacheRepository` avec une méthode `findByTermineeOrderByCreeLeDesc(boolean terminee)`.
3. Crée les records `TacheRequest` (titre obligatoire, 3 à 80 caractères) et `TacheResponse`.
4. Écris `TacheService` avec `lister(Boolean terminee)`, `creer`, `terminer(Long id)` et `supprimer`.
5. Écris `TacheController` : `GET /api/taches` avec le paramètre optionnel `terminee`, `POST`, `PATCH /api/taches/{id}/terminer` et `DELETE`.
6. Ajoute `TacheIntrouvableException` et un `@RestControllerAdvice`.
7. Teste avec `curl` les quatre opérations et les cas d'erreur (titre vide, id inconnu).
8. Écris trois tests MockMvc : création valide, titre trop court, tâche introuvable.
9. Active la console H2 et vérifie la table créée par Hibernate.

Pour t'auto-évaluer : explique la différence entre contrôleur, service et dépôt, et dis pourquoi un DTO protège ton API.

## Erreurs fréquentes

- **Classe principale dans un mauvais paquet.** Spring n'analyse que son paquet et ses sous-paquets : les beans situés ailleurs restent introuvables.
- **Oublier l'annotation de stéréotype.** Sans `@Service` ou `@Component`, aucun bean n'est créé, et l'injection échoue au démarrage.
- **Exposer directement l'entité JPA.** Cela fuit des champs internes et crée des problèmes de sérialisation.
- **Oublier `@Valid`.** Les contraintes sont ignorées sans lui.
- **Constructeur sans paramètre manquant sur une entité.** JPA en a besoin (il peut être `protected`).
- **Mettre de la logique métier dans le contrôleur.** Il doit rester fin ; la logique va dans le service.
- **`ddl-auto=update` en production.** Utilise des migrations versionnées.

## Bonnes pratiques

- Injection par constructeur, attributs `final`, une classe par responsabilité.
- Un paquet par fonctionnalité (`roadmap`, `utilisateur`) plutôt qu'un paquet par couche technique quand le projet grandit.
- DTO en records, validation à l'entrée de l'API.
- Codes HTTP corrects et erreurs homogènes via `ProblemDetail`.
- Configuration externalisée, secrets en variables d'environnement.
- Teste les services sans Spring, les contrôleurs avec MockMvc.
- Versionne l'API dans l'URL dès qu'elle est publique (`/api/v1/...`).

## À retenir

- Spring Boot fournit un serveur intégré, la configuration automatique et des **starters** de dépendances.
- Le conteneur Spring crée les **beans** et les relie par **injection de dépendances**, de préférence par constructeur.
- L'architecture en couches sépare contrôleur, service et dépôt.
- Spring Data JPA génère les dépôts à partir d'une interface ; les entités sont mappées sur des tables.
- Les DTO en records et Bean Validation protègent et valident les entrées ; `@RestControllerAdvice` centralise les erreurs.
- MockMvc teste l'API sans serveur réel ; `mvn package` produit un JAR exécutable.
