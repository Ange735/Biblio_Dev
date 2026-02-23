<?php 
include 'config2.php';
$activePage = 'pret';
//actions

if(isset($_POST['validate_extend'])){
    $etu = htmlspecialchars($_POST['etu']);
    $liv = intval($_POST['liv']);
    $ret = htmlspecialchars($_POST['empr']); // ex: 2025-12-09

    $new_return_date = date('Y-m-d', strtotime($ret . ' +15 days'));
    $sql = "UPDATE emprunt 
            SET date_retour = '$new_return_date' 
            WHERE ID_etu = '$etu' 
              AND ISBN = $liv 
              AND date_retour = '$ret'
              AND statue_empr='en_cours'";

    if($conn->query($sql) === TRUE){
        echo "<script>alert('Prolongation validée !');window.location='admin_pret.php';</script>";
        exit();
    } else {
        echo 'Erreur UPDATE emprunt : ' . $conn->error;
    }
}

if(isset($_POST['penalize'])){
    $etu = htmlspecialchars($_POST['etu']);
    $liv = intval($_POST['liv']);
    $ret = htmlspecialchars($_POST['empr']); // ex: 2025-12-09

    $sql3 = "UPDATE étudiant SET nbr_retard = nbr_retard + 1 WHERE ID_etu = '$etu'";

    if ($conn->query($sql3) === TRUE) {
        // Vérification du nombre de retards
        $sql1 = "SELECT nbr_retard FROM étudiant WHERE ID_etu = '$etu'";
        $result = $conn->query($sql1);
        $row = $result->fetch_assoc();

        // Si blocage nécessaire
        if ($row['nbr_retard'] >= 3) {
            $sql = "UPDATE étudiant SET statue_etu = 0 WHERE ID_etu = '$id'";
            $conn->query($sql);
            echo "<script>alert('Utilisateur bloqué !');window.location='admin_pret.php';</script>";
            exit();
        } else {
            echo "<script>alert('Utilisateur pénalisé !');window.location='admin_pret.php';</script>";
            exit();
        }

    } else {
        echo "Erreur : " . $conn->error;
    }

}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - Bibliothèque Universitaire</title>
  <link rel="stylesheet" href="admin_style.css" />
  <link rel="stylesheet" href="admin_new_style.css">
</head>
<body>
  <header class="admin-header">
    <h1>🔧 Espace Administrateur — Bibliothèque</h1>
    <div class="header-actions">
      <button id="btnInventory">Statistiques</button>
    </div>
  </header>

  <main class="admin-main">
    <aside class="sidebar">
      <ul>
        <li><a href="admin_livre.php" class="menu-link <?php echo ($activePage=='livres') ? 'active' : ''; ?>">
     Livres (CRUD)</a></li>
        <li><a href="admin_etudiant.php" class="menu-link <?php echo ($activePage=='etudiants') ? 'active' : ''; ?>">
     Étudiants (CRUD)</a></li>
        <li><a href="admin_reserv.php" class="menu-link <?php echo ($activePage=='reservation') ? 'active' : ''; ?>">
     Réservations</a></li>
        <li><a href="admin_pret.php" class="menu-link <?php echo ($activePage=='pret') ? 'active' : ''; ?>">
     Prêts</a></li>
        <li><a href="admin_liste.php" class="menu-link <?php echo ($activePage=='liste') ? 'active' : ''; ?>">
     Liste d'attente</a></li>
        <li><a href="admin_messages.php" class="menu-link <?php echo ($activePage=='messages') ? 'active' : ''; ?>">
     Alertes & Messages</a></li>
    </aside>



<!-- Section -->



<section id="panelLoans" >
      <h2>État des prêts</h2>
      <table class="table" id="loansTable">
        <tr>
          <th>Etudiant</th>
          <th>Livre</th>
          <th>Début</th>
          <th>Retour prévu</th>
          <th>Actions</th>
        </tr>
        <?php 
              $sql = "SELECT em.*, e.Prénom, e.nom, l.titre FROM emprunt em
              JOIN étudiant e ON em.ID_etu = e.ID_etu
              JOIN livre l ON em.ISBN = l.ISBN
              WHERE em.statue_empr='en_cours'";
              $result = $conn->query($sql);
              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      echo "<tr>";
                      echo "<td>{$row['Prénom']} {$row['nom']} ({$row['ID_etu']})</td>";
                      echo "<td>{$row['titre']} ({$row['ISBN']})</td>";
                      echo "<td>{$row['date_reserv']}</td>";
                      echo "<td>{$row['date_retour']}</td>";
                      echo "<td>
                              <form action='admin_pret.php' method='POST' style='display:inline;'>
                                <input type='hidden' name='etu' value='{$row['ID_etu']}'>
                                <input type='hidden' name='liv' value='{$row['ISBN']}'>
                                <input type='hidden' name='empr' value='{$row['date_retour']}'>
                                <button class='btn-validate-extend' name='validate_extend'> prolonger</button>
                                <button class='btn-penalize-loan' name='penalize' >Pénaliser</button>
                              </form>
                            </td>";
                      echo "</tr>";
                  }
              } else {
                  echo "<tr><td colspan='5'>Aucun prêt en cours.</td></tr>";
              }

        ?>
      </table>
    </section>






    </main>
<!--Modals-->






<style>

</style>





    <div class="toast" id="toast"></div>

  <script>
    // --- JS minimal pour interactions ---
    function toast(msg){ 
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.display='block';
      setTimeout(()=> t.style.display='none',1500);
    }


    // Modals
    document.querySelectorAll('.close-modal').forEach(btn=>{
      btn.addEventListener('click', e=>{
        btn.closest('.modal').style.display='none';
      });
    });

    document.getElementById('btnInventory').addEventListener('click', ()=>{
    window.location.href = 'admin_dashboard.php';
});


    document.getElementById('sendAlertBtn').addEventListener('click', ()=>{
      const text = document.getElementById('alertText').value.trim();
      if(!text){ alert('Texte vide'); return;}
      const studentSelect = document.getElementById('alertStudentSelect');
      const id = studentSelect.value;
      const name = studentSelect.options[studentSelect.selectedIndex].text;
      const ul = document.getElementById('alertsList');
      ul.insertAdjacentHTML('afterbegin', `<li>${id? 'Alerte à '+name : 'Alerte globale'}: ${text}</li>`);
      toast('Alerte envoyée');
      document.getElementById('alertText').value='';
    });
  </script>
</body>
</html>