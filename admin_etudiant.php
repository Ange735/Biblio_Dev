<?php 
include 'config2.php';
$activePage = 'etudiants';
//actions
if (isset($_POST['inscr'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = htmlspecialchars($_POST['email']);
        $id = htmlspecialchars($_POST['id_etudiant']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);
        $confpass = htmlspecialchars($_POST['cmdp']);

        // Même logique d'insertion qu'au-dessus
        $sql1 = "SELECT * FROM étudiant WHERE Email='$email' OR ID_etu='$id'";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            echo "<script>alert('ERREUR! : Email déjà utilisé ou ID_étudiant déjà présent!');</script>";
        } else {
            if ($pass === $confpass) {
                $sql = "INSERT INTO étudiant (ID_etu, nom, Prénom, Email, nbr_retard, statue_etu, mdp)
                        VALUES ('$id', '$nom', '$prenom', '$email', 0, 1, '$pass')";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Inscription réussie !');window.location='admin_etudiant.php';</script>";
                    exit();
                } else {
                    echo "Erreur: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('ERREUR! : Mot de passe de confirmation différent du mot de passe !');</script>";
            }
        }
    }
    

if (isset($_POST['modif'])){
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = htmlspecialchars($_POST['email']);
        $id = htmlspecialchars($_POST['id_etudiant']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);
        $confpass = htmlspecialchars($_POST['cmdp']);
        // Mise à jour d'un utilisateur existant
        $sql = "SELECT * FROM étudiant WHERE ID_etu='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0){
          
        $sql = "UPDATE étudiant 
                SET nom='$nom', Prénom='$prenom', Email='$email', mdp='$pass' 
                WHERE ID_etu='$id'";

        if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Modification éffectuée avec succès !');window.location='admin_etudiant.php';</script>";
        exit();
    } else {
        echo "Erreur : " . $conn->error;
    }}
      else{
        echo "<script>alert('ERREUR! :Impossible de modifier, ID_étudiant non trouvé !');</script>";}
    }


    if (isset($_POST['supp'])) {
        $id = htmlspecialchars($_POST['id_supp']);
      $sql2 = "DELETE FROM étudiant WHERE ID_etu='$id'";
      if ($conn->query($sql2) === TRUE) {
        echo "<script>alert('Utilisateur supprimé avec succès !');window.location='admin_etudiant.php';</script>";
        exit();
      } else {
        echo "Erreur : " . $conn->error;
    }
}

    if (isset($_POST['penaliser'])) {

    $id = htmlspecialchars($_POST['id_pen']);

    // Pénalisation
    $sql3 = "UPDATE étudiant SET nbr_retard = nbr_retard + 1 WHERE ID_etu = '$id'";

    if ($conn->query($sql3) === TRUE) {
        // Vérification du nombre de retards
        $sql1 = "SELECT nbr_retard FROM étudiant WHERE ID_etu = '$id'";
        $result = $conn->query($sql1);
        $row = $result->fetch_assoc();

        // Si blocage nécessaire
        if ($row['nbr_retard'] >= 3) {
            $sql = "UPDATE étudiant SET statue_etu = 0 WHERE ID_etu = '$id'";
            $conn->query($sql);
            echo "<script>alert('Utilisateur bloqué !');window.location='admin_etudiant.php';</script>";
            exit();
        } else {
            echo "<script>alert('Utilisateur pénalisé !');window.location='admin_etudiant.php';</script>";
            exit();
        }

    } else {
        echo "Erreur : " . $conn->error;
    }
}

    if (isset($_POST['annul'])) {

    $id = htmlspecialchars($_POST['id_ann']);
      $sql="SELECT nbr_retard FROM étudiant WHERE ID_etu = '$id'";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      if($row['nbr_retard'] <= 0) {
        echo "<script>alert('La pénalité ne peut pas être négative !');window.location='admin_etudiant.php';</script>";
        exit();
      }
      else{
        if($row['nbr_retard'] >=3){
          $sql3 = "UPDATE étudiant SET nbr_retard = 0 WHERE ID_etu = '$id'";
          $conn->query($sql3);
          $sql = "UPDATE étudiant SET statue_etu = 1 WHERE ID_etu = '$id'";
          $conn->query($sql);
            echo "<script>alert('Utilisateur débloqué !');window.location='admin_etudiant.php';</script>";
            exit();
        }
        else{
    // Annulation de pénalisation
    $sql3 = "UPDATE étudiant SET nbr_retard = nbr_retard - 1 WHERE ID_etu = '$id'";
    $conn->query($sql3);
            echo "<script>alert('Pénalité réduit de 1 pour ce utilisateur !');window.location='admin_etudiant.php';</script>";
            exit();}
}
}

