<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    session_destroy();
    header("Location: {$baseURL}admin/login.php");
    exit;
}