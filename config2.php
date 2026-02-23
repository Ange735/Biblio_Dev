<?php 
    $servername = "localhost";
    $username = "root";
    $password = "";     
    $dbname = "gestion_bibliothèque";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Echec e connection". $conn->connect_error);
    }
    // else {
    //     echo "✅ Connexion réussie à la base de données '$dbname'";
    // };
?>