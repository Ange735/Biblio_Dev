<?php 
include 'config2.php';
$activePage = 'messages';
//actions



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

// Send message
if (isset($_POST['envoyer_message'])) {
    $id = $_POST['msg_id'];
    $msg = $_POST['msg_text'];

    $sql = "INSERT INTO message_etu (mess, id_etu) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $msg, $id);

    if ($stmt->execute()) {
        echo "<script>alert('Message sent!');</script>";
    } else {
        echo "<script>alert('Error sending message');</script>";
    }
}



if(isset($_POST['me'])){
    $alert_test = htmlspecialchars($_POST['alert_text']);
    $sql = "INSERT INTO message_etu (mess, id_etu) VALUES ('$alert_test',NULL)";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Alert sent to all students!');window.location='admin_messages.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}



?>




<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin - University Library</title>
  <link rel="stylesheet" href="admin_style.css" />
  <link rel="stylesheet" href="admin_new_style.css">
</head>
<body>
  <header class="admin-header">
    <h1>🔧 Admin Space — Library</h1>
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



<!-- Section -->
<section  id="panelAlerts">
    <h2>Alerts & Messages</h2>
    <!-- Level 1: Send a message -->
    <div class="tab-content active" id="tabSend">
        <form action="admin_messages.php" method="POST" id="alertForm">
            <input type="hidden" name="action" value="send_alert">
            <div class="alerts-actions" style="margin-bottom:20px;">
                <input id="alertText" type="text" name="alert_text" placeholder="Message to all students" style="width:70%;" required />
                <button id="sendAlertBtn" name="me" type="submit">Send message</button>
                <button type="reset" style="background-color:#dc3545; margin-left:10px;">Cancel</button>
            </div>
        </form>
    </div>
  <?php 
    // Last 5 messages sent
    //select student (lastname, firstname, ID_etu) and message content
    $sql = "SELECT m.mess, e.nom, e.Prénom, e.ID_etu, m.id_etu as destinataire 
        FROM message_etu m 
        LEFT JOIN étudiant e ON m.id_etu = e.ID_etu 
        ORDER BY m.id DESC 
        LIMIT 5";
        
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h3>Last 5 messages sent:</h3>";
    echo "<ul style='background-color:#e3f2fd; padding:15px; border-radius:8px; border-left:4px solid #2196F3;'>";
    
    while ($row = $result->fetch_assoc()) {
        // If id_etu is NULL, it's a message for everyone
        if ($row['destinataire'] === null) {
            echo "<li style='margin-bottom:10px; color:#1565C0;'><strong>To everyone:</strong> <span style='color:#333;'>" . htmlspecialchars($row['mess']) . "</span></li>";
        } else {
            echo "<li style='margin-bottom:10px; color:#1565C0;'><strong>" . htmlspecialchars($row['Prénom'] . " " . $row['nom'] . " (" . $row['ID_etu'] . ")") . ":</strong> <span style='color:#333;'>" . htmlspecialchars($row['mess']) . "</span></li>";
        }
    }
    
    echo "</ul>";
} else {
    echo "<h3>Last 5 messages sent:</h3>";
    echo "<p>No messages sent recently.</p>";
}

     //Last 10 messages received
    $sql="SELECT m.messag, e.nom, e.Prénom, e.ID_etu FROM message_admin m JOIN étudiant e ON m.id_etudiant = e.ID_etu ORDER BY m.id_etudiant DESC LIMIT 5";
    $result = $conn->query($sql);
      if ($result->num_rows > 0) {
          echo "<h3>Last 10 messages received:</h3>";
          echo "<ul style='background-color:#f3e5f5; padding:15px; border-radius:8px; border-left:4px solid #9C27B0;'>";
          while ($row = $result->fetch_assoc()) {
              echo "<li style='margin-bottom:10px; color:#6A1B9A;'><strong>" . htmlspecialchars($row['Prénom'] . " " . $row['nom'] ." (". $row['ID_etu'] .")") . ":</strong> <span style='color:#333;'>" . htmlspecialchars($row['messag']) . "</span></li>";
          }
          echo "</ul>";
      } else{
        echo "<h3>Last 10 messages received:</h3>";
          echo "<p>No messages received recently.</p>";
      }
    ?>
</section>








    </main>
<!--Modals-->







<?php


if ($modal_open) {
    // get student name
    $sql_et = "SELECT nom, Prénom FROM étudiant WHERE ID_etu = ?";
    $stmt_et = $conn->prepare($sql_et);
    $stmt_et->bind_param("s", $selected_student);
    $stmt_et->execute();
    $res_et = $stmt_et->get_result();
    $et = $res_et->fetch_assoc();
?>
<div id="modalMessage" >
  <div class="modal-content">
    <h3>Send a message to <?= htmlspecialchars($et['nom'] . " " . $et['Prénom']) ?></h3>

    <form method="POST" action="admin_messages.php">
      <input type="hidden" name="msg_id" value="<?= htmlspecialchars($selected_student) ?>">
      <textarea name="msg_text" placeholder="Message..." required></textarea>

      <div class="modal-actions">
        <button type="submit" name="envoyer_message">Send</button>
        <button type="button" onclick="document.getElementById('modalMessage').style.display='none';">
          Close
        </button>
      </div>
    </form>
  </div>
</div>
<?php
}
?>

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
      if(!text){ alert('Empty text'); return;}
      const studentSelect = document.getElementById('alertStudentSelect');
      const id = studentSelect.value;
      const name = studentSelect.options[studentSelect.selectedIndex].text;
      const ul = document.getElementById('alertsList');
      ul.insertAdjacentHTML('afterbegin', `<li>${id? 'Alert to '+name : 'Global alert'}: ${text}</li>`);
      toast('Alert sent');
      document.getElementById('alertText').value='';
    });
  </script>
</body>
</html>