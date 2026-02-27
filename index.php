<?php
session_start();
include 'config2.php';
if (isset($_POST['inscr'])) {
    // Retrieving registration form data


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nom = htmlspecialchars($_POST['nom']);
        $prenom = htmlspecialchars($_POST['prenom']);
        $email = htmlspecialchars($_POST['email']);
        $id = htmlspecialchars($_POST['id_etudiant']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);
        $confpass = htmlspecialchars($_POST['cmdp']);

        $sql1 = "SELECT * FROM étudiant WHERE Email='$email' OR ID_etu='$id'";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            echo "<script>alert('ERROR! : Email already used or Student ID already exists!');</script>";
        } else {
            if ($pass == $confpass) {
                $sql = "INSERT INTO étudiant (ID_etu, nom, Prénom, Email, nbr_retard, statue_etu, mdp)
                VALUES ('$id', '$nom', '$prenom', '$email', 0, 1, '$pass')";

                if ($conn->query($sql) === TRUE) {
                    echo "<script>alert('Registration successful!'); window.location='etu_catalogue.php';</script>";
                    exit();
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            } else {
                echo "<script>alert('ERROR! : Confirmation password does not match the password!');</script>";

            }
        }
    }
}

if (isset($_POST['connect'])) {
    // Retrieving login form data
    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        $id = htmlspecialchars($_POST['id_etudiant']);
        $pass = htmlspecialchars($_POST['mot_de_passe']);

        $sql = "SELECT * FROM étudiant WHERE ID_etu='$id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $sql1 = "SELECT statue_etu FROM étudiant WHERE ID_etu = '$id'";
            $results = $conn->query($sql1);
            $rows = $results->fetch_assoc();
            if ($rows['statue_etu'] == 0) {
                echo "<script>alert('Your account has been blocked due to too many late returns. Please contact the administration to unblock it.');window.location='index.php';</script>";
                exit();
    }
            else{
            $row = mysqli_fetch_array($result);
            if ($row["mdp"] == "$pass") {
                session_regenerate_id(true);
                $_SESSION['id_etudiant'] = $id;
                $_SESSION['etudiant_nom'] = $row['nom'];
                $_SESSION['logged_in'] = true;
                echo "<script>alert('Login successful!!'); window.location='etu_catalogue.php';</script>";
                exit();
            } else {
                echo "<script>alert('Error: Incorrect password!');</script> ";
            }}}
            else {
                echo "<script>alert('Error: No account found for this student ID! (Try another ID or register!)');</script> ";
        }
        
}
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Library - Student</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h1>University Library</h1>

        <div class="form-box">
            <!-- Login form -->
            <form id="loginForm" class="form active" method="POST" action="index.php">
                <h2>Student Login</h2>
                <input type="text" name="id_etudiant" placeholder="Student ID" required>
                <input type="password" name="mot_de_passe" placeholder="Password" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Password must contain at least 8 characters, including at least one letter and one number" required>
                <button type="submit" name="connect">Log in</button>
                <p>Not registered yet? <a href="#" id="showRegister">Create an account</a></p>
            </form>

            <!-- Registration form -->
            <form id="registerForm" class="form" method="POST" action="index.php">
                <h2>Student Registration</h2>
                <input type="text" name="nom" placeholder="Last Name" required>
                <input type="text" name="prenom" placeholder="First Name" required>
                <input type="text" name="id_etudiant" placeholder="Student ID" required>
                <input type="email" name="email" placeholder="University Email" required>
                <input type="password" name="mot_de_passe" placeholder="Password" minlength="8" pattern="^(?=.*[A-Za-z])(?=.*\d).{8,}$" title="Password must contain at least 8 characters, including at least one letter and one number" required>
                <input type="password" name="cmdp" placeholder="Confirm password" minlength="8" required>
                <button type="submit" name="inscr">Register</button>
                <p>Already registered? <a href="#" id="showLogin">Log in</a></p>
            </form>
        </div>
    </div>

    <style>
        /* Hide forms by default */
.form {
  display: none;
  animation: fadeIn 0.5s ease-in-out;
}

/* Show only the active form */
.form.active {
  display: block;
}

/* Fade-in animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

    </style>

    <script>
document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");
    const showRegister = document.getElementById("showRegister");
    const showLogin = document.getElementById("showLogin");

    // Security: always hide registration form at start
    registerForm.classList.remove("active");
    loginForm.classList.add("active");

    showRegister.addEventListener("click", (e) => {
        e.preventDefault();
        loginForm.classList.remove("active");
        registerForm.classList.add("active");
    });

    showLogin.addEventListener("click", (e) => {
        e.preventDefault();
        registerForm.classList.remove("active");
        loginForm.classList.add("active");
    });
});
</script>


</body>

</html>