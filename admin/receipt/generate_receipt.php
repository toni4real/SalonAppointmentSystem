<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/auth.php';
require('../../fpdf/fpdf.php');

date_default_timezone_set('Asia/Manila');

// Check if payment_id is set
if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];

    // Fetch payment details
    $query = "SELECT p.*, c.first_name, c.last_name, a.appointment_date, a.appointment_time, a.payment_status, s.service_name
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
        // Format appointment date and time AFTER fetching
        $formattedDate = date('F j, Y', strtotime($payment['appointment_date']));
        $formattedTime = date('h:i A', strtotime($payment['appointment_time']));

        // Optional: Format payment date too (if you want)
        $formattedPaymentDate = date('F j, Y', strtotime($payment['payment_date'])) . ' at ' . date('h:i A', strtotime($payment['payment_date']));

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
        $pdf->Cell(150, 10, $formattedDate . ' at ' . $formattedTime, 0, 1);

        $pdf->Cell(40, 10, 'Service: ', 0, 0);
        $pdf->Cell(150, 10, $payment['service_name'], 0, 1);

        // Payment details
        $pdf->Cell(40, 10, 'Amount Paid: ', 0, 0);
        $pdf->Cell(150, 10, 'PHP ' . number_format($payment['amount'], 2), 0, 1);

        $pdf->Cell(40, 10, 'Payment Date: ', 0, 0);
        $pdf->Cell(150, 10, $formattedPaymentDate, 0, 1);

        $pdf->Cell(40, 10, 'Payment Status: ', 0, 0);
        $pdf->Cell(150, 10, $payment['payment_status'], 0, 1);

        // Footer
        $pdf->Ln(10);
        $pdf->Cell(200, 10, 'Thank you for choosing our salon!', 0, 1, 'C');

        // Output the PDF as forced download
        $pdf->Output('D', 'Receipt_' . $payment['payment_id'] . '.pdf');
    } else {
        // No payment found
        echo "No payment found.";
    }
} else {
    // No payment_id provided
    echo "Payment ID is missing.";
}
?>
