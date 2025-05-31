<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Gestion des réservations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['annuler_reservation'])) {
        // Annuler la réservation et rendre le livre disponible
        $pdo->beginTransaction();
        try {
            // Récupérer l'ID du livre avant annulation
            $stmt = $pdo->prepare("SELECT livre_id FROM reservations WHERE id = ?");
            $stmt->execute([$_POST['reservation_id']]);
            $livre_id = $stmt->fetchColumn();
            
            // Supprimer la réservation
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute([$_POST['reservation_id']]);
            
            // Rendre le livre disponible
            $stmt = $pdo->prepare("UPDATE livres SET disponible = TRUE WHERE id = ?");
            $stmt->execute([$livre_id]);
            
            $pdo->commit();
            $_SESSION['message'] = "Réservation annulée et livre rendu disponible";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }
        redirect('reservations.php');
    } elseif (isset($_POST['emprunter'])) {
        // Créer un emprunt à partir de la réservation
        $pdo->beginTransaction();
        try {
            // Récupérer la réservation
            $reservation = $pdo->query("SELECT * FROM reservations WHERE id = " . $_POST['reservation_id'])->fetch();
            
            // Créer l'emprunt
            $date_retour_prevue = date('Y-m-d H:i:s', strtotime('+15 days'));
            $stmt = $pdo->prepare("INSERT INTO emprunts 
                                  (livre_id, etudiant_id, agent_id, date_retour_prevue) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $reservation['livre_id'],
                $reservation['etudiant_id'],
                $_SESSION['user']['id'],
                $date_retour_prevue
            ]);
            
            // Supprimer la réservation
            $stmt = $pdo->prepare("DELETE FROM reservations WHERE id = ?");
            $stmt->execute([$_POST['reservation_id']]);
            
            $pdo->commit();
            $_SESSION['message'] = "Emprunt créé avec succès";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }
        redirect('reservations.php');
    }
}

// Récupérer toutes les réservations
$reservations = $pdo->query("
    SELECT r.*, 
           l.titre as livre_titre, 
           l.isbn as livre_isbn,
           et.nom as etudiant_nom,
           et.matricule as etudiant_matricule
    FROM reservations r
    JOIN livres l ON r.livre_id = l.id
    JOIN etudiants et ON r.etudiant_id = et.id
    ORDER BY r.date_reservation DESC
")->fetchAll();

include '../includes/header.php';
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['message'] ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error'] ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<h2>Gestion des Réservations</h2>

<div class="card">
    <div class="card-header">
        <h3>Liste des Réservations</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Étudiant</th>
                        <th>Date Réservation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= htmlspecialchars($reservation['livre_titre']) ?> (<?= $reservation['livre_isbn'] ?>)</td>
                        <td><?= htmlspecialchars($reservation['etudiant_nom']) ?> (<?= $reservation['etudiant_matricule'] ?>)</td>
                        <td><?= date('d/m/Y H:i', strtotime($reservation['date_reservation'])) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                <button type="submit" name="emprunter" class="btn btn-sm btn-success">
                                    <i class="fas fa-book"></i> Emprunter
                                </button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                <button type="submit" name="annuler_reservation" class="btn btn-sm btn-danger">
                                    <i class="fas fa-times"></i> Annuler
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>