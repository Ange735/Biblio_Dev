<?php
include 'config2.php';

// Vérifier que l'admin est connecté
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrateur</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <link rel="stylesheet" href="style2.css">
    <link rel="stylesheet" href="admin_new_style.css">
</head>
<body>
<header>
    <h1>📊 Dashboard Administrateur</h1>
    <nav>
        <a href="admin_livre.php">Retour à l'administration</a>
    </nav>
</header>
<main>

<section>
    <h2>Statistiques générales</h2>
    <div>
        <?php
        // Livre le mieux noté
        $top_book = $conn->query("SELECT titre, note FROM livre ORDER BY note DESC LIMIT 1")->fetch_assoc();
        echo "<p>📚 Livre le mieux noté : " . htmlspecialchars($top_book['titre']) . " (" . round($top_book['note'],1) . "/5)</p>";

        // Livre le plus emprunté
        $most_borrowed = $conn->query("SELECT l.titre, COUNT(e.ISBN) AS total_emprunts 
                                       FROM emprunt e 
                                       JOIN livre l ON e.ISBN = l.ISBN 
                                       GROUP BY e.ISBN 
                                       ORDER BY total_emprunts DESC 
                                       LIMIT 1")->fetch_assoc();
        echo "<p>📖 Livre le plus emprunté : " . htmlspecialchars($most_borrowed['titre']) . " (" . $most_borrowed['total_emprunts'] . " emprunts)</p>";

        // Étudiant avec le plus de pénalités
        $top_penalty = $conn->query("SELECT nom, Prénom, nbr_retard FROM étudiant ORDER BY nbr_retard DESC LIMIT 1")->fetch_assoc();
        echo "<p>👤 Étudiant avec le plus de pénalités : " . htmlspecialchars($top_penalty['nom'] . " " . $top_penalty['Prénom']) . " (" . $top_penalty['nbr_retard'] . ")</p>";

        // Étudiant qui emprunte le plus
        $top_borrower = $conn->query("SELECT e.nom, e.Prénom, COUNT(em.ISBN) AS total_emprunts 
                                      FROM emprunt em 
                                      JOIN étudiant e ON em.ID_etu = e.ID_etu 
                                      GROUP BY em.ID_etu 
                                      ORDER BY total_emprunts DESC 
                                      LIMIT 1")->fetch_assoc();
        echo "<p>👤 Étudiant qui emprunte le plus : " . htmlspecialchars($top_borrower['nom'] . " " . $top_borrower['Prénom']) . " (" . $top_borrower['total_emprunts'] . " livres)</p>";

        // Nombre total de livres
        $total_books = $conn->query("SELECT COUNT(*) as total FROM livre")->fetch_assoc();
        echo "<p>📘 Nombre total de livres : " . $total_books['total'] . "</p>";

        // Nombre total d'étudiants
        $total_students = $conn->query("SELECT COUNT(*) as total FROM étudiant")->fetch_assoc();
        echo "<p>👨‍🎓 Nombre total d'étudiants : " . $total_students['total'] . "</p>";

        // Réservations en attente
        $pending_reservations = $conn->query("SELECT COUNT(*) as total FROM reservation WHERE statut='en_attente'")->fetch_assoc();
        echo "<p>⏳ Réservations en attente : " . $pending_reservations['total'] . "</p>";
        ?>
    </div>
</section>

<section>
    <h2>Graphiques</h2>
    <canvas id="bookStatsChart" width="400" height="200"></canvas>
    <canvas id="studentStatsChart" width="400" height="200"></canvas>
    <canvas id="topRatedBooksChart" width="400" height="200"></canvas>
</section>

<script>
// Top 5 livres les plus empruntés
<?php
$books_chart = $conn->query("SELECT l.titre, COUNT(e.ISBN) AS total_emprunts 
                             FROM emprunt e 
                             JOIN livre l ON e.ISBN = l.ISBN 
                             GROUP BY e.ISBN 
                             ORDER BY total_emprunts DESC 
                             LIMIT 5");

$books_labels = $books_data = [];
while($row = $books_chart->fetch_assoc()){
    $books_labels[] = $row['titre'];
    $books_data[] = $row['total_emprunts'];
}
?>

new Chart(document.getElementById('bookStatsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($books_labels); ?>,
        datasets: [{
            label: 'Nombre d\'emprunts',
            data: <?php echo json_encode($books_data); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Top 5 étudiants avec le plus d'emprunts
<?php
$students_chart = $conn->query("SELECT e.nom, e.Prénom, COUNT(em.ISBN) AS total_emprunts 
                                FROM emprunt em 
                                JOIN étudiant e ON em.ID_etu = e.ID_etu 
                                GROUP BY em.ID_etu 
                                ORDER BY total_emprunts DESC 
                                LIMIT 5");

$students_labels = $students_data = [];
while($row = $students_chart->fetch_assoc()){
    $students_labels[] = $row['nom'] . ' ' . $row['Prénom'];
    $students_data[] = $row['total_emprunts'];
}
?>

new Chart(document.getElementById('studentStatsChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($students_labels); ?>,
        datasets: [{
            label: 'Nombre d\'emprunts',
            data: <?php echo json_encode($students_data); ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});

// Top 5 livres les mieux notés
<?php
$top_rated_books = $conn->query("SELECT titre, note FROM livre ORDER BY note DESC LIMIT 5");
$top_rated_labels = $top_rated_data = [];
while($row = $top_rated_books->fetch_assoc()){
    $top_rated_labels[] = $row['titre'];
    $top_rated_data[] = $row['note'];
}
?>

new Chart(document.getElementById('topRatedBooksChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($top_rated_labels); ?>,
        datasets: [{
            label: 'Note moyenne',
            data: <?php echo json_encode($top_rated_data); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true, max: 5 } } }
});
</script>

<style>

</style>

</body>
</html>
