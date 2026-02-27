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

    // 1️⃣ Check if the student has already reserved THIS book
    $sql_check_same = "SELECT COUNT(*) AS count FROM reservation 
                       WHERE ISBN = $isbn AND ID_etu = '$id_etudiant' 
                       AND statut = 'en_attente'";
    $result_same = $conn->query($sql_check_same);
    $row_same = $result_same->fetch_assoc();

    if ($row_same['count'] > 0) {
        echo "<script>alert('You already have a pending reservation for this book.'); 
              window.location='etu_catalogue.php';</script>";
        exit();
    }

    // 2️⃣ Check if the student already has 2 pending reservations
    $sql_check_total = "SELECT COUNT(*) AS total FROM reservation 
                        WHERE ID_etu = '$id_etudiant' 
                        AND statut = 'en_attente'";
    $result_total = $conn->query($sql_check_total);
    $row_total = $result_total->fetch_assoc();

    if ($row_total['total'] >= 2) {
        echo "<script>alert('You already have 2 pending reservations, you cannot add another one.'); 
              window.location='etu_catalogue.php';</script>";
        exit();
    }

    // 3️⃣ Add the reservation
    $sql_insert = "INSERT INTO reservation (ISBN, ID_etu) VALUES ($isbn, '$id_etudiant')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "<script>alert('Reservation successful!'); window.location='etu_catalogue.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_POST['att'])) {

    $isbn = intval($_POST['isbn']);
    $id_etudiant = htmlspecialchars($_POST['id_etu']);

    // 1️⃣ Check if the student has already joined the waiting list for THIS book
    $sql_check_same = "SELECT COUNT(*) AS count FROM liste_att
                       WHERE ISBN = $isbn AND ID_etu = '$id_etudiant'";
    $result_same = $conn->query($sql_check_same);
    $row_same = $result_same->fetch_assoc();

    if ($row_same['count'] > 0) {
        echo "<script>alert('You have already joined the waiting list for this book.'); 
              window.location='etu_catalogue.php';</script>";
        exit();
    }

    // 2️⃣ Add to waiting list
    $sql_insert = "INSERT INTO liste_att (ISBN, ID_etu) VALUES ($isbn, '$id_etudiant')";

    if ($conn->query($sql_insert) === TRUE) {
        echo "<script>alert('You have successfully joined the waiting list!'); window.location='etu_catalogue.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

if (isset($_POST['eval']) && isset($_POST['eval_l']) && isset($_POST['note'])) {
    $isbn = intval($_POST['eval_l']);
    $note = intval($_POST['note']);
    $id_etudiant = $_SESSION['id_etudiant'];


    // Check that the rating is valid (1 to 5)
    if ($note < 1 || $note > 5) {
        echo "<script>alert('The rating must be between 1 and 5!'); window.location='etu_catalogue.php';</script>";
        exit();
    }

    // Optional: check if the student has already rated this book

    

    // Insert the rating
    $sql_up = "INSERT INTO evaluation (ISBN, vote_tot, ID_etu) VALUES ($isbn,$note,'$id_etudiant')";
    if ($conn->query($sql_up) === TRUE) {
        // Optional: save the student's evaluation
        $sql_insert_eval = "UPDATE livre SET note=(SELECT AVG(vote_tot) FROM evaluation WHERE ISBN=$isbn) WHERE ISBN=$isbn";
        $conn->query($sql_insert_eval);

        echo "<script>alert('Thank you for your rating!'); window.location='etu_catalogue.php';</script>";
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

<section id="catalogue" >
    <h2>Search for a book</h2>
    <form method="GET" action="">
      <div class="filters">
        <input type="text" id="searchTitle" name="titre" placeholder="Book title..." value="<?php echo isset($_GET['titre']) ? htmlspecialchars($_GET['titre']) : ''; ?>">
        
        <select id="categoryFilter" name="categorie">
          <option value="">Category</option>
          <?php
          // Retrieve all categories
          $sqlCat = "SELECT * FROM categorie";
          $resultCat = $conn->query($sqlCat);
          while($cat = $resultCat->fetch_assoc()):
          ?>
            <option value="<?php echo $cat['ID_cat']; ?>" <?php echo (isset($_GET['categorie']) && $_GET['categorie'] == $cat['ID_cat']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat['libelle']); ?>
            </option>
          <?php endwhile; ?>
        </select>
        
        <input type="text" id="authorFilter" name="auteur" placeholder="Author..." value="<?php echo isset($_GET['auteur']) ? htmlspecialchars($_GET['auteur']) : ''; ?>">
        
        <button type="submit" id="searchBtn">🔍 Search</button>
        
        <?php if(isset($_GET['titre']) || isset($_GET['categorie']) || isset($_GET['auteur'])): ?>
          <a href="?" style="text-decoration: none;">
            <button type="button" id="cancelBtn">❌ Cancel</button>
          </a>
        <?php endif; ?>
      </div>
    </form>

<?php 
// Build SQL query with filters
$sql = "SELECT l.*, c.libelle AS nom_categorie 
        FROM livre l 
        LEFT JOIN categorie c ON l.ID_cat = c.ID_cat WHERE 1=1";

$conditions = array();
$types = "";
$params = array();

// Filter by title
if(isset($_GET['titre']) && !empty($_GET['titre'])) {
    $sql .= " AND l.titre LIKE ?";
    $types .= "s";
    $params[] = "%" . $_GET['titre'] . "%";
}

// Filter by category
if(isset($_GET['categorie']) && !empty($_GET['categorie'])) {
    $sql .= " AND l.ID_cat = ?";
    $types .= "s";
    $params[] = $_GET['categorie'];
}

// Filter by author
if(isset($_GET['auteur']) && !empty($_GET['auteur'])) {
    $sql .= " AND l.auteur LIKE ?";
    $types .= "s";
    $params[] = "%" . $_GET['auteur'] . "%";
}

// Execute query
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
        // Determine if the book is available
        $disponible = ($livre['nbr_exemp'] - $livre['nbr_empr']) > 0;
        $bookID = intval($livre['ISBN']); // unique identifier for each book
?>
    
    <?php
// Calculate availability
$disponible = ($livre['nbr_exemp'] - $livre['nbr_empr']) > 0 ? true : false;

// Update the statut_liv column in the livre table
$statut = $disponible ? 'Available' : 'Unavailable';
$sql_update_statut = "UPDATE livre SET statue_liv = ? WHERE ISBN = ?";
$stmt_update_statut = $conn->prepare($sql_update_statut);
$stmt_update_statut->bind_param("si", $statut, $livre['ISBN']);
$stmt_update_statut->execute();
$stmt_update_statut->close();
?>

<div class="book-card <?php echo $disponible ? 'disponible' : 'indisponible'; ?>">
    <img src="<?php echo htmlspecialchars($livre['imag']); ?>" alt="Book">
    <h3><?php echo htmlspecialchars($livre['titre']); ?></h3>
    <p>Author: <?php echo htmlspecialchars($livre['auteur']); ?></p>
    <p>Category: <?php echo htmlspecialchars($livre['nom_categorie']); ?></p>

    <?php 
    // Display rating as stars
    $note = $livre['note'];
    if ($note) {
        $fullStars = floor($note);
        $halfStar = ($note - $fullStars) >= 0.5 ? true : false;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
        echo "<p>Rating: ";
        for ($i = 0; $i < $fullStars; $i++) echo "⭐";
        if ($halfStar) echo "✬"; // half star
        for ($i = 0; $i < $emptyStars; $i++) echo "☆";
        echo " (" . round($note,1) . "/5)</p>";
    } else {
        echo "<p>Rating: Not rated</p>";
    }
    ?>

    <?php if($disponible): ?>
        <p class="status available">✅ Available</p>
        <form method='POST' action='etu_catalogue.php' style='display:inline;' 
            onsubmit="return confirm('Do you really want to reserve this book?');">
            <input type='hidden' name='isbn' value='<?php echo $bookID; ?>'>
            <input type='hidden' name='id_etu' value='<?php echo htmlspecialchars($id_etudiant); ?>'>
            <button type='submit' name='reserv' class='btn-delete' title='Reserve'>Reserve</button>
        </form>
    <?php else: ?>
        <p class="status unavailable">❌ Unavailable</p>
        <form method='POST' action='etu_catalogue.php' style='display:inline;' 
            onsubmit="return confirm('Do you really want to join the waiting list for this book?');">
            <input type='hidden' name='isbn' value='<?php echo $bookID; ?>'>
            <input type='hidden' name='id_etu' value='<?php echo htmlspecialchars($id_etudiant); ?>'>
            <button type='submit' name='att' class='btn-delete' title='Waiting list'>Join the waiting list</button>
        </form>
    <?php endif; ?>

    <!-- Button to open the rating modal -->
    <button class='btn-delete eval-btn' data-isbn='<?php echo $bookID; ?>' title='Rate'>Rate</button>
</div>


<?php 
    endwhile;
else:
?>
    <p style="text-align: center; width: 100%; padding: 20px;">No books found with these criteria.</p>
<?php endif; ?>
</div>

</section>


<div class="modal" id="modalEval">
  <div class="modal-content">
    <h3>Rate the book</h3>
    <form id="formEval" method="POST" action="etu_catalogue.php">
        <input type="hidden" name="eval_l" id="eval_l" value="">
        <label for="note">Give a rating:</label>
        <select name="note" id="note" required>
            <option value="">-- Choose a rating --</option>
            <option value="1">1 ⭐</option>
            <option value="2">2 ⭐⭐</option>
            <option value="3">3 ⭐⭐⭐</option>
            <option value="4">4 ⭐⭐⭐⭐</option>
            <option value="5">5 ⭐⭐⭐⭐⭐</option>
        </select>
        <div class="modal-actions">
            <button type="submit" name="eval">Save</button>
            <button type="button" class="close-modal">Cancel</button>
        </div>
    </form>
  </div>
</div>


<style>

</style>

<script>
// Open the modal
// Open the modal for the corresponding book
document.querySelectorAll('.eval-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const isbn = btn.dataset.isbn;
        document.getElementById('eval_l').value = isbn; // set the correct ISBN in hidden field
        document.getElementById('modalEval').style.display = 'block';
    });
});

// Close the modal
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.modal').style.display = 'none';
    });
});

// Close modal by clicking outside
window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

</script>



</main>

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


  // Reserve / waiting list / rating
  document.querySelectorAll('.reserve-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      toast('Book reserved successfully!');
      btn.disabled = true;
    });
  });
  document.querySelectorAll('.waitlist-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      toast('Added to the waiting list');
      btn.disabled = true;
    });
  });
  document.querySelectorAll('.evaluer-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const note = prompt('Give a rating (1-5)');
      if(note) toast('Thank you for your rating!');
    });
  });

</script>

</body>
</html>