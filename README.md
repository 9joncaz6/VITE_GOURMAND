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

# Installation locale

## 1. Prérequis

- PHP 8.2+  
- Composer  
- MySQL / MariaDB  
- MongoDB  
- Symfony CLI (optionnel)  
- Mailhog (pour les emails en local)  

## 2. Cloner le projet

```bash
git clone https://github.com/9joncaz6/VITE_GOURMAND.git
cd VITE_GOURMAND
