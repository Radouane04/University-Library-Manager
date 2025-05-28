<?php
// index.php - Point d'entrée principal

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isLoggedIn()) {
    // On suppose que le rôle est stocké dans la session après connexion
    $role = $_SESSION['role'] ?? '';

    if ($role === 'etudiant') {
        redirect('pages/etudiant.php');
    } elseif ($role === 'admin') {
        redirect('pages/dashboard.php');
    } else {
        // Redirection par défaut si le rôle est inconnu
        redirect('pages/login.php');
    }
} else {
    redirect('pages/login.php');
}