<p align="center">
<img src="public/logo-home.png" width="200" alt="SERVINI Logo">





<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="200" alt="Laravel Logo">
<h1 align="center">SERVINI - Backend API</h1>
</p>

<p align="center">
<strong>Plateforme de Réservation de Services en Ligne (Architecture Headless)</strong>
</p>

<p align="center">
<a href="https://laravel.com"><img src="https://www.google.com/search?q=https://img.shields.io/badge/Laravel-11-FF2D20%3Fstyle%3Dflat%26logo%3Dlaravel%26logoColor%3Dwhite" alt="Laravel 11"></a>
<a href="https://laravel.com/docs/sanctum"><img src="https://www.google.com/search?q=https://img.shields.io/badge/Auth-Sanctum-lightgrey" alt="Sanctum"></a>
<a href="https://www.mysql.com/"><img src="https://www.google.com/search?q=https://img.shields.io/badge/Database-MySQL-00758F%3Fstyle%3Dflat%26logo%3Dmysql%26logoColor%3Dwhite" alt="MySQL"></a>
</p>

📋 À Propos du Projet

SERVINI est une plateforme web innovante conçue pour simplifier la mise en relation entre les particuliers et les prestataires de services (plombiers, électriciens, bricoleurs) au Maroc.

Ce dépôt contient le Code Source Backend (API RESTful) développé avec Laravel. Il gère toute la logique métier, la base de données, l'authentification et la sécurité, et communique avec le Frontend (React.js) via des réponses JSON.

🚀 Fonctionnalités Clés du Backend

API RESTful : Architecture découplée servie uniquement via des endpoints JSON.

Authentification Multi-Rôles : Gestion sécurisée des Prestataires et des Administrateurs via Laravel Sanctum (Tokens API).

Guest Checkout (Réservation Invité) : Logique permettant de traiter des réservations pour des clients non-inscrits (stockage direct des infos client dans la table réservation).

Système de Filtrage Avancé : Algorithme de recherche dynamique par Ville, Zone, Catégorie, Prix et Note.

Suivi de Réservation : Endpoint public permettant de vérifier le statut d'une demande via un ID unique.

Gestion des Uploads : Traitement des images de profil et des pièces d'identité (CNI).

🛠️ Stack Technique

Framework : Laravel 11.x

Langage : PHP 8.2+

Base de Données : MySQL

Sécurité : Laravel Sanctum (Bearer Token)

ORM : Eloquent (Relations hasOne, hasManyThrough...)

Outils de Dev : Postman, Composer

⚙️ Installation et Configuration

Suivez ces étapes pour lancer l'API sur votre machine locale :

1. Prérequis

PHP >= 8.2

Composer

MySQL

2. Cloner le projet

git clone [https://github.com/votre-username/servini-backend.git](https://github.com/votre-username/servini-backend.git)
cd servini-backend



3. Installer les dépendances

composer install



4. Configuration de l'environnement

Dupliquez le fichier d'exemple et générez la clé d'application :

cp .env.example .env
php artisan key:generate



5. Configuration de la Base de Données

Ouvrez le fichier .env et configurez vos accès MySQL :

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=servini_db
DB_USERNAME=root
DB_PASSWORD=



6. Migrations

Créez les tables dans la base de données :

php artisan migrate



7. Lancer le Serveur

php artisan serve
-------------------------------------------------------------------------------------------------

👥 Auteurs

Projet réalisé dans le cadre du module Programmation Web Dynamique à la Faculté des Sciences d'Agadir (FSA) - Centre d'Excellence.

Rachid OUTSILA - Développeur Full Stack

Khalid ZADO - Développeur Full Stack

Encadré par :

M. JAFAR


<p align="center">Made with ❤️ for the Moroccan Community</p>
