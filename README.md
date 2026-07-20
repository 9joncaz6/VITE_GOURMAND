# Vite & Gourmand  
### Application Web de Commande de Menus  
Développé par **Jonathan CAZAUMAYOU**

![Status](https://img.shields.io/badge/Status-Termin%C3%A9e-brightgreen)
![Symfony](https://img.shields.io/badge/Symfony-7.0-black)
![PHP](https://img.shields.io/badge/PHP-8.2-purple)
![MySQL](https://img.shields.io/badge/MySQL-Database-orange)
![MongoDB](https://img.shields.io/badge/MongoDB-NoSQL-green)

---

# Présentation

**Vite & Gourmand** est une application web permettant aux visiteurs de consulter les menus,  
commander en ligne, laisser des avis, et aux employés/administrateurs de gérer l’activité.

Ce projet a été réalisé dans le cadre du **Titre Professionnel Développeur Web & Web Mobile**.

**Démo en ligne :**  
https://vite-gourmand-2rkl.onrender.com/

---

# Fonctionnalités

## Côté visiteur
- Consultation des menus  
- Filtrage par thème / régime  
- Ajout au panier  
- Validation de commande  
- Création de compte  
- Connexion / déconnexion  
- Laisser un avis sur un menu  

## Côté employé
- Gestion des menus (CRUD)  
- Gestion des catégories, thèmes et régimes  
- Gestion des commandes et statuts  
- Consultation des avis  

## Côté administrateur
- Tableau de bord avec statistiques (MongoDB)  
- Gestion des utilisateurs  
- Gestion des avis  
- Suivi des logs NoSQL  

---

# Stack Technique

- **Backend** : Symfony 7 (PHP 8.2)  
- **Frontend** : Twig, HTML5, CSS3, JavaScript  
- **Base SQL** : MySQL (Doctrine ORM)  
- **Base NoSQL** : MongoDB (Doctrine ODM)  
- **Sécurité** : Hashing, rôles, validations  
- **Déploiement** : Docker + Apache + Render  
- **Mailer local** : Mailhog (`smtp://127.0.0.1:1025`)  
- **Webpack Encore** : Non utilisé  

---

# Architecture

- Architecture MVC (Symfony)  
- ORM : Doctrine (MySQL)  
- ODM : Doctrine MongoDB (logs, statistiques)  
- Services internes (StatsService, LogService, etc.)  
- Formulaires Symfony  
- Système d’upload d’images  
- Sécurité via firewall, rôles et voters  
- Templates Twig  
- Déploiement via Dockerfile personnalisé  

---

# Déploiement

L'application est déployée sur **Render** via une image Docker personnalisée.

Points techniques principaux :
- Image Docker basée sur `php:8.2-apache`  
- Activation de `mod_rewrite`  
- Redirection Apache vers `/public`  
- Installation de l’extension MongoDB (SSL activé)  
- Gestion des permissions pour les uploads  
- Variables d’environnement fournies par Render  
- Connexion sécurisée à MongoDB Atlas  

---

# Dépôt GitHub

https://github.com/9joncaz6/VITE_GOURMAND.git

---





🚀 Installation locale
Ce guide explique pas à pas comment installer et lancer l’application Vite & Gourmand en local.

1️⃣ Prérequis
Assurez-vous d’avoir installé :

🔧 Backend
PHP 8.2+

Composer

Symfony CLI (optionnel mais recommandé)

🗄️ Bases de données
MySQL / MariaDB

MongoDB (local ou Atlas)

📧 Email local
Mailhog (recommandé)

🐳 Optionnel
Docker (si vous souhaitez lancer via conteneur)

2️⃣ Cloner le projet:
git clone https://github.com/9joncaz6/VITE_GOURMAND.git

cd VITE_GOURMAND
3️⃣ Installer les dépendances PHP:
composer install

4️⃣ Configurer l’environnement (.env.local)
Créez un fichier :

-------------------------------------
Code
.env.local
Et ajoutez :

env
APP_ENV=dev
APP_SECRET=your_secret_here

# --- MySQL ---
DATABASE_URL="mysql://root:root@127.0.0.1:3306/vite_gourmand"

# --- MongoDB (Atlas ou local) ---
MONGODB_URL="mongodb+srv://mayjoca789_db_user:n86DcKTrXa8QWAA1@cluster0.f9crcxj.mongodb.net/vite_gourmand?retryWrites=true&w=majority"
MONGODB_DB=vite_gourmand

# --- Mailhog ---
MAILER_DSN=smtp://127.0.0.1:1025
-------------------------------------


5️⃣ Créer la base MySQL
Option A — Importer le schéma SQL fourni
Dans phpMyAdmin / Workbench :

Créez la base :
CREATE DATABASE vite_gourmand CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
Importez le fichier SQL :
schema.sql
(Le fichier contient toutes les tables : utilisateur, menu, plat, commande, avis, etc.)

Option B — Utiliser Doctrine
symfony console doctrine:database:create
symfony console doctrine:schema:update --force


6️⃣ Configurer MongoDB
Option A — MongoDB Atlas (déjà configuré dans ton .env)

Option B — MongoDB local
Lancez MongoDB :
mongod
Puis modifiez .env.local :

env
MONGODB_URL="mongodb://127.0.0.1:27017"
MONGODB_DB=vite_gourmand

7️⃣ Lancer Mailhog (emails locaux)
Via Docker :
bash
docker run -d -p 8025:8025 -p 1025:1025 mailhog/mailhog
Interface web :
👉 http://localhost:8025

8️⃣ Lancer le serveur Symfony
bash
symfony server:start
Ou :

bash
php -S localhost:8000 -t public
Application disponible sur :
👉 http://localhost:8000
