# Biblio_Dev : gestion d'une bibliothèque universitaire

Application web de gestion d'une bibliothèque universitaire, avec un espace étudiant (catalogue, réservations, emprunts) et un espace administrateur (livres, étudiants, prêts, statistiques). Projet académique réalisé à l'ENSAM Meknès.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat-square&logo=javascript&logoColor=black)
![Chart.js](https://img.shields.io/badge/Chart.js-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)
![FPDF](https://img.shields.io/badge/FPDF-reçus%20PDF-555555?style=flat-square)

## Fonctionnalités

### Espace étudiant

- Inscription et connexion.
- Recherche dans le catalogue.
- Réservation d'un livre disponible, ou inscription sur la liste d'attente s'il est déjà emprunté.
- Suivi de ses emprunts, réservations et listes d'attente.
- Téléchargement d'un **reçu d'emprunt en PDF**.
- Notifications et messagerie avec l'administration.

### Espace administrateur

- Gestion des livres (ajout avec photo de couverture, modification, suppression) et des étudiants.
- Validation des réservations : la validation crée l'emprunt et prévient l'étudiant par message.
- Suivi des prêts et des retours. Chaque retard est comptabilisé et **le compte est bloqué automatiquement à partir de 3 retards**.
- Consultation des listes d'attente par livre.
- Tableau de bord avec statistiques générales et graphiques (Chart.js).
- Messagerie avec les étudiants.

## Structure

```
├── index.php               # connexion et inscription étudiant
├── etu_*.php               # espace étudiant (catalogue, emprunts, profil, messages, notifications)
├── admin_login.php         # connexion administrateur
├── admin_*.php             # espace administrateur (tableau de bord, livres, étudiants, prêts, réservations...)
├── recu_emprunt.php        # génération du reçu PDF (FPDF)
├── config1.php, config2.php  # connexions aux deux bases MySQL
├── gestion_admin.sql       # base des administrateurs
├── gestion_bibliothèque.sql  # base de la bibliothèque
├── fichier/                # couvertures des livres
└── fpdf/                   # bibliothèque FPDF
```

## Installation (XAMPP ou WAMP)

1. Cloner le dépôt dans `htdocs` (XAMPP) ou `www` (WAMP) :
   ```bash
   git clone https://github.com/Ange735/Biblio_Dev.git
   ```
2. Créer les **deux** bases de données avec MySQL Workbench ou phpMyAdmin : `gestion_admin.sql`, puis `gestion_bibliothèque.sql`.
   > Les scripts contiennent aussi des requêtes de test (`DROP`, `SELECT`). Exécute-les bloc par bloc et ignore les `DROP DATABASE` / `DROP TABLE`.
3. Si ton MySQL a un mot de passe, le renseigner dans `config1.php` et `config2.php`.
4. Ouvrir `http://localhost/Biblio_Dev/index.php` (espace étudiant) ou `http://localhost/Biblio_Dev/admin_login.php` (espace administrateur).

Pour te connecter, crée un compte étudiant depuis la page d'inscription. Pour l'espace administrateur, `gestion_admin.sql` crée un compte de démonstration (identifiant `AD_1`, mot de passe dans le script).

## Limites connues et améliorations prévues

Projet pédagogique, pas destiné à la production en l'état :

- Les mots de passe sont stockés en clair : à remplacer par `password_hash` et `password_verify`.
- Plusieurs requêtes SQL concatènent les saisies utilisateur : à passer en requêtes préparées pour supprimer les risques d'injection SQL.
- Les scripts SQL mélangent création et tests : à séparer en un script d'installation propre.

## Équipe

- **Ange Bado** · [GitHub](https://github.com/Ange735) · [LinkedIn](https://www.linkedin.com/in/ange-bado)
- **issougmehdi75-alt** · [GitHub](https://github.com/issougmehdi75-alt)
