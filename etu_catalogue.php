<?php 
include 'config2.php';
    session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.php');
        exit();
    } 
    $etudiant_nom = $_SESSION['etudiant_nom'];
    $id_etudiant = $_SESSION['id_etudiant'];

    $activePage = 'catalogue';


    if (isset($_POST['reserv'])) {

    $isbn = intval($_POST['isbn']);
    $id_etudiant = htmlspecialchars($_POST['id_etu']);

    // 1️⃣ Vérifier si l'étudiant a déjà réservé CE livre
    $sql_check_same = "SELECT COUNT(*) AS count FROM reservation 
                       WHERE ISBN = $isbn AND ID_etu = '$id_etudiant' 
                       AND statut = 'en_attente'";
    $result_same = $conn->query($sql_check_same);
    $row_same = $result_same->fetch_assoc();

    if ($row_same['count'] > 0) {
        echo "<script>alert('Vous avez déjà une réservation en attente pour ce livre.'); 
              window.location='etu_catalogue.php';</script>";
        exit();
    }

    // 2️⃣ Vérifier si l'étudiant a déjà 2 réservations en attente 
    $sql_check_total = "SELECT COUNT(*) AS total FROM reservation 
                        WHERE ID_etu = '$id_etudiant' 
                        AND statut = 'en_attente'";
    $result_total = $conn->query($sql_check_total);
    $row_total = $result_total->fetch_assoc();

    if ($row_total['total'] >= 2) {
        echo "<script>alert('Vous avez déjà 2 réservations en attente, impossible d\\'en ajouter une autre.'); 
              window.location='etu_catalogue.php';</script>";
        exit();
    }

    // 3️⃣ Ajouter la réservation
    $sql_insert = "INSERT INTO reservation (ISBN, ID_etu) VALUES ($isbn, '$id_etudiant')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "<script>alert('Réservation réussie !'); window.location='etu_catalogue.php';</script>";
        exit();
    } else {
        echo "Erreur: " . $conn->error;
    }
}

if (isset($_POST['att'])) {

    $isbn = intval($_POST['isbn']);
    $id_etudiant = htmlspecialchars($_POST['id_etu']);

    // 1️⃣ Vérifier si l'étudiant a déjà rejoint la liste d'attente CE livre
    $sql_check_same = "SELECT COUNT(*) AS count FROM liste_att
                       WHERE ISBN = $isbn AND ID_etu = '$id_etudiant'";
    $result_same = $conn->query($sql_check_same);
    $row_same = $result_same->fetch_assoc();

    if ($row_same['count'] > 0) {
        echo "<script>alert('Vous avez déjà rejoint la liste d\\'attente pour ce livre.'); 
              window.location='etu_catalogue.php';</script>";
        exit();
    }

    // 2️⃣ Ajouter à la liste d'attente
    $sql_insert = "INSERT INTO liste_att (ISBN, ID_etu) VALUES ($isbn, '$id_etudiant')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "<script>alert('Vous avez rejoint la liste d\\'attente avec succès !'); window.location='etu_catalogue.php';</script>";
        exit();
    } else {
        echo "Erreur: " . $conn->error;
    }
}

