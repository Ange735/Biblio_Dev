<?php
session_start();
include 'config2.php';
if (isset($_POST['inscr'])) {
    // On récupère les données du formulaire d'inscription


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = htmlspecialchars($_POST['email']);
        $id = htmlspecialchars($_POST['id_etudiant']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);
        $confpass = htmlspecialchars($_POST['cmdp']);

        $sql1 = "SELECT * FROM étudiant WHERE Email='$email' OR ID_etu='$id'";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            echo "<script>alert('ERREUR! :Email déja utilisé ou ID_étudiant déja présent!');</script>";
        } else {
            if ($pass == $confpass) {
                $sql = "INSERT INTO étudiant (ID_etu, nom, Prénom, Email, nbr_retard, statue_etu, mdp)
                VALUES ('$id', '$nom', '$prenom', '$email', 0, 1, '$pass')";

                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Inscription réussie !'); window.location='etu_catalogue.php';</script>";
                    exit();
                } else {
                    echo "Erreur: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('ERREUR! :mot de passe de confirmation différent du mot de passe !');</script>";

            }
        }
    }
}

if (isset($_POST['connect'])) {
    // On récupère les données du formulaire de connection
    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        $id = htmlspecialchars($_POST['id_etudiant']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);

        $sql = "SELECT * FROM étudiant WHERE ID_etu='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $sql1 = "SELECT statue_etu FROM étudiant WHERE ID_etu = '$id'";
            $results = $conn->query($sql1);
            $rows = $results->fetch_assoc();
            if ($rows['statue_etu'] == 0) {
                echo "<script>alert('Votre compte est bloqué en raison de trop nombreux retards. Veuillez contacter l\'administration pour le débloquer.');window.location='index.php';</script>";
                exit();
    }
            else{
            $row = mysqli_fetch_array($result);
            if ($row["mdp"] == "$pass") {
                session_regenerate_id(true);
                $_SESSION['id_etudiant'] = $id;
                $_SESSION['etudiant_nom'] = $row['nom'];
                $_SESSION['logged_in'] = true;
                echo "<script>alert('Connexion réussie !!'); window.location='etu_catalogue.php';</script>";
                exit();
            } else {
                echo "<script>alert('Erreur : Mot de passe incorecte !');</script> ";
            }}}
            else {
                echo "<script>alert('Erreur : Aucun compte trouvé pour cet ID étudiant ! (Réessayer avec un autre ID ou inscrivez-vous !)');</script> ";
        }
        
}
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque Universitaire - Étudiant</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h1>Bibliothèque de l'Université</h1>

        <div class="form-box">
            <!-- Formulaire de connexion -->
            <form id="loginForm" class="form active" method="POST" action="index.php">
                <h2>Connexion Étudiant</h2>
                <input type="text" name="id_etudiant" placeholder="ID Étudiant" required>
                <input type="password" name="mot_de_passe" placeholder="Mot de passe" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Le mot de passe doit contenir au moins 8 caractères, dont au moins une lettre et un chiffre" required>
                <button type="submit" name="connect">Se connecter</button>
                <p>Pas encore inscrit ? <a href="#" id="showRegister">Créer un compte</a></p>
            </form>

            <!-- Formulaire d'inscription -->
            <form id="registerForm" class="form" method="POST" action="index.php">
                <h2>Inscription Étudiant</h2>
                <input type="text" name="nom" placeholder="Nom" required>
                <input type="text" name="prenom" placeholder="Prénom" required>
                <input type="text" name="id_etudiant" placeholder="ID Étudiant" required>
                <input type="email" name="email" placeholder="Email universitaire" required>
                <input type="password" name="mot_de_passe" placeholder="Mot de passe" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Le mot de passe doit contenir au moins 8 caractères, dont au moins une lettre et un chiffre" required>
                <input type="password" name="cmdp" placeholder="Confirmer le mot de passe" minlength="8" required>
                <button type="submit" name="inscr">S'inscrire</button>
                <p>Déjà inscrit ? <a href="#" id="showLogin">Se connecter</a></p>
            </form>
        </div>
    </div>

    <style>
        /* Masquer les formulaires par défaut */
.form {
  display: none;
  animation: fadeIn 0.5s ease-in-out;
}

/* Afficher uniquement le formulaire actif */
.form.active {
  display: block;
}

/* Animation d'apparition */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

    </style>

    <script>
document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showRegister = document.getElementById("showRegister");
    const showLogin = document.getElementById("showLogin");

    // Sécurité : on cache toujours l'inscription au départ
    registerForm.classList.remove("active");
    loginForm.classList.add("active");

    showRegister.addEventListener("click", (e) => {
        e.preventDefault();
        loginForm.classList.remove("active");
        registerForm.classList.add("active");
    });

    showLogin.addEventListener("click", (e) => {
        e.preventDefault();
        registerForm.classList.remove("active");
        loginForm.classList.add("active");
    });
});
</script>


</body>

</html>