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

    // send a message to admin to extend
    $sql_msg = "INSERT INTO message_admin (id_etudiant, messag) VALUES (?, ?)";
    $contenu = "Extension request for book ISBN: $isbn";
    $stmt_msg = $conn->prepare($sql_msg);
    $stmt_msg->bind_param("ss", $id_etudiant, $contenu);
    if ($stmt_msg->execute()) {
        echo "<script>alert('Extension request sent!'); window.location='etu_emprunt.php';</script>";
        exit();
    } else {
        echo "Error sending request: " . $conn->error;
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

<section id="emprunts">
    <h2>My Loans</h2>

    <table>
        <thead>
            <tr>
                <th>Book</th>
                <th>Loan Date</th>
                <th>Return Date</th>
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

                        <!-- EXTEND -->
                        <form method="POST" action="etu_emprunt.php" style="display:inline;">
                            <input type="hidden" name="isbn" value="<?php echo $emprunt['ISBN']; ?>">
                            <button type="submit" name="prolongerr" class="extend-btn">
                                ⏳ Extend
                            </button>
                        </form>

                        <!-- GENERATE RECEIPT -->
                        <form method="GET" action="recu_emprunt.php" style="display:inline;">
                            <input type="hidden" name="isbn" value="<?php echo $emprunt['ISBN']; ?>">
                            <button type="submit" class="pdf-btn">
                                📄 Receipt
                            </button>
                        </form>

                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align:center;">
                    No active loans
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</section>

<!-- display books for which the student has made a reservation -->
        <h2>Reservations</h2>
        <table>
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Reservation Date</th>
                    <th>Status</th>
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
                        No active reservations
                    </td>
                </tr>
            <?php endif; ?>

            </tbody>
        </table>

            <!-- Display books for which the student is on the waiting list -->
            <h2>Waiting List</h2>
<table>
    <thead>
        <tr>
            <th>Book</th>
            <th>Position in queue</th>
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
                No books on waiting list
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

/* Table styles */
table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 40px;
}

/* Table header */
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

/* Table body */
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

/* Alternating row colors */
tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:nth-child(even):hover {
    background-color: #f0f0f0;
}

/* Style for status column */
tbody tr td:last-child {
    font-weight: 600;
}

/* Status badges */
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

/* "No reservations" message */
tbody tr td[colspan] {
    text-align: center !important;
    color: #999;
    font-style: italic;
    padding: 30px 20px;
}

/* Style for position in queue */
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

/* Loading animation */
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


  // Extend loan
  document.querySelectorAll('.extend-btn').forEach(btn=>{
    btn.addEventListener('click', ()=>toast('Extension request sent'));
  });

  // Generate PDF (simulation)
  document.getElementById('generatePDF').addEventListener('click', ()=>toast('PDF generated!'));
</script>

</body>
</html>