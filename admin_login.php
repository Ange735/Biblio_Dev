<?php 
    include 'config1.php';

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        $id = htmlspecialchars($_POST['id_admin']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);

        $sql = "SELECT * FROM administrateur WHERE ID_admin='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = mysqli_fetch_array($result);
            if ($row["mdp"] == "$pass") {
                echo "<script>alert('Login successful!!'); window.location='admin_livre.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error: Incorrect password!');</script>";
                
            }
        } else {
            echo "<script>alert('Error: No account found for this administrator ID!');</script>";
            
        }
    }
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administrator Login - Library</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="container">
    <h1>Administrator Space</h1>
    <div class="form-box">
      <form id="adminLogin" class="form active" method="POST" action="admin_login.php">
        <h2>Admin Login</h2>
        <input type="text" name="id_admin" placeholder="Administrator ID" required>
        <input type="password" name="mot_de_passe" placeholder="Password" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Password must contain at least 8 characters, including at least one letter and one number" required>
        <button type="submit">Login</button>
        <p><a href="etudiant.php">← Student Space</a></p>
      </form>
    </div>
  </div>

  <!-- <script>
    document.getElementById("adminLogin").addEventListener("submit", function(e) {
      e.preventDefault();
      alert("Admin login awaiting server verification (PHP)");
    });
  </script> -->
</body>
</html>