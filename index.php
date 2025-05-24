<?php
// index.php - Point d'entrée principal

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirect('pages/dashboard.php');
} else {
    redirect('pages/login.php');
}