<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}

// Get the selected service filter from GET parameters
$selectedService = isset($_GET['service']) ? $_GET['service'] : '';

// Prepare the base query for appointments, joining customers and services
$query = "
SELECT
    customers.first_name AS customer_name,
    services.service_name,
    appointments.appointment_date,
    appointments.appointment_time,
    appointments.type

FROM appointments
JOIN customers ON appointments.customer_id = customers.customer_id
JOIN services ON appointments.service_id = services.service_id
";

// If a service is selected, add WHERE clause to filter by service
if ($selectedService !== '') {
    $query .= " WHERE services.service_name = ?";
}

$query .= " ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC LIMIT 50";

// Prepare and execute query
if ($selectedService !== '') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $selectedService);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}

function getCount($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die('Query failed: ' . mysqli_error($conn));
    }
    return mysqli_fetch_assoc($result)['total'];
}

$admin_id = $_SESSION['admin_id'];

// Get admin's name
$stmtAdmin = $conn->prepare("SELECT first_name FROM admins WHERE admin_id = ?");
$stmtAdmin->bind_param("i", $admin_id);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();
$adminData = $resultAdmin->fetch_assoc();

$firstName = explode(' ', $adminData['first_name'])[0]; // Get only the first word of the name

$customerCount = getCount("SELECT COUNT(*) as total FROM customers");
$staffCount = getCount("SELECT COUNT(*) as total FROM staff");
$appointmentCount = getCount("SELECT COUNT(*) as total FROM appointments");
$pendingPayments = getCount("SELECT COUNT(*) as total FROM appointments WHERE payment_status = 'pending'");

// Get unread notification count for the admin
$unreadCount = 0;
if ($admin_id) {
    $stmtNotify = $conn->prepare("
    SELECT COUNT(*) AS unread_count
    FROM admin_notifications
    WHERE is_read = 0
    ");
    $stmtNotify->execute();
    $resultNotify = $stmtNotify->get_result();
    $dataNotify = $resultNotify->fetch_assoc();
    $unreadCount = $dataNotify['unread_count'];
}

// Fetch all services for the dropdown filter
$servicesResult = $conn->query("SELECT service_name FROM services ORDER BY service_name ASC");
$services = [];
if ($servicesResult && $servicesResult->num_rows > 0) {
    while ($serviceRow = $servicesResult->fetch_assoc()) {
        $services[] = $serviceRow['service_name'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../admin/css/admin_dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <h2>Dashboard Overview</h2>
        <hr>
        <div class="dashboard-cards">
            <div class="card">
                <i class="bi bi-people-fill"></i>
                <h5>Total Customers</h5>
                <h2><?php echo $customerCount; ?></h2>
            </div>
            <div class="card">
                <i class="bi bi-person-badge-fill"></i>
                <h5>Total Staff</h5>
                <h2><?php echo $staffCount; ?></h2>
            </div>
            <div class="card">
                <i class="bi bi-calendar-check-fill"></i>
                <h5>Total Appointments</h5>
                <h2><?php echo $appointmentCount; ?></h2>
            </div>
            <div class="card">
                <i class="bi bi-cash-stack"></i>
                <h5>Pending Payments</h5>
                <h2><?php echo $pendingPayments; ?></h2>
            </div>
        </div>

    <div class="container mt-4">
    <h4 class="mt-5 mb-3">Performance Metrics</h4>
    <hr>
    <div class="row">
            <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                <h5 class="card-title">Appointments per Month</h5>
                <canvas id="appointmentsChart" height="200"></canvas>
                </div>
            </div>
            </div>
            <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                <h5 class="card-title">Monthly Revenue</h5>
                <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>
            </div>
        </div>

    <h4 class="mt-5 mb-3">Insights</h4>
    <hr>
    <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">Top 5 Services</h5>
                        <canvas id="topServicesChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                    <h5 class="card-title">Appointment Status</h5>
                    <canvas id="appointmentStatusChart" height="200"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-7 mb-4">
              <div class="card shadow-sm h-100">
                <div class="card-body">
                  <h5 class="card-title">All Services Usage</h5>
                  <div class="mb-3">
                    <label for="serviceSelect" class="form-label">Select Service:</label>
                    <select id="serviceSelect" class="form-select">
                      <option value="">-- All Services --</option>
                    </select>
                  </div>
                  <canvas id="allServicesChart" height="200"></canvas>
                  <form method="GET" class="mb-3">
        <label for="service" class="form-label">Filter Appointments by Service:</label>
        <select name="service" id="service" class="form-select" onchange="this.form.submit()">
            <option value="">-- All Services --</option>
            <?php foreach ($services as $serviceName): ?>
                <option value="<?= htmlspecialchars($serviceName) ?>" <?= ($serviceName === $selectedService) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($serviceName) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Appointments Table -->
    
    <table class="table table-striped table-bordered ">
        <thead>
    <tr>
        <th>Customer Name</th>
        <th>Service Name</th> <!-- ✅ Add this -->
        <th>Date</th>
        <th>Time</th>
        <th>Type</th>
    </tr>
</thead>

        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?= htmlspecialchars($row['customer_name']) ?></td>
    <td><?= htmlspecialchars($row['service_name']) ?></td> <!-- ✅ Add this -->
    <td><?= htmlspecialchars(date('M d, Y', strtotime($row['appointment_date']))) ?></td>
    <td><?= htmlspecialchars(date('h:i A', strtotime($row['appointment_time']))) ?></td>
    <td><?= htmlspecialchars($row['type']) ?></td>
</tr>
<?php endwhile; ?>

        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">No appointments found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
                </div>
              </div>
            </div>

           
            

    </div>
          

    </div>

</div>



<script>
        fetch('charts/appointments_per_month.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('appointmentsChart').getContext('2d');
            new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                label: 'Appointments per Month',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                y: { beginAtZero: true }
                }
            }
            });
        });
