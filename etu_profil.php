<?php 
include 'config2.php';
    $activePage = 'profil';

session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.php');
        exit();
    } 
    $etudiant_nom = $_SESSION['etudiant_nom'];
    $id_etudiant = $_SESSION['id_etudiant'];


    $id_etudiant = $_SESSION['id_etudiant'];

// Retrieve current info
$sql_profil = "SELECT nom, Prénom, Email FROM étudiant WHERE ID_etu = ?";
$stmt = $conn->prepare($sql_profil);
$stmt->bind_param("s", $id_etudiant);
$stmt->execute();
$result_profil = $stmt->get_result();
$profil = $result_profil->fetch_assoc();



if (isset($_POST['update_profil'])) {

    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $id_etudiant = $_SESSION['id_etudiant'];

    // If password filled → update with hash
    if (!empty($password)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE étudiant 
                SET nom = ?, Prénom = ?, Email = ?, mdp = ?
                WHERE ID_etu = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nom, $prenom, $email, $password_hash, $id_etudiant);
    } 
    // Otherwise → leave password unchanged
    else {
        $sql = "UPDATE étudiant 
                SET nom = ?, Prénom = ?, Email = ?
                WHERE ID_etu = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nom, $prenom, $email, $id_etudiant);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully'); window.location='etu_profil.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}


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

<section id="profil" >
    <h2>My Profile</h2>
    <form method="POST" action="logout.php" style="margin-top:15px;">
    <button type="submit" class="logout-btn">🚪 Logout</button>
</form>
<style>
  .logout-btn{
    background:yellowgreen;
    color:white;
    padding:10px 15px;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

.logout-btn:hover{
    background:#c9302c;
}

</style>

    <form method="POST" action="etu_profil.php">

        <input 
            type="text" 
            name="nom"
            placeholder="Last Name"
            value="<?php echo htmlspecialchars($profil['nom']); ?>"
            required
        >

        <input 
            type="text" 
            name="prenom"
            placeholder="First Name"
            value="<?php echo htmlspecialchars($profil['Prénom']); ?>"
            required
        >

        <input 
            type="email" 
            name="email"
            placeholder="University Email"
            value="<?php echo htmlspecialchars($profil['Email']); ?>"
            required
        >

        <input 
            type="password" 
            name="password"
            placeholder="New password (leave blank if unchanged)"
        >

        <button type="submit" name="update_profil">
            Update
        </button>
        

    </form>
</section>



</main>
<style>
    /* ===== GENERAL ===== */

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