if (isset($_POST['eval']) && isset($_POST['eval_l']) && isset($_POST['note'])) {
    $isbn = intval($_POST['eval_l']);
    $note = intval($_POST['note']);
    $id_etudiant = $_SESSION['id_etudiant'];


    // Vérifier que la note est valide (1 à 5)
    if ($note < 1 || $note > 5) {
        echo "<script>alert('La note doit être entre 1 et 5 !'); window.location='etu_catalogue.php';</script>";
        exit();
    }

    // Optionnel : vérifier si l'étudiant a déjà évalué ce livre

    

    // Insérer la note
    $sql_up = "INSERT INTO evaluation (ISBN, vote_tot, ID_etu) VALUES ($isbn,$note,'$id_etudiant')";
    if ($conn->query($sql_up) === TRUE) {
        // Optionnel : enregistrer l'évaluation de l'étudiant
        $sql_insert_eval = "UPDATE livre SET note=(SELECT AVG(vote_tot) FROM evaluation WHERE ISBN=$isbn) WHERE ISBN=$isbn";
        $conn->query($sql_insert_eval);

        echo "<script>alert('Merci pour votre évaluation !'); window.location='etu_catalogue.php';</script>";
        exit();
    } else {
        echo "Erreur: " . $conn->error;
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

<section id="catalogue" >
    <h2>Rechercher un livre</h2>
    <form method="GET" action="">
      <div class="filters">
        <input type="text" id="searchTitle" name="titre" placeholder="Titre du livre..." value="<?php echo isset($_GET['titre']) ? htmlspecialchars($_GET['titre']) : ''; ?>">
        
        <select id="categoryFilter" name="categorie">
          <option value="">Catégorie</option>
          <?php
          // Récupérer toutes les catégories
          $sqlCat = "SELECT * FROM categorie";
          $resultCat = $conn->query($sqlCat);
          while($cat = $resultCat->fetch_assoc()):
          ?>
            <option value="<?php echo $cat['ID_cat']; ?>" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == $cat['ID_cat']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat['libelle']); ?>
            </option>
          <?php endwhile; ?>
        </select>
        
        <input type="text" id="authorFilter" name="auteur" placeholder="Auteur..." value="<?php echo isset($_GET['auteur']) ? htmlspecialchars($_GET['auteur']) : ''; ?>">
        
        <button type="submit" id="searchBtn">🔍 Rechercher</button>
        
        <?php if(isset($_GET['titre']) || isset($_GET['categorie']) || isset($_GET['auteur'])): ?>
          <a href="?" style="text-decoration: none;">
            <button type="button" id="cancelBtn">❌ Annuler</button>
          </a>
        <?php endif; ?>
      </div>
    </form>

<?php 
// Construction de la requête SQL avec filtres
$sql = "SELECT l.*, c.libelle AS nom_categorie 
        FROM livre l 
        LEFT JOIN categorie c ON l.ID_cat = c.ID_cat WHERE 1=1";

$conditions = array();
$types = "";
$params = array();

// Filtre par titre
if(isset($_GET['titre']) && !empty($_GET['titre'])) {
    $sql .= " AND l.titre LIKE ?";
    $types .= "s";
    $params[] = "%" . $_GET['titre'] . "%";
}

// Filtre par catégorie
if(isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $sql .= " AND l.ID_cat = ?";
    $types .= "s";
    $params[] = $_GET['categorie'];
}

// Filtre par auteur
if(isset($_GET['auteur']) && !empty($_GET['auteur'])) {
    $sql .= " AND l.auteur LIKE ?";
    $types .= "s";
    $params[] = "%" . $_GET['auteur'] . "%";
}

// Exécution de la requête
if(!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}
?>
      
<div class="book-list">
<?php 
if($result->num_rows > 0):
    while($livre = $result->fetch_assoc()): 
        // Déterminer si le livre est disponible
        $disponible = ($livre['nbr_exemp'] - $livre['nbr_empr']) > 0;
        $bookID = intval($livre['ISBN']); // identifiant unique pour chaque livre
?>
    
    <?php
// Calculer la disponibilité
$disponible = ($livre['nbr_exemp'] - $livre['nbr_empr']) > 0 ? true : false;

// Mettre à jour la colonne statut_liv dans la table livre
$statut = $disponible ? 'Disponible' : 'Indisponible';
$sql_update_statut = "UPDATE livre SET statue_liv = ? WHERE ISBN = ?";
$stmt_update_statut = $conn->prepare($sql_update_statut);
$stmt_update_statut->bind_param("si", $statut, $livre['ISBN']);
$stmt_update_statut->execute();
$stmt_update_statut->close();
?>

<div class="book-card <?php echo $disponible ? 'disponible' : 'indisponible'; ?>">
    <img src="<?php echo htmlspecialchars($livre['imag']); ?>" alt="Livre">
    <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
    <p>Auteur : <?php echo htmlspecialchars($livre['auteur']); ?></p>
    <p>Catégorie : <?php echo htmlspecialchars($livre['nom_categorie']); ?></p>

    <?php 
    // Affichage de la note sous forme d'étoiles
    $note = $livre['note'];
    if ($note) {
        $fullStars = floor($note);
        $halfStar = ($note - $fullStars) >= 0.5 ? true : false;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
        echo "<p>Note : ";
        for ($i = 0; $i < $fullStars; $i++) echo "⭐";
        if ($halfStar) echo "✬"; // demi-étoile
        for ($i = 0; $i < $emptyStars; $i++) echo "☆";
        echo " (" . round($note,1) . "/5)</p>";
    } else {
        echo "<p>Note : Non noté</p>";
    }
    ?>

    <?php if($disponible): ?>
        <p class="status available">✅ Disponible</p>
        <form method='POST' action='etu_catalogue.php' style='display:inline;' 
            onsubmit="return confirm('Voulez-vous vraiment réserver ce livre ?');">
            <input type='hidden' name='isbn' value='<?php echo $bookID; ?>'>
            <input type='hidden' name='id_etu' value='<?php echo htmlspecialchars($id_etudiant); ?>'>
            <button type='submit' name='reserv' class='btn-delete' title='Réserver'>Réservé</button>
        </form>
    <?php else: ?>
        <p class="status unavailable">❌ Indisponible</p>
        <form method='POST' action='etu_catalogue.php' style='display:inline;' 
            onsubmit="return confirm('Voulez-vous vraiment rejoindre la liste d\'attente de ce livre ?');">
            <input type='hidden' name='isbn' value='<?php echo $bookID; ?>'>
            <input type='hidden' name='id_etu' value='<?php echo htmlspecialchars($id_etudiant); ?>'>
            <button type='submit' name='att' class='btn-delete' title='Liste d\'attente'>Rejoindre la liste d'attente</button>
        </form>
    <?php endif; ?>

    <!-- Bouton pour ouvrir le modal d'évaluation -->
    <button class='btn-delete eval-btn' data-isbn='<?php echo $bookID; ?>' title='Évaluer'>Évaluer</button>
</div>


<?php 
    endwhile;
else:
?>
    <p style="text-align: center; width: 100%; padding: 20px;">Aucun livre trouvé avec ces critères.</p>
<?php endif; ?>
</div>

</section>


<div class="modal" id="modalEval">
  <div class="modal-content">
    <h3>Évaluer le livre</h3>
    <form id="formEval" method="POST" action="etu_catalogue.php">
        <input type="hidden" name="eval_l" id="eval_l" value="">
        <label for="note">Donnez une note :</label>
        <select name="note" id="note" required>
            <option value="">-- Choisir une note --</option>
            <option value="1">1 ⭐</option>
            <option value="2">2 ⭐⭐</option>
            <option value="3">3 ⭐⭐⭐</option>
            <option value="4">4 ⭐⭐⭐⭐</option>
            <option value="5">5 ⭐⭐⭐⭐⭐</option>
        </select>
        <div class="modal-actions">
            <button type="submit" name="eval">Enregistrer</button>
            <button type="button" class="close-modal">Annuler</button>
        </div>
    </form>
  </div>
</div>


<style>

</style>

<script>
// Ouvrir le modal
// Ouvrir le modal pour le livre correspondant
document.querySelectorAll('.eval-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const isbn = btn.dataset.isbn;
        document.getElementById('eval_l').value = isbn; // mettre le bon ISBN dans le hidden
        document.getElementById('modalEval').style.display = 'block';
    });
});

// Fermer le modal
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.modal').style.display = 'none';
    });
});

// Fermer modal en cliquant en dehors
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

</script>



</main>

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


  // Réserver / liste d'attente / évaluation
  document.querySelectorAll('.reserve-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      toast('Livre réservé avec succès !');
      btn.disabled = true;
    });
  });
  document.querySelectorAll('.waitlist-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      toast('Ajouté à la liste d’attente');
      btn.disabled = true;
    });
  });
  document.querySelectorAll('.evaluer-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const note = prompt('Donnez une note (1-5)');
      if(note) toast('Merci pour votre évaluation !');
    });
  });

</script>

</body>
</html>
