<?php 
include 'config2.php';
$activePage = 'reservation';
//actions
if (isset($_POST['vali'])) {
    $id_etu = $_POST['id_etu'];
    $isbn = intval($_POST['isbn']);

    // Vérifier si l'étudiant a déjà emprunté ce livre
    $sql0 = "SELECT COUNT(*) AS count_reserv FROM emprunt 
             WHERE ISBN=$isbn AND ID_etu='$id_etu' AND statue_empr='en_cours'";
    $result0 = $conn->query($sql0);
    $row0 = $result0->fetch_assoc();

    // Si l'étudiant a déjà emprunté ce livre, bloquer
    if ($row0['count_reserv'] > 0) {
        echo "<script>alert('Erreur : L\\'étudiant a déjà emprunté ce livre !');
              window.location='admin_reserv.php';</script>";
        exit();
    }

    // Sinon, procéder à la validation
    $sql = "UPDATE reservation SET statut='validé' 
            WHERE ISBN=$isbn AND ID_etu='$id_etu' AND statut='en_attente'";

    if($conn->query($sql) === TRUE){
        
        $sql1 = "UPDATE livre SET nbr_empr = nbr_empr + 1 WHERE ISBN=$isbn";  
        
        if($conn->query($sql1) === TRUE){
          
            $date_empr = date('Y-m-d');
            $date_retour = date('Y-m-d', strtotime('+15 days'));

            $sql2 = "INSERT INTO emprunt (ISBN, ID_etu, date_reserv, date_empr, date_retour, date_retour_eff, statue_empr) 
                     VALUES ($isbn, '$id_etu', NOW(), '$date_empr', '$date_retour', NULL, 'en_cours')";

            if($conn->query($sql2) === TRUE){
                $sql3 = "INSERT INTO message_etu (ID_etu, mess) 
                         VALUES ('$id_etu', 'Votre réservation pour le livre (ISBN: $isbn) a été validée. Veuillez récupérer le livre dans les 2 jours.')";
                
                if($conn->query($sql3) === TRUE){
                    echo "<script>alert('Réservation validée avec succès !');
                          window.location='admin_reserv.php';</script>";
                    exit();
                } else {
                    echo "Erreur INSERT message_etu : " . $conn->error;
                }
            } else {
                echo "Erreur INSERT emprunt : " . $conn->error;
            }
        } else {
            echo "Erreur UPDATE livre : " . $conn->error;
        }
    } else {
        echo "Erreur UPDATE reservation : " . $conn->error;
    }
}

if (isset($_POST['refus'])) {
    $id_etu = $_POST['id_etu'];
    $isbn = intval($_POST['isbn']);

    $sql="UPDATE reservation SET statut='refusé' WHERE ISBN=$isbn AND ID_etu='$id_etu' AND statut='en_attente'";
    if($conn->query($sql) === TRUE){
        echo "<script>alert('Réservation refusée avec succès !');window.location='admin_reserv.php';</script>";
        exit();
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
      </ul>
    </aside>

    <!-- Section Réservations -->
    <section id="panelReservations">
      <h2>Réservations en attente</h2>
      <table class="table" id="reservationsTable">
        <thead>
          <tr>
            <th>Etudiant</th>
            <th>Livre</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php 
        $sql = "SELECT r.*, e.Prénom, e.nom, l.titre FROM reservation r 
                JOIN étudiant e ON r.ID_etu = e.ID_etu
                JOIN livre l ON r.ISBN = l.ISBN
                WHERE r.statut='en_attente'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>{$row['Prénom']} {$row['nom']} ({$row['ID_etu']})</td>";
                echo "<td>{$row['titre']} ({$row['ISBN']})</td>";
                echo "<td>{$row['date_reserv']}</td>";
                echo "<td>
                        <form method='POST' action='admin_reserv.php' style='display:inline;'>
                            <input type='hidden' name='isbn' value='{$row['ISBN']}'>
                            <input type='hidden' name='id_etu' value='{$row['ID_etu']}'>
                            <button type='submit' name='vali' title='Valider'>✅</button>
                            <button type='submit' name='refus' title='Refuser'>❌</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>Pas de demande de réservation.</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </section>

  </main>

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


    document.getElementById('btnInventory').addEventListener('click', ()=>{
      window.location.href = 'admin_dashboard.php';
    });
  </script>
</body>
</html>