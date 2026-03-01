# Biblio_Dev
Dépot pour un projet  de gestion de bibliothèque
#Setup

Pour exécuter le code vous devez obligatoirement cloner ce depot github
Ensuite vous placer le dossier cloner dans votre dossier xampp/htdocs
Une fois le ficher bien placer, vous vous assurez d'exécuter le code de la base de donnée( veuillez lire le code de la base de donnée et exécuter bloc par bloc pour ne pas creer et supprimer la base de donnée en même temps par ce que j'ai mis des instructions qui ne servent pas dans la creation de la base de donnée, c'était juste pour des test)
A noter qu'il ya 2 bases de donnes donc vous devez les exécuter tous les 2, de preference dans mysql workbench
ensuite vous vous render dans votre navigateur, vous taper : localhost/chemin_de_votre_dossier/nom_ficher_a_exécuter.php
Si en ouvrant par exemple la page index.php et que on vous demande de vous connecter vous pouvez juste aller sur workbench et regarder les mails et mots de passes utiliser pour vous connecter avec l'instruction : select * from étudiant pour la page étudiant 📚 Biblio_Dev

Dépôt pour un projet de gestion de bibliothèque.

⚙️ Installation & Configuration (Setup)

Pour exécuter ce projet, veuillez suivre les étapes ci-dessous :

Cloner le dépôt GitHub

git clone <lien_du_depot>

Déplacer le dossier cloné dans le répertoire :

xampp/htdocs

Configurer la base de données

Exécutez le script SQL fourni.

⚠️ Important : Lisez attentivement le fichier SQL et exécutez les requêtes bloc par bloc, car certaines instructions sont uniquement destinées à des tests et ne sont pas nécessaires à la création de la base de données.

Il existe deux bases de données, vous devez donc exécuter les deux scripts.

Il est recommandé d’utiliser MySQL Workbench.

Lancer le projet dans le navigateur

Ouvrez votre navigateur et tapez :

localhost/chemin_du_dossier/nom_du_fichier.php

Exemple :

localhost/Biblio_Dev/index.php

Connexion au système

Si une page de connexion apparaît (ex : index.php), vous pouvez récupérer les identifiants de connexion dans la base de données.

Pour la page étudiant, utilisez la requête suivante dans MySQL Workbench :

SELECT * FROM etudiant;

Vous y trouverez les emails et mots de passe nécessaires pour vous connecter. si vous ne trouvez rien vous n'avez juste qu'a inserer votre propre utilisateur et vous connecter !
