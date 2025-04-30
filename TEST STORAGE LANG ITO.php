<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

$admin_id = $_SESSION['admin_id'];

// Handle Add Staff
if (isset($_POST['add_staff'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = trim($_POST['role']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $status = 'Active'; // Default status

    if (!empty($first_name) && !empty($last_name) && !empty($role)) {
        $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, role, email, phone, status, admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $role, $email, $phone, $status, $admin_id);
        $stmt->execute();
        header('Location: staff_management.php?success=added');
        exit();
    }
}

// Handle Edit Staff
if (isset($_POST['edit_staff'])) {
    $edit_id = intval($_POST['edit_id']);
    $first_name = trim($_POST['edit_first_name']);
    $last_name = trim($_POST['edit_last_name']);
    $role = trim($_POST['edit_role']);
    $email = trim($_POST['edit_email']);
    $phone = trim($_POST['edit_phone']);

    $stmt = $conn->prepare("UPDATE staff SET first_name = ?, last_name = ?, role = ?, email = ?, phone = ? WHERE staff_id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $role, $email, $phone, $edit_id);
    $stmt->execute();
    header('Location: staff_management.php?success=updated');
    exit();
}

// Handle Delete Staff
if (isset($_POST['delete_staff'])) {
    $delete_id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM staff WHERE staff_id = $delete_id");
    header('Location: staff_management.php?success=deleted');
    exit();
}

// Fetch Staff
$staff_members = [];
$result = $conn->query("SELECT * FROM staff WHERE admin_id = $admin_id ORDER BY staff_id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $staff_members[] = $row;
    }
}

// Fetch Admin Info
$stmt = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();

$firstName = explode(' ', $adminData['first_name'])[0];
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

<div class="main-content">
  <h2 class="mb-4">Manage Staff Members</h2>

  <?php if (isset($_GET['success'])): ?>
    <?php if ($_GET['success'] == 'added'): ?>
      <div class="alert alert-success">Staff member added successfully!</div>
    <?php elseif ($_GET['success'] == 'updated'): ?>
      <div class="alert alert-info">Staff member updated successfully!</div>
    <?php elseif ($_GET['success'] == 'deleted'): ?>
      <div class="alert alert-danger">Staff member deleted successfully!</div>
    <?php endif; ?>
  <?php endif; ?>

  <!-- Add Staff Form -->
  <div class="card p-4 mb-5">
    <form action="staff_management.php" method="POST">
      <input type="hidden" name="add_staff" value="1">

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="firstName" class="form-label">First Name</label>
          <input type="text" class="form-control" name="first_name" id="firstName" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="lastName" class="form-label">Last Name</label>
          <input type="text" class="form-control" name="last_name" id="lastName" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select class="form-select" name="role" id="role" required>
          <option value="" selected disabled>Select Role</option>
          <option value="Stylist">Stylist</option>
          <option value="Barber">Barber</option>
          <option value="Nail Technician">Nail Technician</option>
          <option value="Receptionist">Receptionist</option>
          <option value="Makeup Artist">Makeup Artist</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email (optional)</label>
        <input type="email" class="form-control" name="email" id="email">
      </div>

      <div class="mb-3">
        <label for="phone" class="form-label">Phone (optional)</label>
        <input type="text" class="form-control" name="phone" id="phone">
      </div>

      <button type="submit" class="btn btn-primary">Add Staff Member</button>
    </form>
  </div>

  <!-- Staff List -->
  <h3 class="mb-3">Staff Members</h3>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Full Name</th>
          <th>Role</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Status</th>
          <th>Date Added</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($staff_members)): ?>
          <?php foreach ($staff_members as $index => $staff): ?>
            <tr>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></td>
              <td><?= htmlspecialchars($staff['role']) ?></td>
              <td><?= htmlspecialchars($staff['email']) ?></td>
              <td><?= htmlspecialchars($staff['phone']) ?></td>
              <td><?= htmlspecialchars($staff['status']) ?></td>
              <td><?= date('F d, Y', strtotime($staff['created_at'])) ?></td>
              <td>
                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $staff['staff_id'] ?>">Edit</button>
                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $staff['staff_id'] ?>">Delete</button>
              </td>
            </tr>

            <!-- Edit Modal -->
            <div class="modal fade" id="editModal<?= $staff['staff_id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form action="staff_management.php" method="POST" class="modal-content">
                  <input type="hidden" name="edit_staff" value="1">
                  <input type="hidden" name="edit_id" value="<?= $staff['staff_id'] ?>">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Staff</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">First Name</label>
                      <input type="text" class="form-control" name="edit_first_name" value="<?= htmlspecialchars($staff['first_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Last Name</label>
                      <input type="text" class="form-control" name="edit_last_name" value="<?= htmlspecialchars($staff['last_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Role</label>
                      <select class="form-select" name="edit_role" required>
                        <option value="Stylist" <?= $staff['role'] == 'Stylist' ? 'selected' : '' ?>>Stylist</option>
                        <option value="Barber" <?= $staff['role'] == 'Barber' ? 'selected' : '' ?>>Barber</option>
                        <option value="Nail Technician" <?= $staff['role'] == 'Nail Technician' ? 'selected' : '' ?>>Nail Technician</option>
                        <option value="Receptionist" <?= $staff['role'] == 'Receptionist' ? 'selected' : '' ?>>Receptionist</option>
                        <option value="Makeup Artist" <?= $staff['role'] == 'Makeup Artist' ? 'selected' : '' ?>>Makeup Artist</option>
                        <option value="Other" <?= $staff['role'] == 'Other' ? 'selected' : '' ?>>Other</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email</label>
                      <input type="email" class="form-control" name="edit_email" value="<?= htmlspecialchars($staff['email']) ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Phone</label>
                      <input type="text" class="form-control" name="edit_phone" value="<?= htmlspecialchars($staff['phone']) ?>">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?= $staff['staff_id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form action="staff_management.php" method="POST" class="modal-content">
                  <input type="hidden" name="delete_staff" value="1">
                  <input type="hidden" name="delete_id" value="<?= $staff['staff_id'] ?>">
                  <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    Are you sure you want to delete <strong><?= htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) ?></strong>?
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  </div>
                </form>
              </div>
            </div>

          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center">No staff members found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
