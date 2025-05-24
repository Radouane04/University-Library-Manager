<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/../includes/auth.php';



session_destroy();
redirect('login.php');
?>