<?php 
include 'config2.php';
$activePage = 'livres';
//actions
$categories = [];
$sql_categories = "SELECT ID_cat, libelle FROM categorie ORDER BY libelle";
$result_categories = $conn->query($sql_categories);
if ($result_categories->num_rows > 0) {
    while ($row = $result_categories->fetch_assoc()) {
        $categories[] = $row;
    }
}

if (isset($_POST['inscr_b'])) {
    // Validation et nettoyage des données
    $isbn = intval($_POST['isbn']);
    $titre = trim($_POST['titre']);
    $auteur = trim($_POST['auteur']);
    $ID_cat = trim($_POST['ID_cat']);
    $nbr_exemp = intval($_POST['exemplaires']);
    $date_publication = $_POST['date_publication'];
    
    // Validation basique
    if ($isbn <= 0 || empty($titre) || empty($auteur) || empty($ID_cat) || $nbr_exemp <= 0) {
        echo "<script>alert('Erreur : Données invalides !');</script>";
        exit;
    }
    
    // ⭐ VÉRIFIER QUE LA CATÉGORIE EXISTE (ajouté ici)
    $sqlCat = "SELECT ID_cat FROM categorie WHERE ID_cat = ?";
    $stmtCat = $conn->prepare($sqlCat);
    $stmtCat->bind_param("s", $ID_cat);
    $stmtCat->execute();
    $resultCat = $stmtCat->get_result();
    
    if ($resultCat->num_rows == 0) {
        echo "<script>alert('Erreur : La catégorie sélectionnée n\\'existe pas !');</script>";
        $stmtCat->close();
        exit;
    }
    $stmtCat->close();
    
    // Validation de la date
    $date = DateTime::createFromFormat('Y-m-d', $date_publication);
    if (!$date || $date->format('Y-m-d') !== $date_publication) {
        echo "<script>alert('Erreur : Date invalide !');</script>";
        exit;
    }
    
    // Vérification du fichier
    if (empty($_FILES["file"]["name"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Erreur : Aucun fichier valide sélectionné !');</script>";
        exit;
    }
    
    // Vérification de la taille (5MB max)
    if ($_FILES["file"]["size"] > 5 * 1024 * 1024) {
        echo "<script>alert('Erreur : Fichier trop volumineux (max 5MB) !');</script>";
        exit;
    }
    
    // Vérification du type MIME réel
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $_FILES["file"]["tmp_name"]);
    finfo_close($finfo);
    
    $allowedMimes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (!in_array($mimeType, $allowedMimes)) {
        echo "<script>alert('Erreur : Type de fichier non autorisé !');</script>";
        exit;
    }
    
    // Dossier cible
    $targetDir = "fichier/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Générer un nom unique pour éviter les conflits
    $fileExt = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
    $fileName = uniqid('livre_', true) . '.' . $fileExt;
    $targetFile = $targetDir . $fileName;
    
    // Vérifier si le livre existe déjà AVANT l'upload
    $sql1 = "SELECT ISBN FROM livre WHERE ISBN = ?";
    $stmt = $conn->prepare($sql1);
    $stmt->bind_param("i", $isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<script>alert('ERREUR : Livre déjà présent !');</script>";
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Upload du fichier
    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        // Insertion sécurisée avec requête préparée
        $sql = "INSERT INTO livre (ISBN, titre, auteur, ID_cat, année_par, nbr_exemp, nbr_empr, statue_liv, note, imag) 
                VALUES (?, ?, ?, ?, ?, ?, 0, 'Disponible', 0, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssis", $isbn, $titre, $auteur, $ID_cat, $date_publication, $nbr_exemp, $targetFile);
        
        if ($stmt->execute()) {
            echo "<script>alert('Ajout réussi !'); window.location='admin_livre.php';</script>";
            $stmt->close();
            exit;
        } else {
            // Supprimer le fichier uploadé en cas d'erreur SQL
            unlink($targetFile);
            echo "<script>alert('Erreur lors de l\\'insertion en base de données : " . htmlspecialchars($conn->error) . "');</script>";
            $stmt->close();
        }
    } else {
        echo "<script>alert('Erreur lors de l\\'upload du fichier !');</script>";
    }
}

if (isset($_POST['modif_b'])){
    $isbn = intval($_POST['isbn']);
    $titre = trim($_POST['titre']);
    $auteur = trim($_POST['auteur']);
    $ID_cat = trim($_POST['ID_cat']);
    $nbr_exemp = intval($_POST['exemplaires']);
    $date_publication = $_POST['date_publication'];
        // Mise à jour d'un utilisateur existant
        $sql = "SELECT * FROM livre WHERE ISBN=$isbn";
        $result = $conn->query($sql);

        if ($result->num_rows > 0){
          
        $sql = "UPDATE livre 
                SET titre='$titre', auteur='$auteur', ID_cat='$ID_cat', année_par='$date_publication', nbr_exemp='$nbr_exemp' 
                WHERE ISBN='$isbn'";

        if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Modification éffectuée avec succès !');window.location='admin_livre.php';</script>";
        exit();
    } else {
        echo "Erreur : " . $conn->error;
    }}
      else{
        echo "<script>alert('ERREUR! :Impossible de modifier, ISBN non trouvé !');</script>";}
    }

if (isset($_POST['supp_b'])) {
    $isbn = intval($_POST['id_supp_b']);
    $sql2 = "DELETE FROM livre WHERE ISBN='$isbn'";
    if ($conn->query($sql2) === TRUE) {
        echo "<script>alert('Livre supprimé avec succès !');window.location='admin_livre.php';</script>";
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
      <button id="btnAddBook">+ Ajouter/Modifier un livre</button>
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

    <section id="Books" >
      <h2>Gestion des livres</h2>
      <form method='POST' action='admin_livre.php' style='display:inline;'>
                    <input type="text" name="rech_b" placeholder="Rechercher par ISBN, titre, auteur ou catégorie" value="<?php echo (isset($_POST['rechercher_b']) && isset($_POST['rech_b'])) ? htmlspecialchars($_POST['rech_b']) : ''; ?>">
                    <button type="submit" name="rechercher_b">Rechercher</button>
                    <button type="submit" name="annuler_b">Annuler</button>
        </form>
      <table class="table" id="booksTable" border="1">
        <tr>
          <th>ISBN</th>
          <th>Titre</th>
          <th>Auteur</th>
          <th>Catégorie</th>
          <th>Nombre d'exemplaires</th>
          <th>Nombre d'emprunts</th>
          <th>Statut</th>
          <th>Note</th>
          <th>Actions</th>
  </tr>
  <?php
  // Logique de recherche ou affichage complet
  if (isset($_POST['rechercher_b']) && !empty($_POST['rech_b'])) {
      // Mode recherche
      $titre = $conn->real_escape_string($_POST['rech_b']);
      $isbn = intval($_POST['rech_b']);

      $n= "SELECT * FROM livre WHERE ISBN=$isbn";
      $result = $conn->query($n);
      if ($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if($row['nbr_exemp']-$row['nbr_empr']<=0){
          $sql = "UPDATE livre SET statue_liv='Indisponible' WHERE ISBN=$isbn";
          $conn->query($sql);
        }
        else{
          $sql = "UPDATE livre SET statue_liv='Disponible' WHERE ISBN=$isbn";
          $conn->query($sql);
        }
      }

      $sql = "SELECT l.ISBN,l.titre,l.auteur,c.libelle,l.nbr_exemp,l.nbr_empr,l.statue_liv,l.note FROM livre l JOIN categorie c ON l.ID_cat = c.ID_cat WHERE l.ISBN=$isbn OR l.titre='$titre' OR l.auteur='$titre' OR c.libelle='$titre'";
  } else {
      // Mode par défaut : afficher tous les étudiants
      $sql = "SELECT l.ISBN,l.titre,l.auteur,c.libelle,l.nbr_exemp,l.nbr_empr,l.statue_liv,l.note FROM livre l JOIN categorie c ON l.ID_cat = c.ID_cat";
  }

  $result = $conn->query($sql);

  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          echo "<tr>";
          echo "<td>" . $row['ISBN'] . "</td>";
          echo "<td>" . $row['titre'] . "</td>";
          echo "<td>" . $row['auteur'] . "</td>";
          echo "<td>" . $row['libelle'] . "</td>";
          echo "<td>" . $row['nbr_exemp'] . "</td>";
          echo "<td>" . $row['nbr_empr'] . "</td>";
          echo "<td>" . $row['statue_liv'] . "</td>";
          echo "<td>" . $row['note'] . "</td>";
          echo "<td>";
          echo "
                  <form method='POST' action='admin_livre.php' style='display:inline;' 
                    onsubmit=\"return confirm('Voulez-vous vraiment supprimer cet livre ?');\">
                    <input type='hidden' name='id_supp_b' value='" . htmlspecialchars($row['ISBN']) . "'>
                    <button type='submit' name='supp_b' class='btn-delete' title='Supprimer'>🗑️</button>
                  </form>
                ";
                echo "</td>";
                echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='9'>Aucun livre trouvé</td></tr>";
  }
  ?>

      </table>
    </section>


<!-- Section -->










    </main>
<!--Modals-->


<div class="modal" id="modalBook">
    <div class="modal-content">
      <h3 id="modalBookTitle">Ajouter un livre</h3>
      <form id="formBook" method="POST" action="admin_livre.php" enctype="multipart/form-data">
        <input type="text" name="isbn" placeholder="ISBN" required>
        <input type="text" name="titre" placeholder="Titre" required>
        <input type="text" name="auteur" placeholder="Auteur" required>
        <input type="number" name="exemplaires" placeholder="Nombre d'exemplaires" min="1" required>
        <label for="date_publication">Date de publication :</label>
        <input type="date" name="date_publication" placeholder="Date de publication" required>
        <select name="ID_cat" required>
            <option value="">-- Choisir une catégorie --</option>
            <?php foreach ($categories as $categorie): ?>
                <option value="<?php echo htmlspecialchars($categorie['ID_cat']); ?>"
                    <?php echo (isset($book_to_edit) && $book_to_edit['ID_cat'] == $categorie['ID_cat']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($categorie['libelle']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <label for="file">Page de garde :</label>
        <input type="file" id="file" name="file" required accept=".pdf,.jpg,.png,.jpeg">
        <div class="modal-actions">
          <button type="submit" name="inscr_b">Enregistrer</button>
          <button type="submit" name="modif_b">Modifier</button>
          <button type="button" class="close-modal">Annuler</button>
        </div>
      </form>
    </div>
</div>









    <div class="toast" id="toast"></div>

    <style>


</style>


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

    document.getElementById('btnAddBook').addEventListener('click',()=>{ 
      document.getElementById('modalBookTitle').textContent='Ajouter un livre';
      document.getElementById('formBook').reset();
      document.getElementById('modalBook').style.display='flex';
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