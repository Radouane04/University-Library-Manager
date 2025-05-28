<?php
require_once 'config.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Veuillez vous connecter pour réserver']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$bookId = $_POST['book_id'] ?? null;

if (!$bookId) {
    echo json_encode(['success' => false, 'message' => 'ID livre manquant']);
    exit;
}

try {
    // Vérifier la disponibilité
    $stmt = $pdo->prepare("SELECT disponible FROM livres WHERE id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();
    
    if (!$book) {
        echo json_encode(['success' => false, 'message' => 'Livre introuvable']);
        exit;
    }
    
    if (!$book['disponible']) {
        echo json_encode(['success' => false, 'message' => 'Livre déjà réservé']);
        exit;
    }
    
    // Mettre à jour la disponibilité
    $update = $pdo->prepare("UPDATE livres SET disponible = 0 WHERE id = ?");
    $update->execute([$bookId]);
    
    // Enregistrer la réservation
    $reservation = $pdo->prepare("INSERT INTO reservations (livre_id, user_id, date_reservation) VALUES (?, ?, NOW())");
    $reservation->execute([$bookId, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Réservation confirmée']);
    
} catch (PDOException $e) {
    error_log("Reservation error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}