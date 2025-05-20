<?php
session_start();
date_default_timezone_set('Asia/Manila'); // <-- Set to your local timezone
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

$admin_id = $_SESSION['admin_id'];

// Fetch admin first name
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$firstName = explode(' ', $adminData['first_name'])[0];

// Fetch staff
$staffQuery = "SELECT * FROM staff ORDER BY created_at DESC";
$staffResult = mysqli_query($conn, $staffQuery);
if (!$staffResult) {
    die("Error fetching staff: " . mysqli_error($conn));
}

$current_page = basename($_SERVER['PHP_SELF']);

$unreadCount = 0;

if ($admin_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS unread_count
        FROM admin_notifications
        WHERE is_read = 0
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $unreadCount = $data['unread_count'];
}
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Staff Management</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../admin/css/staff_management.css">
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
    <a class="nav-link <?= ($current_page == 'admin_walkins.php') ? 'active' : ''; ?>" href="admin_walkins.php">
        <i class="bi bi-door-open"></i> Walk-ins
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

    <a class="nav-link <?= ($current_page == 'admin_promos.php') ? 'active' : ''; ?>" href="admin_promos.php">
          <i class="bi bi-tag"></i> Promos
    </a>

    <a class="nav-link <?= ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
        <i class="bi bi-bell-fill"></i> Notifications
        <?php if ($unreadCount > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= $unreadCount ?>
            </span>
        <?php endif; ?>
    </a>
    <a class="nav-link <?= ($current_page == 'admin_help.php') ? 'active' : ''; ?>" href="admin_help.php">
      <i class="bi bi-question-circle"></i> Help
    </a>
    <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">
    <h2>Manage Staff Members</h2>
    <hr>
    <div class="staff-management-table table-responsive">
        <button class="btn mb-3" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="bi bi-plus-circle"></i> Add New Staff
        </button>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; while ($row = mysqli_fetch_assoc($staffResult)) : ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['role']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'Active') : ?>
                                <span class="badge bg-success">Active</span>
                            <?php else : ?>
                                <span class="badge bg-danger">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                        <td class="d-flex gap-2">
                            <button class="btn edit-btn btn-sm" data-bs-toggle="modal" data-bs-target="#editStaffModal<?= $row['staff_id'] ?>">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <button class="btn delete-btn btn-sm" data-bs-toggle="modal" data-bs-target="#deleteStaffModal<?= $row['staff_id'] ?>">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editStaffModal<?= $row['staff_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="staff/update_staff.php">
                                <input type="hidden" name="staff_id" value="<?= $row['staff_id'] ?>">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update Staff</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-2">
                                            <label>First Name</label>
                                            <input type="text" name="first_name" class="form-control" value="<?= $row['first_name'] ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label>Last Name</label>
                                            <input type="text" name="last_name" class="form-control" value="<?= $row['last_name'] ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label>Role</label>
                                            <select name="role" class="form-control" required>
                                                <option value="" disabled <?= ($row['role'] == '') ? 'selected' : '' ?>>--- Select Role ---</option>
                                                <option value="Hairstylist" <?= ($row['role'] == 'Hairstylist') ? 'selected' : '' ?>>Hairstylist</option>
                                                <option value="Barber" <?= ($row['role'] == 'Barber') ? 'selected' : '' ?>>Barber</option>
                                                <option value="Beautician/Nail Technician" <?= ($row['role'] == 'Beautician/Nail Technician') ? 'selected' : '' ?>>Beautician/Nail Technician</option>
                                                <option value="Junior Stylist" <?= ($row['role'] == 'Junior Stylist') ? 'selected' : '' ?>>Junior Stylist</option>
                                                <option value="Admin" <?= ($row['role'] == 'Admin') ? 'selected' : '' ?>>Admin</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Status</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="" disabled <?= ($row['status'] == '') ? 'selected' : '' ?>>--- Select Status ---</option>
                                                    <option value="Active" <?= $row['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                                    <option value="Inactive" <?= $row['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                                </select>
                                        </div>
                                        <div class="mb-2">
                                            <label>Email</label>
                                            <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>" required>
                                        </div>
                                        <div class="mb-2">
                                            <label>Phone</label>
                                            <input type="text" name="phone" class="form-control" value="<?= $row['phone'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update_staff" class="btn edit-btn">Update</button>
                                        <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteStaffModal<?= $row['staff_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="POST" action="staff/delete_staff.php">
                                <input type="hidden" name="staff_id" value="<?= $row['staff_id'] ?>">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Confirm Deletion</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete "<strong><?= $row['first_name'] . ' ' . $row['last_name'] ?></strong>" from the staff members?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="delete_staff" class="btn delete-btn">Yes</button>
                                        <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">No</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="staff/add_staff.php">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label>First Name</label>
                        <input type="text" name="first_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="" disabled selected>---Select Role---</option>
                            <option value="Hairstylist">Hairstylist</option>
                            <option value="Barber">Barber</option>
                            <option value="Beautician/Nail Technician">Beautician/Nail Technician</option>
                            <option value="Junior Stylist">Junior Stylist</option>
                            <option value="Admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="" disabled selected>---Select Status---</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>Phone</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_staff" class="btn">Add Staff</button>
                    <button type="button" class="btn cancel-btn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
