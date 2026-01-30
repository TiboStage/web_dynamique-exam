# StoryForge v0.1

> Plateforme collaborative de création narrative — Module d'authentification

## Stack technique

- **Framework** : Symfony 7.4 LTS
- **PHP** : 8.2+
- **Base de données** : MySQL 8.2 (via WAMP)
- **Moteur de templates** : Twig
- **ORM** : Doctrine
- **CSS** : CSS natif (vanilla)
- **SCSS** : SCSS 


## Prérequis

- WAMP Server (ou équivalent) avec :
  - PHP 8.2 ou supérieur
  - MySQL 8.0
  - Composer 2.x
- Symfony CLI

## Procédure d'installation

### 1. Cloner le repository

```bash
git clone https://github.com/TiboStage/web_dynamique-exam.git
cd storyforge
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer l'environnement

Copier le fichier d'environnement et le configurer :

```bash
cp .env .env.local
```

Éditer `.env.local` et configurer la connexion à la base de données :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/webdynamique?serverVersion=8.0"
```

> **Note WAMP** : Par défaut, l'utilisateur est `root` sans mot de passe.

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test (fixtures)

```bash
php bin/console doctrine:fixtures:load
```

Comptes créés par défaut :

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@storyforge.local | admin123 | ROLE_ADMIN |
| user@storyforge.local | user123 | ROLE_USER |

### 6. Lancer le serveur de développement

Avec Symfony CLI :
```bash
symfony serve
```

### 7. Accéder à l'application

Ouvrir dans le navigateur : `http://localhost:8000`

## Fonctionnalités v0.1

- [x] Inscription utilisateur
- [x] Connexion / Déconnexion
- [x] Gestion des rôles (USER / ADMIN)
- [x] Dashboard utilisateur
- [x] Dashboard administrateur
- [x] Protection des routes par rôle
- [x] Page d'accueil publique

## Auteur

Thibault C. — Web Dynamique 

## Licence

Ce projet est développé dans le cadre d'un travail de fin d'études.