</script>

<script>
fetch('charts/top_services.php')
  .then(response => response.json())
  .then(result => {
    const ctx2 = document.getElementById('topServicesChart').getContext('2d');
    new Chart(ctx2, {
      type: 'doughnut',
      data: {
        labels: result.labels,
        datasets: [{
          label: 'Top 5 Services',
          data: result.data,
          backgroundColor: [
            'rgba(255, 99, 132, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(255, 205, 86, 0.6)',
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)'
          ],
          borderColor: 'rgba(255,255,255,1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  });
</script>

<script>
fetch('charts/appointment_status_distribution.php')
  .then(response => response.json())
  .then(result => {
    const ctx3 = document.getElementById('appointmentStatusChart').getContext('2d');
    new Chart(ctx3, {
      type: 'pie',
      data: {
        labels: result.labels,
        datasets: [{
          label: 'Appointment Status',
          data: result.data,
          backgroundColor: [
            'rgba(255, 205, 86, 0.7)',   // Pending
            'rgba(54, 162, 235, 0.7)',   // Confirmed
            'rgba(75, 192, 192, 0.7)'    // Completed
          ],
          borderColor: 'rgba(255,255,255,1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  });
</script>

<script>
fetch('charts/revenue_per_month.php')
  .then(response => response.json())
  .then(data => {
    const ctx4 = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx4, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
          label: 'Revenue per Month',
          data: data,
          fill: false,
          borderColor: 'rgba(75, 192, 192, 1)',
          tension: 0.1
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  });
</script>

<script>
let allServicesChart = null;

function renderAllServicesChart(labels, data) {
  const ctx = document.getElementById('allServicesChart').getContext('2d');

  if (allServicesChart) {
    allServicesChart.destroy();
  }

  allServicesChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        label: 'Service Usage',
        data: data,
        backgroundColor: [
          'rgba(255, 99, 132, 0.6)',
          'rgba(255, 159, 64, 0.6)',
          'rgba(255, 205, 86, 0.6)',
          'rgba(75, 192, 192, 0.6)',
          'rgba(153, 102, 255, 0.6)',
          'rgba(201, 203, 207, 0.6)',
          'rgba(54, 162, 235, 0.6)'
        ],
        borderColor: 'rgba(255,255,255,1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom' }
      }
    }
  });
}

function loadServices(selected = '') {
  fetch(`charts/all_services.php${selected ? '?service=' + selected : ''}`)
    .then(response => response.json())
    .then(result => {
      const dropdown = document.getElementById('serviceSelect');
      dropdown.innerHTML = '<option value="">-- All Services --</option>';
      result.services.forEach(service => {
        const opt = document.createElement('option');
        opt.value = service;
        opt.textContent = service;
        if (service === selected) opt.selected = true;
        dropdown.appendChild(opt);
      });
      renderAllServicesChart(result.labels, result.data);
    })
    .catch(error => console.error('Chart load error:', error));
}

// Initial load
loadServices();

// Event listener for dropdown change
document.getElementById('serviceSelect').addEventListener('change', function () {
  const selectedService = this.value;
  loadServices(selectedService);
});
</script>

<script>
function loadServiceAppointmentsDropdown() {
  fetch('charts/all_services.php')
    .then(res => res.json())
    .then(data => {
      const dropdown = document.getElementById('serviceAppointmentsSelect');
      dropdown.innerHTML = '<option value="">-- Select a Service --</option>';
      data.services.forEach(service => {
        const option = document.createElement('option');
        option.value = service;
        option.textContent = service;
        dropdown.appendChild(option);
      });
    });
}
</script>

<script>
function filterByService() {
  const select = document.getElementById('serviceFilter');
  const selectedService = select.value;
  const url = new URL(window.location.href);
  
  if (selectedService) {
    url.searchParams.set('service', selectedService);
  } else {
    url.searchParams.delete('service');
  }
  
  window.location.href = url.toString();  
}
</script>



<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>