<?php
session_start();
include 'config2.php';
require('fpdf/fpdf.php');

if (!isset($_SESSION['id_etudiant']) || !isset($_GET['isbn'])) {
    die("Access denied");
}

$id_etudiant = $_SESSION['id_etudiant'];
$isbn = intval($_GET['isbn']);

// Retrieving info
$sql = "
    SELECT 
        et.nom,
        et.Prénom,
        et.ID_etu,
        l.titre,
        e.ISBN,
        e.date_reserv,
        e.date_retour
    FROM emprunt e
    JOIN livre l ON e.ISBN = l.ISBN
    JOIN étudiant et ON et.ID_etu = e.ID_etu
    WHERE e.ID_etu = ? AND e.ISBN = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $id_etudiant, $isbn);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Loan not found");
}

$data = $result->fetch_assoc();

/* ===== PDF ===== */

$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Borrowing Receipt - University Library',0,1,'C');
$pdf->Ln(8);

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,'Last Name: '.$data['nom'],0,1);
$pdf->Cell(0,8,'First Name: '.$data['Prénom'],0,1);
$pdf->Cell(0,8,'Student ID: '.$data['ID_etu'],0,1);
$pdf->Ln(5);

$pdf->Cell(0,8,'Book Title: '.$data['titre'],0,1);
$pdf->Cell(0,8,'ISBN: '.$data['ISBN'],0,1);
$pdf->Cell(0,8,'Reservation Date: '.$data['date_reserv'],0,1);
$pdf->Cell(0,8,'Expected Return Date: '.$data['date_retour'],0,1);

$pdf->Ln(10);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,10,'Official Document - University Library',0,1,'C');

$pdf->Output('D', 'recu_emprunt_'.$data['ISBN'].'.pdf');
exit();