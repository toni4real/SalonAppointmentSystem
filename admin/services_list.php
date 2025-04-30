<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

// Determine current page for navbar highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Ensure only admins can access this page
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Get services from database
$sql = "SELECT * FROM services";
$result = mysqli_query($conn, $sql);
$services = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $services[] = $row;
    }
}

$admin_id = $_SESSION['admin_id'];

// Get admin's name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();

// Dummy session variable for example
$firstName = explode(' ', $adminData['first_name'])[0]; // Get only the first word of the name
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../admin/css/services_list.css">
</head>

<body>

    <div class="sidebar d-flex flex-column">
        <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
        <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a class="nav-link <?= ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php">
            <i class="bi bi-person-circle"></i> Profile
        </a>
        <a class="nav-link <?= ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php">
            <i class="bi bi-calendar-check"></i> Appointments
        </a>
        <a class="nav-link <?= ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php">
            <i class="bi bi-credit-card-2-front"></i> Payments
        </a>
        <a class="nav-link <?= ($current_page == 'staff_management.php') ? 'active' : ''; ?>" href="staff_management.php">
            <i class="bi bi-person-gear"></i> Staff Management
        </a>
        <a class="nav-link <?= ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="staff_attendance.php">
            <i class="bi bi-person-lines-fill"></i> Staff Attendance
        </a>
        <a class="nav-link <?= ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="services_list.php">
            <i class="bi bi-stars"></i> Services
        </a>
        <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Manage Services</h2>

        <button type="button" class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addServiceModal">
            <i class="bi bi-plus-circle"></i> Add New Service
        </button>

        <div class="row g-4 mt-2">
            <?php foreach ($services as $service): ?>
                <div class="col-md-4">
                    <div class="card h-100 shadow">
                        <img src="../<?= $service['image']; ?>" class="card-img-top" alt="<?= $service['service_name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($service['service_name']); ?></h5>
                            <p><?= htmlspecialchars($service['description']); ?></p>
                            <p><strong>Price:</strong> PHP <?= htmlspecialchars($service['price']); ?></p>
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn edit-btn btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $service['service_id']; ?>">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button class="btn delete-btn btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $service['service_id']; ?>">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $service['service_id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="services/edit_service.php" enctype="multipart/form-data">
                            <input type="hidden" name="service_id" value="<?= $service['service_id']; ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Update Service</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Name:</label>
                                    <input type="text" class="form-control" name="service_name" value="<?= $service['service_name']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description:</label>
                                    <textarea class="form-control" name="description" required><?= $service['description']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price:</label>
                                    <input type="text" class="form-control" name="price" value="<?= $service['price']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image (optional):</label>
                                    <input type="file" class="form-control" name="image">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn">Update</button>
                                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal<?= $service['service_id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="services/delete_service.php">
                            <div class="modal-header">
                                <h5 class="modal-title">Confirm Deletion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" value="<?= $service['service_id']; ?>">
                                <p>Are you sure you want to delete "<strong><?= $service['service_name']; ?></strong>" from the services?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn delete-btn">Yes</button>
                                <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">No</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Add Service Modal -->
    <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="services/add_service.php" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">New Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name:</label>
                        <input type="text" class="form-control" name="service_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description:</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price:</label>
                        <input type="text" class="form-control" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image:</label>
                        <input type="file" class="form-control" name="image" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn">Add</button>
                    <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
