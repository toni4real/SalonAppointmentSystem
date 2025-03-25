<?php
// Admin Authentication Check
function checkAdmin() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: ../admin/admin_login.php');
        exit();
    }
}

// Customer Authentication Check
function checkCustomer() {
    if (!isset($_SESSION['customer_id'])) {
        header('Location: ../customer/customer_login.php');
        exit();
    }
}
?>
