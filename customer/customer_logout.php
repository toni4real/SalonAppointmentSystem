<?php
session_start();

// Destroy customer session
session_unset();
session_destroy();

// Redirect to customer login page
header("Location: customer_login.php");
exit();
?>
