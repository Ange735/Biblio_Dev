<?php 
include 'config2.php';
    $activePage = 'emprunt';

session_start();
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: index.php');
        exit();
    } 
    $etudiant_nom = $_SESSION['etudiant_nom'];
    $id_etudiant = $_SESSION['id_etudiant'];

    $id_etudiant = $_SESSION['id_etudiant'];

$sql_emprunts = "
    SELECT 
        e.ISBN,
        e.date_empr,
        e.date_retour,
        l.titre
    FROM emprunt e
    JOIN livre l ON e.ISBN = l.ISBN
    WHERE e.ID_etu = ?
    ORDER BY e.date_empr DESC
";

$stmt = $conn->prepare($sql_emprunts);
$stmt->bind_param("s", $id_etudiant);
$stmt->execute();
$result_emprunts = $stmt->get_result();


if(isset($_POST['prolongerr'])) {
    $isbn = intval($_POST['isbn']);
    $id_etudiant = $_SESSION['id_etudiant'];

    //envoyer un message à l'admin pour prolonger
    $sql_msg = "INSERT INTO message_admin (id_etudiant, messag) VALUES (?, ?)";
    $contenu = "Demande de prolongation pour le livre ISBN: $isbn";
    $stmt_msg = $conn->prepare($sql_msg);
    $stmt_msg->bind_param("ss", $id_etudiant, $contenu);
    if ($stmt_msg->execute()) {
        echo "<script>alert('Demande de prolongation envoyée !'); window.location='etu_emprunt.php';</script>";
        exit();
    } else {
        echo "Erreur lors de l'envoi de la demande : " . $conn->error;
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

<section id="emprunts">
    <h2>Mes emprunts</h2>

    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Date d'emprunt</th>
                <th>Date de retour</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

        <?php if ($result_emprunts->num_rows > 0): ?>
            <?php while ($emprunt = $result_emprunts->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($emprunt['titre']); ?></td>
                    <td><?php echo htmlspecialchars($emprunt['date_empr']); ?></td>
                    <td><?php echo htmlspecialchars($emprunt['date_retour']); ?></td>
                    <td>

                        <!-- PROLONGER -->
                        <form method="POST" action="etu_emprunt.php" style="display:inline;">
                            <input type="hidden" name="isbn" value="<?php echo $emprunt['ISBN']; ?>">
                            <button type="submit" name="prolongerr" class="extend-btn">
                                ⏳ Prolonger
                            </button>
                        </form>

                        <!-- GÉNÉRER REÇU -->
                        <form method="GET" action="recu_emprunt.php" style="display:inline;">
                            <input type="hidden" name="isbn" value="<?php echo $emprunt['ISBN']; ?>">
                            <button type="submit" class="pdf-btn">
                                📄 Reçu
                            </button>
                        </form>

                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center;">
                    Aucun emprunt en cours
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</section>

<!-- afficher les livres pour lesquels l'étudiant a fait une réservation -->
        <h2>Reservations </h2>
        <table>
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Date de réservation</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>

            <?php 
$sql_reservations = "
    SELECT
        r.date_reserv,
        r.statut,   
        l.titre
    FROM reservation r
    JOIN livre l ON r.ISBN = l.ISBN
    WHERE r.ID_etu = ?
    ORDER BY r.date_reserv DESC
";
$stmt_res = $conn->prepare($sql_reservations);
$stmt_res->bind_param("s", $id_etudiant);
$stmt_res->execute();
$result_reservations = $stmt_res->get_result();
            if ($result_reservations->num_rows > 0): ?>
                <?php while ($reservation = $result_reservations->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reservation['titre']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['date_reserv']); ?></td>
                        <td><?php echo htmlspecialchars($reservation['statut']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align:center;">
                        Aucune réservation en cours
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

            <!--Afficher les livres pour lesquels l'étudiant est sur la liste d'attente-->
            <h2>Liste d'attente</h2>
<table>
    <thead>
        <tr>
            <th>Livre</th>
            <th>Position dans la file</th>
        </tr>
    </thead>
    <tbody>

    <?php
    $sql_attente = "
        SELECT 
            l.titre,
            classement.position
        FROM liste_att a
        JOIN livre l ON a.ISBN = l.ISBN
        JOIN (
            SELECT 
                ISBN,
                ID_etu, 
                ROW_NUMBER() OVER (PARTITION BY ISBN ORDER BY numero ASC) as position
            FROM liste_att
        ) AS classement ON a.ISBN = classement.ISBN AND a.ID_etu = classement.ID_etu
        WHERE a.ID_etu = ?
        ORDER BY l.titre ASC
    ";
    
    $stmt_attente = $conn->prepare($sql_attente);
    $stmt_attente->bind_param("s", $id_etudiant);
    $stmt_attente->execute();
    $result_attente = $stmt_attente->get_result();

    if ($result_attente->num_rows > 0): ?>
        <?php while ($attente = $result_attente->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($attente['titre']); ?></td>
                <td><?php echo htmlspecialchars($attente['position']); ?></td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="2" style="text-align:center;">
                Aucun livre en liste d'attente
            </td>
        </tr>
    <?php endif; ?>
    
    </tbody>
</table>

<style>
    h2 {
    color: #2c3e50;
    font-size: 1.8rem;
    margin: 30px 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 3px solid #3498db;
    display: inline-block;
}

/* Style des tableaux */
table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 40px;
}

/* En-tête du tableau */
thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

thead tr th {
    color: #fff;
    font-weight: 600;
    text-align: left;
    padding: 15px 20px;
    font-size: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Corps du tableau */
tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

tbody tr:last-child {
    border-bottom: none;
}

tbody tr:hover {
    background-color: #f8f9fa;
    transform: scale(1.01);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

tbody tr td {
    padding: 15px 20px;
    color: #333;
    font-size: 0.95rem;
}

/* Alternance de couleurs pour les lignes */
tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:nth-child(even):hover {
    background-color: #f0f0f0;
}

/* Style pour la colonne statut */
tbody tr td:last-child {
    font-weight: 600;
}

/* Badges de statut */
tbody tr td:contains("en_attente"),
tbody tr td[data-status="en_attente"] {
    color: #ff9800;
}

tbody tr td:contains("validé"),
tbody tr td[data-status="validé"] {
    color: #4caf50;
}

tbody tr td:contains("refusé"),
tbody tr td[data-status="refusé"] {
    color: #f44336;
}

/* Message "Aucune réservation" */
tbody tr td[colspan] {
    text-align: center !important;
    color: #999;
    font-style: italic;
    padding: 30px 20px;
}

/* Style pour la position dans la file */
tbody tr td:last-child {
    font-weight: bold;
    color: #667eea;
}

/* Responsive design */
@media screen and (max-width: 768px) {
    table {
        font-size: 0.85rem;
    }
    
    thead tr th,
    tbody tr td {
        padding: 10px 12px;
    }
    
    h2 {
        font-size: 1.5rem;
    }
}

/* Animation au chargement */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

table {
    animation: fadeIn 0.5s ease-out;
}
</style>



</main>

<style>
    /* ===== GÉNÉRAL ===== */

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


  // Prolonger emprunt
  document.querySelectorAll('.extend-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>toast('Demande de prolongation envoyée'));
  });

  // Générer PDF (simulation)
  document.getElementById('generatePDF').addEventListener('click', ()=>toast('PDF généré !'));
</script>

</body>
</html>