$modal_open = false;
$selected_student = "";

if (isset($_POST['ouvrir_modal_mess'])) {
    $modal_open = true;
    $selected_student = $_POST['id_mess'];
}


$modal_open = false;
$selected_student = "";

if (isset($_POST['ouvrir_modal_mess'])) {
    $modal_open = true;
    $selected_student = $_POST['id_mess'];
}

// Envoyer le message
if (isset($_POST['envoyer_message'])) {
    $id = $_POST['msg_id'];
    $msg = $_POST['msg_text'];

    $sql = "INSERT INTO message_etu (mess, id_etu) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $msg, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Message envoyé !');</script>";
    } else {
        echo "<script>alert('Erreur lors de l\\'envoi');</script>";
    }
}



if(isset($_POST['me'])){
    $alert_test = htmlspecialchars($_POST['alert_text']);
    $sql = "INSERT INTO message_etu (mess, id_etu) VALUES ('$alert_test',NULL)";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Alerte envoyée à tous les étudiants !');window.location='admin_etudiant.php';</script>";
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
      <button id="btnAddStudent">+ Ajouter/Modifier un étudiant</button>
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



<!-- Section -->

<section  id="panelStudents" >
      <h2>Gestion des étudiants</h2>
      <form method='POST' action='admin_etudiant.php' style='display:inline;'>
                    <input type="text" name="rech" placeholder="Rechercher par email ou ID_étudiant" value="<?php echo (isset($_POST['rechercher']) && isset($_POST['rech'])) ? htmlspecialchars($_POST['rech']) : ''; ?>">
                    <button type="submit" name="rechercher">Rechercher</button>
                    <button type="submit" name="annuler">Annuler</button>
        </form>
      
      <table class="table" id="studentsTable" border="1">
  <tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Prénom</th>
    <th>Email</th>
    <th>Pénalités</th>
    <th>Statut</th>
    <th>password</th>
    <th>Actions</th>
  </tr>
  <?php
  // Logique de recherche ou affichage complet
  if (isset($_POST['rechercher']) && !empty($_POST['rech'])) {
      // Mode recherche
      $email = $conn->real_escape_string($_POST['rech']);
      $id = $conn->real_escape_string($_POST['rech']);
      $sql = "SELECT * FROM étudiant WHERE Email='$email' OR ID_etu='$id'";
  } else {
      // Mode par défaut : afficher tous les étudiants
      $sql = "SELECT * FROM étudiant";
  }

  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . $row['ID_etu'] . "</td>";
          echo "<td>" . $row['nom'] . "</td>";
          echo "<td>" . $row['Prénom'] . "</td>";
          echo "<td>" . $row['Email'] . "</td>";
          echo "<td>" . $row['nbr_retard'] . "</td>";
          echo "<td>" . $row['statue_etu'] . "</td>";
          echo "<td>" . $row['mdp'] . "</td>";
          echo "<td>";
          echo "
                  <form method='POST' action='admin_etudiant.php' style='display:inline;' 
                    onsubmit=\"return confirm('Voulez-vous vraiment pénaliser cet utilisateur ?');\">
                    <input type='hidden' name='id_pen' value='" . htmlspecialchars($row['ID_etu']) . "'>
                    <button type='submit' name='penaliser' class='btn-penalize' title='Pénaliser'>⚠️</button>
                  </form>
                ";
                echo "
                  <form method='POST' action='admin_etudiant.php' style='display:inline;' 
                    onsubmit=\"return confirm('Voulez-vous vraiment annuler la pénalité ?');\">
                    <input type='hidden' name='id_ann' value='" . htmlspecialchars($row['ID_etu']) . "'>
                    <button type='submit' name='annul' class='btn-reset' title='Réduire pénalité'>🔄</button>
                  </form>
                ";
                echo "
                  <form method='POST' action='admin_etudiant.php' style='display:inline;' 
                    onsubmit=\"return confirm('Voulez-vous vraiment supprimer cet utilisateur ?');\">
                    <input type='hidden' name='id_supp' value='" . htmlspecialchars($row['ID_etu']) . "'>
                    <button type='submit' name='supp' class='btn-reset' title='Supprimer'>🗑️</button>
                  </form>
                ";
                echo " 
                <form method='POST' action='admin_etudiant.php' style='display:inline;' 
    onsubmit=\"return confirm('Voulez-vous vraiment envoyer un message à cet utilisateur ?');\">

    <input type='hidden' name='id_mess' value='".htmlspecialchars($row['ID_etu']) . "'>

    <button type='submit' name='ouvrir_modal_mess' class='btn-reset' title='Message'>✉️</button>
</form>

                   ";
                echo "</td>";
                echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='8'>Aucun utilisateur trouvé</td></tr>";
  }
  ?>
