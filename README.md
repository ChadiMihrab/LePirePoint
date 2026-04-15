# LePirePoint

Projet étudiant de fin de module visant à concevoir, développer et sécuriser une plateforme web de mise en relation de bout en bout (type Leboncoin).

---

## Fonctionnalités Principales

- **Authentification & Sécurité :** Inscription avec critères de mot de passe strict (Regex), hachage BCRYPT (`password_hash`), et protection contre les injections SQL (requêtes préparées via PDO).
- **Gestion des Annonces (CRUD) :** Création, lecture, modification et suppression d'annonces avec upload d'images sécurisé.
- **Système de Messagerie :** Chat interne entre membres avec suivi des notifications (messages non lus) en temps réel dans la barre de navigation.
- **Interactions Utilisateurs :** Possibilité d'ajouter des annonces à une liste de favoris personnelle.
- **Filtres de Recherche :** Recherche d'annonces par mots-clés, catégories et budget maximum.

---

## Stack Technique

- **Back-End :** PHP 8 (Procédural avec approche modulaire)
- **Base de Données :** MySQL (Architecture relationnelle avec contraintes d'intégrité `ON DELETE CASCADE`)
- **Front-End :** HTML5, CSS3, Bootstrap 5 (Entièrement Responsive)
- **Versioning :** Git & GitHub

---

## Installation et Déploiement Local

Pour tester ce projet, suivez ces étapes :

1. Prérequis
- Un serveur local tel que MAMP.
- PHP 7.4 ou supérieur.

2. Base de données
1. Ouvrez phpMyAdmin.
2. Créez une nouvelle base de données nommée `leboncoin_db`.
3. Importez le fichier `leboncoin_db.sql` (fourni à la racine du projet) pour générer les tables (`utilisateurs`, `annonces`, `categories`, `favoris`, `messages`) et les données de test.

3. Configuration du projet
1. Clonez ce dépôt dans le dossier racine de votre serveur local (`htdocs` pour XAMPP/MAMP, `www` pour WAMP).
   ```bash
   git clone [https://github.com/VOTRE_PSEUDO/LePirePoint.git](https://github.com/VOTRE_PSEUDO/LePirePoint.git)
