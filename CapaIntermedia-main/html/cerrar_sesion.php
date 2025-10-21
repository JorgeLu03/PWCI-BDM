<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header('Location: inicio.php'); // Redirect to the home page
exit();
?>