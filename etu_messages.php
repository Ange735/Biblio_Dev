<?php 
include 'config2.php';
    $activePage = 'messages';

session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.php');
        exit();
    } 
    $etudiant_nom = $_SESSION['etudiant_nom'];
    $id_etudiant = $_SESSION['id_etudiant'];

if (isset($_POST['send_message']) && !empty($_POST['message_text'])) {

    $message = $_POST['message_text']; // sécuriser le texte
    $id_etudiant = $_SESSION['id_etudiant'];             // id de l'étudiant
    
    $sql = "INSERT INTO message_admin (id_etudiant, messag) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $id_etudiant, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Message envoyé à l’administration !'); window.location='etu_messages.php';</script>";
        exit();
    } else {
        echo "Erreur : " . $conn->error;
    }
}

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

<section id="messages">
    <h2>Contacter l’administration</h2>

    <form method="POST" action="etu_messages.php">
        <textarea name="message_text" placeholder="Votre message ici..." required></textarea>
        <button type="submit" name="send_message">Envoyer</button>
    </form>
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


  // Envoyer message
  document.getElementById('sendMessage').addEventListener('click', ()=>{
    const txt = document.getElementById('messageText').value.trim();
    if(!txt) return alert('Message vide');
    toast('Message envoyé à l’administration !');
    document.getElementById('messageText').value='';
  });

</script>

</body>
</html>
