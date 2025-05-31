<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Fonctions utilitaires pour l'application
 */

/**
 * Vérifie si un livre est disponible
 */
function isLivreDisponible($livre_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT disponible FROM livres WHERE id = ?");
    $stmt->execute([$livre_id]);
    $result = $stmt->fetch();
    
    return $result && $result['disponible'];
}

/**
 * Vérifie si un étudiant a déjà emprunté un livre
 */
function hasEmprunteLivre($etudiant_id, $livre_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM emprunts 
                          WHERE etudiant_id = ? AND livre_id = ? AND date_retour IS NULL");
    $stmt->execute([$etudiant_id, $livre_id]);
    
    return $stmt->fetchColumn() > 0;
}

/**
 * Calcule les pénalités pour un emprunt
 */
function calculerPenalite($emprunt_id) {
    global $pdo;
    
    $emprunt = $pdo->query("SELECT * FROM emprunts WHERE id = $emprunt_id")->fetch();
    
    if (!$emprunt || !$emprunt['date_retour'] || $emprunt['penalite'] > 0) {
        return 0;
    }
    
    $date_retour_prevue = new DateTime($emprunt['date_retour_prevue']);
    $date_retour = new DateTime($emprunt['date_retour']);
    
    if ($date_retour > $date_retour_prevue) {
        $jours_retard = $date_retour->diff($date_retour_prevue)->days;
        return $jours_retard * 1.5; // 1.5€ par jour de retard
    }
    
    return 0;
}

/**
 * Génère un matricule étudiant unique
 */
function genererMatricule() {
    global $pdo;
    
    do {
        $matricule = 'ET' . date('Y') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $exists = $pdo->query("SELECT COUNT(*) FROM etudiants WHERE matricule = '$matricule'")->fetchColumn();
    } while ($exists > 0);
    
    return $matricule;
}

/**
 * Récupère les statistiques globales
 */
function getStats() {
    global $pdo;
    
    return $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM livres) as total_livres,
            (SELECT COUNT(*) FROM etudiants) as total_etudiants,
            (SELECT COUNT(*) FROM emprunts WHERE date_retour IS NULL) as emprunts_actifs,
            (SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente') as reservations_attente,
            (SELECT SUM(penalite) FROM emprunts WHERE penalite > 0) as total_penalites
    ")->fetch();
}

/**
 * Formate une date pour l'affichage
 */
function formatDate($date, $format = 'd/m/Y') {
    if (!$date) return '-';
    return date($format, strtotime($date));
}

/**
 * Vérifie si un ISBN est valide
 */
