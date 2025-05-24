<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// auth.php - Gestion centralisée de l'authentification

// Vérifier si database.php est déjà inclus
if (!isset($pdo)) {
    require_once __DIR__ . '/../config/database.php';
}

/**
 * Redirection sécurisée
 */
function redirect(string $url, int $statusCode = 303): void {
    // Nettoyer l'URL
    $url = filter_var($url, FILTER_SANITIZE_URL);
    
    // Vérifier si l'URL est relative
    if (!preg_match('#^https?://#i', $url)) {
        $base = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . 
               $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
        $url = rtrim($base, '/') . '/' . ltrim($url, '/');
    }
    
    header("Location: $url", true, $statusCode);
    exit;
}

/**
 * Vérification de l'authentification
 */
function checkAuth(): void {
    if (!isset($_SESSION['user'])) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user']);
}