<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Delete promo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_promo_id'])) {
    $delete_id = intval($_POST['delete_promo_id']);
    $stmtDelete = $conn->prepare("DELETE FROM promos WHERE promo_id = ?");
    $stmtDelete->bind_param("i", $delete_id);
    $stmtDelete->execute();

    header('Location: admin_promos.php');
    exit;
}

// Add promo with overlap check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_promo_id'])) {
    $promo_name = $_POST['promo_name'];
    $discount_percent = floatval($_POST['discount_percent']) / 100;
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Check for overlapping date range
    $stmt = $conn->prepare("SELECT COUNT(*) AS overlap_count FROM promos WHERE NOT (end_date < ? OR start_date > ?)");
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['overlap_count'] > 0) {
        echo "<script>alert('Promo dates overlap with an existing promo. Please select a different date range.'); window.location.href='admin_promos.php';</script>";
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO promos (promo_name, discount_percent, start_date, end_date, is_active) VALUES (?, ?, ?, ?, 0)");
    $stmt->bind_param("sdss", $promo_name, $discount_percent, $start_date, $end_date);
    $stmt->execute();

    header('Location: admin_promos.php');
    exit;
}

// Update promo status based on today's date
$today = date('Y-m-d');
$conn->query("UPDATE promos SET is_active = 1 WHERE start_date <= '$today' AND end_date >= '$today'");
$conn->query("UPDATE promos SET is_active = 0 WHERE start_date > '$today' OR end_date < '$today'");

// Fetch promos
$promos = $conn->query("SELECT * FROM promos ORDER BY promo_id DESC");
$current_page = basename($_SERVER['PHP_SELF']);

// Admin first name
$admin_id = $_SESSION['admin_id'];
$stmtAdmin = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmtAdmin->bind_param("i", $admin_id);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();
$adminData = $resultAdmin->fetch_assoc();
$firstName = explode(' ', $adminData['first_name'])[0];

// Notification count
$unreadCount = 0;
if ($admin_id) {
    $stmtNotify = $conn->prepare("SELECT COUNT(*) AS unread_count FROM admin_notifications WHERE is_read = 0");
    $stmtNotify->execute();
    $resultNotify = $stmtNotify->get_result();
    $dataNotify = $resultNotify->fetch_assoc();
    $unreadCount = $dataNotify['unread_count'];
}

// Get existing promo dates for JS
$existing_dates = [];
$result = $conn->query("SELECT start_date, end_date FROM promos");
while ($row = $result->fetch_assoc()) {
    $existing_dates[] = ['start' => $row['start_date'], 'end' => $row['end_date']];
}
$existing_dates_json = json_encode($existing_dates);
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="../admin/css/admin_promos.css" />
</head>
<body>
    <div class="sidebar d-flex flex-column">
        <h4 class="text-white mb-4">Hi, <?= htmlspecialchars($firstName) ?> <span class="wave">👋</span></h4>
        <a class="nav-link <?= ($current_page == 'admin_dashboard.php') ? 'active' : ''; ?>" href="admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a class="nav-link <?= ($current_page == 'admin_profile.php') ? 'active' : ''; ?>" href="admin_profile.php"><i class="bi bi-person-circle"></i> Profile</a>
        <a class="nav-link <?= ($current_page == 'admin_appointments.php') ? 'active' : ''; ?>" href="admin_appointments.php"><i class="bi bi-calendar-check"></i> Appointments</a>
        <a class="nav-link <?= ($current_page == 'payment_history.php') ? 'active' : ''; ?>" href="payment_history.php"><i class="bi bi-credit-card-2-front"></i> Payments</a>
        <a class="nav-link <?= ($current_page == 'admin_walkins.php') ? 'active' : ''; ?>" href="admin_walkins.php"><i class="bi bi-door-open"></i> Walk-ins</a>
        <a class="nav-link <?= ($current_page == 'staff_management.php') ? 'active' : ''; ?>" href="staff_management.php"><i class="bi bi-person-gear"></i> Staff Management</a>
        <a class="nav-link <?= ($current_page == 'staff_attendance.php') ? 'active' : ''; ?>" href="staff_attendance.php"><i class="bi bi-person-lines-fill"></i> Staff Attendance</a>
        <a class="nav-link <?= ($current_page == 'services_list.php') ? 'active' : ''; ?>" href="services_list.php"><i class="bi bi-stars"></i> Services</a>
        <a class="nav-link <?= ($current_page == 'admin_promos.php') ? 'active' : ''; ?>" href="admin_promos.php"><i class="bi bi-tag"></i> Promos</a>
        <a class="nav-link <?= ($current_page == 'notifications.php') ? 'active' : ''; ?>" href="notifications.php">
            <i class="bi bi-bell-fill"></i> Notifications
            <?php if ($unreadCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link <?= ($current_page == 'admin_help.php') ? 'active' : ''; ?>" href="admin_help.php"><i class="bi bi-question-circle"></i> Help</a>
        <a class="nav-link btn btn-danger mt-auto text-white" href="admin_logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <div class="main-content container py-4">
        <h2 class="mb-4">Add Promo</h2>
        <form method="POST" class="mb-5">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="promo_name" class="form-label">Promo Name</label>
                    <input type="text" id="promo_name" name="promo_name" class="form-control" required />
                </div>
                <div class="col-md-2">
                    <label for="discount_percent" class="form-label">Discount %</label>
                    <input type="number" step="0.01" id="discount_percent" name="discount_percent" class="form-control" required />
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required />
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required />
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Add Promo</button>
        </form>

        <h2 class="mb-3">Existing Promos</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Discount %</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Active</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($promo = $promos->fetch_assoc()): ?>
                    <tr>
                        <td><?= $promo['promo_id'] ?></td>
                        <td><?= htmlspecialchars($promo['promo_name']) ?></td>
                        <td><?= $promo['discount_percent'] * 100 ?></td>
                        <td><?= $promo['start_date'] ?></td>
                        <td><?= $promo['end_date'] ?></td>
                        <td><?= $promo['is_active'] ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this promo?');">
                                <input type="hidden" name="delete_promo_id" value="<?= $promo['promo_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const existingRanges = <?= $existing_dates_json ?>;

    function rangesOverlap(start1, end1, start2, end2) {
        return (start1 <= end2 && end1 >= start2);
    }

    function isRangeOverlapping(startStr, endStr) {
        const start = new Date(startStr);
        const end = new Date(endStr);
        return existingRanges.some(range => {
            const existingStart = new Date(range.start);
            const existingEnd = new Date(range.end);
            return rangesOverlap(start, end, existingStart, existingEnd);
        });
    }

    // 🔒 Prevent past date selection
    window.addEventListener('DOMContentLoaded', () => {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').setAttribute('min', today);
        document.getElementById('end_date').setAttribute('min', today);
    });

    // 🛡️ Prevent overlapping
    document.querySelector('form').addEventListener('submit', function (e) {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;

        if (start && end && isRangeOverlapping(start, end)) {
            alert('This promo date range overlaps with an existing promo. Please choose a different range.');
            e.preventDefault();
        }
    });
</script>


    
</body>
</html>
