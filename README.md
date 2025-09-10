# Ina Zaoui

![PHPStan](https://img.shields.io/badge/PHPStan-level%206-brightgreen)
![Tests](https://img.shields.io/badge/tests-Passing-brightgreen)

## Description

Ce site web présente les œuvres et le portfolio de la photographe **Ina Zaoui**.  
Il offre :  
- Une interface **administration** pour gérer les albums et les utilisateurs invités.  
- Un espace **public** pour découvrir l’ensemble des œuvres photographiques.

---

## Technologies

- PHP 8.1+
- Symfony 6+
- MySQL 8+
- Composer
- PHPUnit
- Xdebug
- Node.js / npm

---

## Pré-requis

- Base de données MySQL accessible
- Symfony CLI (optionnel mais recommandé)

---

## Installation

### 1. Récupérer le projet

```bash
git clone https://github.com/clbtd/InaZaoui.git
cd INAZAOUI
```

2. Installer les dépendances
```bash
composer install
npm install
```

3. Configuration de la Base de données
Créer le fichier `.env.local` à la racine du projet :
```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
```

Créer la base de données, exécuter les migrations et charger les fixtures :
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

4. Démarrer le serveur
```bash
# Avec Symfony CLI
symfony server:start

# Alternative avec le serveur PHP intégré
php -S localhost:8000 -t public/
```

## Utilisation
- Interface publique : http://localhost:8000
- Interface admin : http://localhost:8000/admin

Identifiants par défaut
| Rôle  | Email                                     | Mot de passe |
| ----- | ------------------------- | ------------ |
| Admin | [ina@zaoui.com]     | 123          |
| User  | [invite1@test.fr] | 123          |

Permissions

Utilisateur non-admin :
- Ajouter et supprimer ses photos depuis l’admin

Admin :
- Gérer les albums (ajouter, modifier, supprimer)
- Gérer les photos (ajouter, modifier, supprimer)
- Gérer les utilisateurs (ajouter, bloquer, supprimer)
- Bloquer ou supprimer un utilisateur supprime ses photos

## Tests
| Commande                      | Description                                           |
| ----------------------------- | ----------------------------------------------------- |
| `composer purge-bdd`          | Purger la base de données (env=dev)                   |
| `composer purge-bdd-test`     | Purger la base de données (env=test)                  |
| `composer test`               | Lancer tous les tests PHPUnit, purge la BDD, coverage |
| `composer test-no-purge`      | Tous les tests PHPUnit, sans purge, coverage          |
| `composer test-home-ctrl`     | Tester HomeController, sans purge ni coverage         |
| `composer test-album-ctrl`    | Tester AlbumController, sans purge ni coverage        |
| `composer test-guest-ctrl`    | Tester GuestController, sans purge ni coverage        |
| `composer test-security-ctrl` | Tester SecurityController, sans purge ni coverage     |
| `composer test-media-ctrl`    | Tester MediaController, sans purge ni coverage        |
| `vendor/bin/phpstan analyse`  | Lancer PHPStan pour l’analyse statique                |
| `composer cs:fix`             | Lancer PHP CS Fixer pour formater le code             |

## Licence
Ce projet est sous licence MIT.