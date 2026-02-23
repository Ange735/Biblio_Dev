<?php
include 'config2.php';
    $activePage = 'notification';

session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.php');
        exit();
    } 
    $etudiant_nom = $_SESSION['etudiant_nom'];
    $id_etudiant = $_SESSION['id_etudiant'];



?>




<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Espace Étudiant - Bibliothèque Universitaire</title>
  <link rel="stylesheet" href="style2.css">
  <link rel="stylesheet" href="etudiant_new_style.css">
</head>
<body>

<header>
  <h1>📚 Bibliothèque Universitaire</h1>
  <nav>
    <ul>
      <li><a href="etu_catalogue.php" class="nav-link" class="menu-link <?php echo ($activePage=='catalogue') ? 'active' : ''; ?>">
     Catalogue</a></li>
      <li><a href="etu_emprunt.php" class="nav-link" class="menu-link <?php echo ($activePage=='emprunt') ? 'active' : ''; ?>">
     Mes emprunts</a></li>
      <li><a href="etu_profil.php" class="nav-link" class="menu-link <?php echo ($activePage=='profil') ? 'active' : ''; ?>">
     Profil</a></li>
      <li><a href="etu_messages.php" class="nav-link" class="menu-link <?php echo ($activePage=='messages') ? 'active' : ''; ?>">
     Messages</a></li>
      <li><a href="etu_notification.php" class="nav-link" class="menu-link <?php echo ($activePage=='notification') ? 'active' : ''; ?>">
     Notifications</a></li>
    </ul>
  </nav>
</header>

<main>

<section id="notifications">
    <h2>Mes notifications</h2>
    
    <?php
    $id_etudiant = $_SESSION['id_etudiant'];

    // 1️⃣ Messages généraux (10 derniers)
    $sql_general = "SELECT mess 
                    FROM message_etu 
                    WHERE id_etu IS NULL  
                    LIMIT 10";
    $result_general = $conn->query($sql_general);

    if ($result_general->num_rows > 0) {
        echo "<h3>📢 Alertes générales</h3><ul>";
        while($row = $result_general->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['mess'], ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Aucune alerte pour le moment.</p>";
    }

    // 2️⃣ Messages privés (10 derniers)
    $sql_private = "SELECT mess
                    FROM message_etu 
                    WHERE id_etu = ? 
                    LIMIT 10";
    $stmt = $conn->prepare($sql_private);
    $stmt->bind_param("s", $id_etudiant);
    $stmt->execute();
    $result_private = $stmt->get_result();

    if ($result_private->num_rows > 0) {
        echo "<h3>✉ Messages privés</h3><ul>";
        while($row = $result_private->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['mess'], ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Aucun message privé pour le moment.</p>";
    }
    ?>
</section>



</main>
<style>

</style>

<footer>
  <p>© 2025 Bibliothèque Universitaire — Tous droits réservés</p>
</footer>

<div class="toast" id="toast"></div>

<script>
  // Toast simple
  function toast(msg){ 
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.display='block';
    setTimeout(()=> t.style.display='none',1500);
  }

  
</script>

</body>
</html>
