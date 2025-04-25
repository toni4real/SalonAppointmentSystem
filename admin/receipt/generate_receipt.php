<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Fetch the payment details based on payment ID (or appointment ID)
if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Fetch payment details
    $query = "SELECT p.*, c.first_name, c.last_name, a.appointment_date, a.appointment_time, a.service
              FROM payments p
              JOIN appointments a ON p.appointment_id = a.appointment_id
              JOIN customers c ON a.customer_id = c.customer_id
              WHERE p.payment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();

    if ($payment) {
        // Create a new PDF document
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('Arial', 'B', 16);

        // Title
        $pdf->Cell(200, 10, 'Salon Appointment System Receipt', 0, 1, 'C');
        $pdf->Ln(10);

        // Customer and appointment details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 10, 'Customer: ', 0, 0);
        $pdf->Cell(150, 10, $payment['first_name'] . ' ' . $payment['last_name'], 0, 1);

        $pdf->Cell(40, 10, 'Appointment: ', 0, 0);
        $pdf->Cell(150, 10, $payment['appointment_date'] . ' ' . $payment['appointment_time'], 0, 1);

        $pdf->Cell(40, 10, 'Service: ', 0, 0);
        $pdf->Cell(150, 10, $payment['service'], 0, 1);

        // Payment details
        $pdf->Cell(40, 10, 'Amount Paid: ', 0, 0);
        $pdf->Cell(150, 10, '₱' . number_format($payment['amount'], 2), 0, 1);

        $pdf->Cell(40, 10, 'Payment Date: ', 0, 0);
        $pdf->Cell(150, 10, $payment['payment_date'], 0, 1);

        $pdf->Cell(40, 10, 'Payment Status: ', 0, 0);
        $pdf->Cell(150, 10, $payment['status'], 0, 1);

        // Footer
        $pdf->Ln(10);
        $pdf->Cell(200, 10, 'Thank you for choosing our salon!', 0, 1, 'C');

        // Output the PDF (this will prompt the download)
        $pdf->Output('I', 'Receipt_' . $payment['payment_id'] . '.pdf');
    } else {
        echo "No payment found.";
    }
}
?>
