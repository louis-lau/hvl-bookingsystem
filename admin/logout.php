<?php
require_once __DIR__ . "/../includes/global.php";
session_start();
session_destroy();
header("Location: {$baseURL}");