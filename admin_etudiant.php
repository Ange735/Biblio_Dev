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

        // Same insertion logic as above
        $sql1 = "SELECT * FROM étudiant WHERE Email='$email' OR ID_etu='$id'";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            echo "<script>alert('ERROR! : Email already used or Student ID already exists!');</script>";
        } else {
            if ($pass === $confpass) {
                $sql = "INSERT INTO étudiant (ID_etu, nom, Prénom, Email, nbr_retard, statue_etu, mdp)
                        VALUES ('$id', '$nom', '$prenom', '$email', 0, 1, '$pass')";
                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Registration successful!');window.location='admin_etudiant.php';</script>";
                    exit();
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('ERROR! : Confirmation password different from password!');</script>";
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
        // Update existing user
        $sql = "SELECT * FROM étudiant WHERE ID_etu='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0){
          
        $sql = "UPDATE étudiant 
                SET nom='$nom', Prénom='$prenom', Email='$email', mdp='$pass' 
                WHERE ID_etu='$id'";

        if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Modification successfully completed!');window.location='admin_etudiant.php';</script>";
        exit();
    } else {
        echo "Error: " . $conn->error;
    }}
      else{
        echo "<script>alert('ERROR! : Cannot modify, Student ID not found!');</script>";}
    }


    if (isset($_POST['supp'])) {
        $id = htmlspecialchars($_POST['id_supp']);
      $sql2 = "DELETE FROM étudiant WHERE ID_etu='$id'";
      if ($conn->query($sql2) === TRUE) {
        echo "<script>alert('User successfully deleted!');window.location='admin_etudiant.php';</script>";
        exit();
      } else {
        echo "Error: " . $conn->error;
    }
}

    if (isset($_POST['penaliser'])) {

    $id = htmlspecialchars($_POST['id_pen']);

    // Penalize
    $sql3 = "UPDATE étudiant SET nbr_retard = nbr_retard + 1 WHERE ID_etu = '$id'";

    if ($conn->query($sql3) === TRUE) {
        // Check number of delays
        $sql1 = "SELECT nbr_retard FROM étudiant WHERE ID_etu = '$id'";
        $result = $conn->query($sql1);
        $row = $result->fetch_assoc();

        // If blocking needed
        if ($row['nbr_retard'] >= 3) {
            $sql = "UPDATE étudiant SET statue_etu = 0 WHERE ID_etu = '$id'";
            $conn->query($sql);
            echo "<script>alert('User blocked!');window.location='admin_etudiant.php';</script>";
            exit();
        } else {
            echo "<script>alert('User penalized!');window.location='admin_etudiant.php';</script>";
            exit();
        }

    } else {
        echo "Error: " . $conn->error;
    }
}

    if (isset($_POST['annul'])) {

    $id = htmlspecialchars($_POST['id_ann']);
      $sql="SELECT nbr_retard FROM étudiant WHERE ID_etu = '$id'";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      if($row['nbr_retard'] <= 0) {
        echo "<script>alert('Penalty cannot be negative!');window.location='admin_etudiant.php';</script>";
        exit();
      }
      else{
        if($row['nbr_retard'] >=3){
          $sql3 = "UPDATE étudiant SET nbr_retard = 0 WHERE ID_etu = '$id'";
          $conn->query($sql3);
          $sql = "UPDATE étudiant SET statue_etu = 1 WHERE ID_etu = '$id'";
          $conn->query($sql);
            echo "<script>alert('User unblocked!');window.location='admin_etudiant.php';</script>";
            exit();
        }
        else{
    // Cancel penalty
    $sql3 = "UPDATE étudiant SET nbr_retard = nbr_retard - 1 WHERE ID_etu = '$id'";
    $conn->query($sql3);
            echo "<script>alert('Penalty reduced by 1 for this user!');window.location='admin_etudiant.php';</script>";
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
        echo "<script>alert('Alert sent to all students!');window.location='admin_etudiant.php';</script>";
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
      <button id="btnAddStudent">+ Add/Modify a student</button>
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

<section  id="panelStudents" >
      <h2>Student Management</h2>
      <form method='POST' action='admin_etudiant.php' style='display:inline;'>
                    <input type="text" name="rech" placeholder="Search by email or Student ID" value="<?php echo (isset($_POST['rechercher']) && isset($_POST['rech'])) ? htmlspecialchars($_POST['rech']) : ''; ?>">
                    <button type="submit" name="rechercher">Search</button>
                    <button type="submit" name="annuler">Cancel</button>
        </form>
      
      <table class="table" id="studentsTable" border="1">
  <tr>
    <th>ID</th>
    <th>Last Name</th>
    <th>First Name</th>
    <th>Email</th>
    <th>Penalties</th>
    <th>Status</th>
    <th>Password</th>
    <th>Actions</th>
  </tr>
  <?php
  // Search logic or full display
  if (isset($_POST['rechercher']) && !empty($_POST['rech'])) {
      // Search mode
      $email = $conn->real_escape_string($_POST['rech']);
      $id = $conn->real_escape_string($_POST['rech']);
      $sql = "SELECT * FROM étudiant WHERE Email='$email' OR ID_etu='$id'";
  } else {
      // Default mode: show all students
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
                    onsubmit=\"return confirm('Do you really want to penalize this user?');\">
                    <input type='hidden' name='id_pen' value='" . htmlspecialchars($row['ID_etu']) . "'>
                    <button type='submit' name='penaliser' class='btn-penalize' title='Penalize'>⚠️</button>
                  </form>
                ";
                echo "
                  <form method='POST' action='admin_etudiant.php' style='display:inline;' 
                    onsubmit=\"return confirm('Do you really want to cancel the penalty?');\">
                    <input type='hidden' name='id_ann' value='" . htmlspecialchars($row['ID_etu']) . "'>
                    <button type='submit' name='annul' class='btn-reset' title='Reduce penalty'>🔄</button>
                  </form>
                ";
                echo "
                  <form method='POST' action='admin_etudiant.php' style='display:inline;' 
                    onsubmit=\"return confirm('Do you really want to delete this user?');\">
                    <input type='hidden' name='id_supp' value='" . htmlspecialchars($row['ID_etu']) . "'>
                    <button type='submit' name='supp' class='btn-reset' title='Delete'>🗑️</button>
                  </form>
                ";
                echo " 
                <form method='POST' action='admin_etudiant.php' style='display:inline;' 
    onsubmit=\"return confirm('Do you really want to send a message to this user?');\">

    <input type='hidden' name='id_mess' value='".htmlspecialchars($row['ID_etu']) . "'>

    <button type='submit' name='ouvrir_modal_mess' class='btn-reset' title='Message'>✉️</button>
</form>

                   ";
                echo "</td>";
                echo "</tr>";
      }
  } else {
      echo "<tr><td colspan='8'>No users found</td></tr>";
  }
  ?>
</table>
    </section>








    </main>
<!--Modals-->
<div class="modal" id="modalStudent">
    <div class="modal-content">
      <h3 id="modalStudentTitle">Add a student</h3>
      <form id="formStudent" method="POST" action="admin_etudiant.php">
        <input type="text" name="nom" placeholder="Last Name" required>
                <input type="text" name="prenom" placeholder="First Name" required>
                <input type="text" name="id_etudiant" placeholder="Student ID" required>
                <input type="email" name="email" placeholder="University email" required>
                <input type="password" name="mot_de_passe" placeholder="Password" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Password must contain at least 8 characters, including at least one letter and one number" required>
                <input type="password" name="cmdp" placeholder="Confirm password" minlength="8" required>
        <div class="modal-actions">
          <button type="submit" name="inscr">Save</button>
          <button type="submit" name="modif">Modify</button>
          <button type="button" class="close-modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>






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
<div class="modal" id="modalMessage" style="display:block;">
  <div class="modal-content">
    <h3>Send a message to <?= htmlspecialchars($et['nom'] . " " . $et['Prénom']) ?></h3>

    <form method="POST" action="admin_etudiant.php">
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


    document.getElementById('btnAddStudent').addEventListener('click',()=>{ 
      document.getElementById('modalStudentTitle').textContent='Add a student';
      document.getElementById('formStudent').reset();
      document.getElementById('modalStudent').style.display='flex';
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