<?php 
include 'config2.php';
$activePage = 'liste';
//actions

$books_with_waitlist = [];
$sql = "SELECT DISTINCT la.ISBN, l.titre 
        FROM liste_att la
        JOIN livre l ON la.ISBN = l.ISBN";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books_with_waitlist[] = $row; // tableau avec ISBN et titre
    }
}

$selected_isbn = $_POST['isbn'] ?? null;
$students_waiting = [];
$selected_title = '';

if ($selected_isbn) {
    // Récupérer les étudiants avec nom et prénom
    $sql = "SELECT la.ID_etu AS etudiant_id, e.nom, e.Prénom AS prenom, l.titre 
            FROM liste_att la
            JOIN étudiant e ON la.ID_etu = e.ID_etu
            JOIN livre l ON la.ISBN = l.ISBN
            WHERE la.ISBN = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_isbn);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $students_waiting[] = $row;
            $selected_title = $row['titre']; // récupérer le titre pour l'affichage
        }
    } else {
        // Si aucun étudiant, récupérer quand même le titre
        $stmt2 = $conn->prepare("SELECT titre FROM livre WHERE ISBN = ?");
        $stmt2->bind_param("i", $selected_isbn);
        $stmt2->execute();
        $res2 = $stmt2->get_result();
        if ($res2->num_rows > 0) {
            $row2 = $res2->fetch_assoc();
            $selected_title = $row2['titre'];
        }
        $stmt2->close();
    }
    $stmt->close();}
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


<section id="panelWaitlist" >
    <h2>Liste d'attente (par livre)</h2>

    <!-- Sélecteur de livres -->
    <label for="isbn">Choisir un livre :</label>
    <form method="POST" action="#panelWaitlist">
        <select name="isbn" id="isbn" onchange="this.form.submit()">
            <option value="">--Sélectionnez--</option>
            <?php foreach ($books_with_waitlist as $book): ?>
                <option value="<?= $book['ISBN'] ?>" <?= ($selected_isbn == $book['ISBN']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($book['titre'] . " (" . $book['ISBN'] . ")") ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Affichage de la table des étudiants -->
    <?php if ($selected_isbn): ?>
        <h3>Liste d'attente pour le livre <?= htmlspecialchars($selected_title . " (" . $selected_isbn . ")") ?> :</h3>

        <?php if (!empty($students_waiting)): ?>
            <table class="waitlist-table">
                <thead>
                    <tr>
                        <th>ID Étudiant</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students_waiting as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['etudiant_id']) ?></td>
                            <td><?= htmlspecialchars($student['nom']) ?></td>
                            <td><?= htmlspecialchars($student['prenom']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun étudiant en liste d'attente pour ce livre.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>

<style>
 
</style>







    </main>
<!--Modals-->












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