</table>
    </section>








    </main>
<!--Modals-->
<div class="modal" id="modalStudent">
    <div class="modal-content">
      <h3 id="modalStudentTitle">Ajouter un étudiant</h3>
      <form id="formStudent" method="POST" action="admin_etudiant.php">
        <input type="text" name="nom" placeholder="Nom" required>
                <input type="text" name="prenom" placeholder="Prénom" required>
                <input type="text" name="id_etudiant" placeholder="ID Étudiant" required>
                <input type="email" name="email" placeholder="Email universitaire" required>
                <input type="password" name="mot_de_passe" placeholder="Mot de passe" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Le mot de passe doit contenir au moins 8 caractères, dont au moins une lettre et un chiffre" required>
                <input type="password" name="cmdp" placeholder="Confirmer le mot de passe" minlength="8" required>
        <div class="modal-actions">
          <button type="submit" name="inscr">Enregistrer</button>
          <button type="submit" name="modif">Modifier</button>
          <button type="button" class="close-modal">Annuler</button>
        </div>
      </form>
    </div>
  </div>






<?php

if ($modal_open) {
    // récupérer le nom de l'étudiant
    $sql_et = "SELECT nom, Prénom FROM étudiant WHERE ID_etu = ?";
    $stmt_et = $conn->prepare($sql_et);
    $stmt_et->bind_param("s", $selected_student);
    $stmt_et->execute();
    $res_et = $stmt_et->get_result();
    $et = $res_et->fetch_assoc();
?>
<div class="modal" id="modalMessage" style="display:block;">
  <div class="modal-content">
    <h3>Envoyer un message à <?= htmlspecialchars($et['nom'] . " " . $et['Prénom']) ?></h3>

    <form method="POST" action="admin_etudiant.php">
      <input type="hidden" name="msg_id" value="<?= htmlspecialchars($selected_student) ?>">
      <textarea name="msg_text" placeholder="Message..." required></textarea>

      <div class="modal-actions">
        <button type="submit" name="envoyer_message">Envoyer</button>
        <button type="button" onclick="document.getElementById('modalMessage').style.display='none';">
          Fermer
        </button>
      </div>
    </form>
  </div>
</div>
<?php
}
?>


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


    document.getElementById('btnAddStudent').addEventListener('click',()=>{ 
      document.getElementById('modalStudentTitle').textContent='Ajouter un étudiant';
      document.getElementById('formStudent').reset();
      document.getElementById('modalStudent').style.display='flex';
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