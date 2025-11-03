<?php
// Auto redirect to login page or dashboard
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit();
?>