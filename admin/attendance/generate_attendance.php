<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';
require('../../fpdf/fpdf.php');

date_default_timezone_set('Asia/Manila');
$attendance_date = $_GET['date'] ?? date('Y-m-d');

$query = "
    SELECT s.first_name, s.last_name, sa.status
    FROM staff_attendance sa
    JOIN staff s ON sa.staff_id = s.staff_id
    WHERE sa.attendance_date = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 's', $attendance_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    die('No attendance data found for this date.');
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Salon Staff Attendance Sheet',0,1,'C');
$pdf->SetFont('Arial','',12);
$display_date = date('F j, Y', strtotime($attendance_date));
$pdf->Cell(0,10,'Date: ' . $display_date,0,1,'C');
$pdf->Ln(5);

// Table Header
$pdf->SetFont('Arial','B',12);
$pdf->Cell(10,10,'#',1);
$pdf->Cell(75,10,'Staff Name',1);
$pdf->Cell(50,10,'Status',1);
$pdf->Ln();

// Table Content
$pdf->SetFont('Arial','',12);
$counter = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $full_name = $row['first_name'] . ' ' . $row['last_name'];
    $pdf->Cell(10,10,$counter++,1);   // No. column
    $pdf->Cell(75,10,$full_name,1);   // Staff Name column
    $pdf->Cell(50,10,$row['status'],1); // Status column
    $pdf->Ln();
}

$pdf->Output('D', 'Attendance_Sheet_' . $attendance_date . '.pdf');
exit;
