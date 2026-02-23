<?php 
    include 'config1.php';

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        $id = htmlspecialchars($_POST['id_admin']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);

        $sql = "SELECT * FROM administrateur WHERE ID_admin='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = mysqli_fetch_array($result);
            if ($row["mdp"] == "$pass") {
                echo "<script>alert('Connexion réussie !!'); window.location='admin_livre.php';</script>";
                exit();
            } else {
                echo "<script>alert('Erreur : Mot de passe incorecte !');</script>";
                
            }
        } else {
            echo "<script>alert('Erreur : Aucun compte trouvé pour cet ID administrateur !');</script>";
            
        }
    }
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Administrateur - Bibliothèque</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Espace Administrateur</h1>
    <div class="form-box">
      <form id="adminLogin" class="form active" method="POST" action="admin_login.php">
        <h2>Connexion Admin</h2>
        <input type="text" name="id_admin" placeholder="ID Administrateur" required>
        <input type="password" name="mot_de_passe" placeholder="Mot de passe" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Le mot de passe doit contenir au moins 8 caractères, dont au moins une lettre et un chiffre" required>
        <button type="submit">Se connecter</button>
        <p><a href="etudiant.php">← Espace Étudiant</a></p>
      </form>
    </div>
  </div>

  <!-- <script>
    document.getElementById("adminLogin").addEventListener("submit", function(e) {
      e.preventDefault();
      alert("Connexion admin en attente de vérification serveur (PHP)");
    });
  </script> -->
</body>
</html>
