# Parcours professeur — contrat backend

Le backend est complet et testé (`tests/Feature/TeacherPathTest.php`). Les pages `Teacher/Dashboard`, `Teacher/GroupShow` et `Groups/Index` sont des **pages provisoires** : remplace-les par tes maquettes, les props ci-dessous restent le contrat.

## Principes
- Un compte a un rôle : `student` (défaut) ou `teacher` (`auth.user.role`, déjà partagé par Inertia). Le rôle n'est jamais mass-assignable.
- Un professeur crée des **groupes**. Chaque groupe a un **code à 8 caractères** (sans 0/O/1/I/L). Les élèves le saisissent pour rejoindre.
- DevRoad reste un outil d'apprentissage : pas de notes, pas de diplôme, pas de certificat. Le professeur suit, il ne note pas.
- Confidentialité : le professeur voit **nom, progression, dernière activité, leçon en cours**. Jamais l'email, les mémos, le code DevLab ni la clé IA. L'élève est informé (`shared_with_teacher`).

## Routes
| Méthode | URL | Nom | Qui |
|---|---|---|---|
| POST | `/register` (champ `account_type` = `teacher`) | — | public |
| POST | `/teacher/activate` | `teacher.activate` | tout utilisateur sans groupe d'élève |
| GET | `/teacher` | `teacher.dashboard` | professeur |
| POST | `/teacher/groups` (`name`, `technology?`) | `teacher.groups.store` | professeur |
| GET | `/teacher/groups/{group}` | `teacher.groups.show` | propriétaire |
| PATCH | `/teacher/groups/{group}` (`name?`, `technology?`, `archived?`) | `teacher.groups.update` | propriétaire |
| POST | `/teacher/groups/{group}/code` | `teacher.groups.code` | propriétaire (invalide l'ancien code) |
| DELETE | `/teacher/groups/{group}` | `teacher.groups.destroy` | propriétaire |
| DELETE | `/teacher/groups/{group}/students/{student}` | `teacher.groups.students.destroy` | propriétaire |
| GET | `/groups` | `groups.index` | élève |
| POST | `/groups/join` (`code`) | `groups.join` | élève (10/min) |
| DELETE | `/groups/{group}/leave` | `groups.leave` | élève |

## Props
`Teacher/Dashboard` : `groups[]` = `{id, name, technology, join_code, archived, students_count, created_at}`, `technologies[]`.

`Teacher/GroupShow` : `group` (même forme), `technologies[]`, et `stats` :
- `members_count`, `average_progress` (%), `active_last_7_days`, `stalled_count`
- `blocking_step` : `{title, students}` ou `null` — la leçon « en cours » partagée par le plus d'élèves
- `members[]` : `{id, name, progress, completed_steps, total_steps, last_activity_at, active_recently, stalled, current_step}`

`Groups/Index` : `groups[]` = `{id, name, technology, teacher, archived, joined_at}`, `shared_with_teacher[]`.

Erreurs de formulaire : `errors.code` (join), `errors.name` / `errors.technology` (création).

## Définitions des indicateurs (`App\Services\TeachingGroupStats`)
- Progression = leçons terminées / leçons totales (parcours de la technologie du groupe, sinon tous les parcours de l'élève).
- Dernière activité = dernière modification d'une de ses leçons.
- Inactif (`stalled`) = aucune activité depuis 7 jours et parcours non terminé.
- Limite connue : la validation d'un exercice reste auto-déclarée par l'élève ; la progression mesure donc ce que l'élève déclare, pas ce qu'il maîtrise.

## Points d'accroche UI à ajouter
- Case « Je suis professeur » à l'inscription → champ `account_type=teacher`.
- Lien « Espace professeur » (`/teacher`) dans la navigation si `auth.user.role === 'teacher'`; lien « Mes groupes » (`/groups`) sinon.

## Contenu des cours
Le contenu riche vit dans `config/devroad_rich/<technologie>.php` (111 leçons) et surcharge `config/devroad_course_enrichment.php` champ par champ (`App\Services\RoadmapGenerator::loadEnrichment`). Pour appliquer aux parcours déjà créés : `php artisan devroad:sync-courses` (statuts et exercices validés conservés).
