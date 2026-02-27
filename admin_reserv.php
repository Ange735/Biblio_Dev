<?php 
include 'config2.php';
$activePage = 'reservation';
//actions
if (isset($_POST['vali'])) {
    $id_etu = $_POST['id_etu'];
    $isbn = intval($_POST['isbn']);

    // Check if the student has already borrowed this book
    $sql0 = "SELECT COUNT(*) AS count_reserv FROM emprunt 
             WHERE ISBN=$isbn AND ID_etu='$id_etu' AND statue_empr='en_cours'";
    $result0 = $conn->query($sql0);
    $row0 = $result0->fetch_assoc();

    // If the student has already borrowed this book, block
    if ($row0['count_reserv'] > 0) {
        echo "<script>alert('Error: The student has already borrowed this book!');
              window.location='admin_reserv.php';</script>";
        exit();
    }

    // Otherwise, proceed with validation
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
                         VALUES ('$id_etu', 'Your reservation for the book (ISBN: $isbn) has been validated. Please pick up the book within 2 days.')";
                
                if($conn->query($sql3) === TRUE){
                    echo "<script>alert('Reservation validated successfully!');
                          window.location='admin_reserv.php';</script>";
                    exit();
                } else {
                    echo "Error INSERT message_etu: " . $conn->error;
                }
            } else {
                echo "Error INSERT emprunt: " . $conn->error;
            }
        } else {
            echo "Error UPDATE livre: " . $conn->error;
        }
    } else {
        echo "Error UPDATE reservation: " . $conn->error;
    }
}

if (isset($_POST['refus'])) {
    $id_etu = $_POST['id_etu'];
    $isbn = intval($_POST['isbn']);

    $sql="UPDATE reservation SET statut='refusé' WHERE ISBN=$isbn AND ID_etu='$id_etu' AND statut='en_attente'";
    if($conn->query($sql) === TRUE){
        echo "<script>alert('Reservation rejected successfully!');window.location='admin_reserv.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - University Library</title>
  <link rel="stylesheet" href="admin_style.css" />
  <link rel="stylesheet" href="admin_new_style.css">
</head>
<body>
  <header class="admin-header">
    <h1>🔧 Administrator Space — Library</h1>
    <div class="header-actions">
      <button id="btnInventory">Statistics</button>
    </div>
  </header>

  <main class="admin-main">
    <aside class="sidebar">
      <ul>
        <li><a href="admin_livre.php" class="menu-link <?php echo ($activePage=='livres') ? 'active' : ''; ?>">
     Books (CRUD)</a></li>
        <li><a href="admin_etudiant.php" class="menu-link <?php echo ($activePage=='etudiants') ? 'active' : ''; ?>">
     Students (CRUD)</a></li>
        <li><a href="admin_reserv.php" class="menu-link <?php echo ($activePage=='reservation') ? 'active' : ''; ?>">
     Reservations</a></li>
        <li><a href="admin_pret.php" class="menu-link <?php echo ($activePage=='pret') ? 'active' : ''; ?>">
     Loans</a></li>
        <li><a href="admin_liste.php" class="menu-link <?php echo ($activePage=='liste') ? 'active' : ''; ?>">
     Waiting List</a></li>
        <li><a href="admin_messages.php" class="menu-link <?php echo ($activePage=='messages') ? 'active' : ''; ?>">
     Alerts & Messages</a></li>
      </ul>
    </aside>

    <!-- Reservations Section -->
    <section id="panelReservations">
      <h2>Pending Reservations</h2>
      <table class="table" id="reservationsTable">
        <thead>
          <tr>
            <th>Student</th>
            <th>Book</th>
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
                            <button type='submit' name='vali' title='Validate'>✅</button>
                            <button type='submit' name='refus' title='Reject'>❌</button>
                        </form>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No reservation requests.</td></tr>";
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
    // --- Minimal JS for interactions ---
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