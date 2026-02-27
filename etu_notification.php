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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Space - University Library</title>
  <link rel="stylesheet" href="style2.css">
  <link rel="stylesheet" href="etudiant_new_style.css">
</head>
<body>

<header>
  <h1>📚 University Library</h1>
  <nav>
    <ul>
      <li><a href="etu_catalogue.php" class="nav-link" class="menu-link <?php echo ($activePage=='catalogue') ? 'active' : ''; ?>">
     Catalogue</a></li>
      <li><a href="etu_emprunt.php" class="nav-link" class="menu-link <?php echo ($activePage=='emprunt') ? 'active' : ''; ?>">
     My Loans</a></li>
      <li><a href="etu_profil.php" class="nav-link" class="menu-link <?php echo ($activePage=='profil') ? 'active' : ''; ?>">
     Profile</a></li>
      <li><a href="etu_messages.php" class="nav-link" class="menu-link <?php echo ($activePage=='messages') ? 'active' : ''; ?>">
     Messages</a></li>
      <li><a href="etu_notification.php" class="nav-link" class="menu-link <?php echo ($activePage=='notification') ? 'active' : ''; ?>">
     Notifications</a></li>
    </ul>
  </nav>
</header>

<main>

<section id="notifications">
    <h2>My Notifications</h2>
    
    <?php
    $id_etudiant = $_SESSION['id_etudiant'];

    // 1️⃣ General messages (last 10)
    $sql_general = "SELECT mess 
                    FROM message_etu 
                    WHERE id_etu IS NULL  
                    LIMIT 10";
    $result_general = $conn->query($sql_general);

    if ($result_general->num_rows > 0) {
        echo "<h3>📢 General Alerts</h3><ul>";
        while($row = $result_general->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['mess'], ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No alerts at the moment.</p>";
    }

    // 2️⃣ Private messages (last 10)
    $sql_private = "SELECT mess
                    FROM message_etu 
                    WHERE id_etu = ? 
                    LIMIT 10";
    $stmt = $conn->prepare($sql_private);
    $stmt->bind_param("s", $id_etudiant);
    $stmt->execute();
    $result_private = $stmt->get_result();

    if ($result_private->num_rows > 0) {
        echo "<h3>✉ Private Messages</h3><ul>";
        while($row = $result_private->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['mess'], ENT_QUOTES, 'UTF-8') . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No private messages at the moment.</p>";
    }
    ?>
</section>



</main>
<style>

</style>

<footer>
  <p>© 2025 University Library — All rights reserved</p>
</footer>

<div class="toast" id="toast"></div>

<script>
  // Simple toast
  function toast(msg){ 
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.style.display='block';
    setTimeout(()=> t.style.display='none',1500);
  }

  
</script>

</body>
</html>