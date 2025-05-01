<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

$customerQuery = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id = '$customer_id'");
$customer = mysqli_fetch_assoc($customerQuery);

$appointmentQuery = mysqli_query($conn, "SELECT a.*, s.service_name, st.first_name AS staff_name 
    FROM appointments a
    JOIN services s ON a.service_id = s.service_id
    JOIN staff st ON a.staff_id = st.staff_id
    WHERE a.customer_id = '$customer_id'
    AND a.status != 'Cancelled'
    AND NOT (a.status = 'Completed' AND a.payment_status = 'Paid')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/customer_dashboard1.css">
</head>
<body>

<div class="sidebar">
    <h5 class="fw-bold mb-4">Salon Customer Panel</h5>
    <a class="nav-link" href="profile.php"><i class="bi bi-person-circle"></i> Profile</a>
    <a class="nav-link active" href="customer_dashboard.php"><i class="bi bi-speedometer2"></i> Your Appointments</a>
    <a class="nav-link" href="appointment_booking.php"><i class="bi bi-calendar-plus-fill"></i> Book Appointment</a>
    <a class="nav-link" href="customer_history.php"><i class="bi bi-clock-history"></i> Appointment History</a>
    <a class="nav-link" href="notifications.php"><i class="bi bi-bell"></i> Notifications</a>
    <a class="nav-link" href="help.php"><i class="bi bi-question-circle"></i> Help</a>
    <a class="btn btn-danger text-white" href="customer_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
</div>

<div class="main-content">
    <div class="welcome-message text-center mb-4">
        <h2>Welcome To Customer Dashboard</h2>
    </div>

    <div class="table-container">
        <h4>Your Appointments</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Staff</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($appointment = mysqli_fetch_assoc($appointmentQuery)): ?>
                        <tr>
                            <td><?= htmlspecialchars($appointment['service_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['staff_name']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_date']) ?></td>
                            <td><?= htmlspecialchars($appointment['appointment_time']) ?></td>
                            <td><?= htmlspecialchars($appointment['status']) ?></td>
                            <td><?= htmlspecialchars($appointment['payment_status']) ?></td>
                            <td>
                                <?php if ($appointment['status'] !== 'Confirmed'): ?>
                                    <a href="actions/edit_appointment.php?id=<?= $appointment['appointment_id'] ?>" class="btn btn-sm text-white" style="background-color: #6f42c1;">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $appointment['appointment_id'] ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $appointment['appointment_id'] ?>">
                                        <i class="bi bi-x-circle"></i> Cancel Appointment
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Cancel Modal -->
                        <div class="modal fade" id="cancelModal<?= $appointment['appointment_id'] ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?= $appointment['appointment_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="cancelModalLabel<?= $appointment['appointment_id'] ?>">Cancel Appointment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to cancel this appointment?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="actions/cancel_appointment.php?id=<?= $appointment['appointment_id'] ?>" class="btn btn-warning">Confirm</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delete Modal -->
                        <div class="modal fade" id="deleteModal<?= $appointment['appointment_id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $appointment['appointment_id'] ?>" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteModalLabel<?= $appointment['appointment_id'] ?>">Delete Appointment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete this appointment?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="actions/delete_appointment.php?id=<?= $appointment['appointment_id'] ?>" class="btn btn-danger">Confirm</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
