<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';
require('../../fpdf/fpdf.php');

date_default_timezone_set('Asia/Manila');

// Fetch the payment details based on payment ID
if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Fetch payment details (now includes service price)
    $query = "SELECT p.*, c.first_name, c.last_name, a.appointment_date, a.appointment_time, a.payment_status, s.service_name, s.price
              FROM payments p
              JOIN appointments a ON p.appointment_id = a.appointment_id
              JOIN customers c ON a.customer_id = c.customer_id
              JOIN services s ON a.service_id = s.service_id
              WHERE p.payment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $payment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();

    if ($payment) {
        // Format dates and times
        $appointmentDateTime = date('F d, Y \a\t h:i A', strtotime($payment['appointment_date'] . ' ' . $payment['appointment_time']));
        $paymentDateTime = date('F d, Y \a\t h:i A', strtotime($payment['payment_date']));

        // Create PDF
        $pdf = new FPDF();
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('Arial', 'B', 16);

        // Title
        $pdf->Cell(0, 10, 'Salon Appointment System Receipt', 0, 1, 'C');
        $pdf->Ln(10);

        // Customer details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Customer:', 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, $payment['first_name'] . ' ' . $payment['last_name'], 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Appointment:', 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, $appointmentDateTime, 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Service:', 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, $payment['service_name'] . ' --- PHP ' . number_format($payment['price'], 2), 0, 1);

        // Payment details
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Amount Paid:', 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, 'PHP ' . number_format($payment['amount'], 2), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Payment Date:', 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, $paymentDateTime, 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 10, 'Payment Status:', 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(140, 10, $payment['payment_status'], 0, 1);

        // Footer
        $pdf->Ln(15);
        $pdf->SetFont('Arial', 'I', 11);
        $pdf->Cell(0, 10, 'Thank you for choosing our salon!', 0, 1, 'C');

        // Output the PDF (force download)
        $pdf->Output('D', 'Receipt_' . $payment['payment_id'] . '.pdf');
    } else {
        echo "No payment found.";
    }
} else {
    echo "Payment ID not provided.";
}
?>
