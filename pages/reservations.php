<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Gestion des réservations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_reservation'])) {
        $stmt = $pdo->prepare("INSERT INTO reservations (livre_id, etudiant_id) VALUES (?, ?)");
        $stmt->execute([
            $_POST['livre_id'],
            $_POST['etudiant_id']
        ]);
        $_SESSION['message'] = "Réservation enregistrée";
        redirect('reservations.php');
    } elseif (isset($_POST['annuler_reservation'])) {
        $stmt = $pdo->prepare("UPDATE reservations SET statut = 'annulee' WHERE id = ?");
        $stmt->execute([$_POST['reservation_id']]);
        $_SESSION['message'] = "Réservation annulée";
        redirect('reservations.php');
    } elseif (isset($_POST['valider_reservation'])) {
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
            
            // Mettre à jour la disponibilité du livre
            $stmt = $pdo->prepare("UPDATE livres SET disponible = FALSE WHERE id = ?");
            $stmt->execute([$reservation['livre_id']]);
            
            // Mettre à jour le statut de la réservation
            $stmt = $pdo->prepare("UPDATE reservations SET statut = 'completee' WHERE id = ?");
            $stmt->execute([$_POST['reservation_id']]);
            
            $pdo->commit();
            $_SESSION['message'] = "Réservation validée et emprunt créé";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur: " . $e->getMessage();
        }
        redirect('reservations.php');
    }
}

// Récupérer les réservations
$reservations = $pdo->query("
    SELECT r.*, 
           l.titre as livre_titre, 
           l.isbn as livre_isbn,
           et.nom as etudiant_nom,
           et.matricule as etudiant_matricule
    FROM reservations r
    JOIN livres l ON r.livre_id = l.id
    JOIN etudiants et ON r.etudiant_id = et.id
    WHERE r.statut = 'en_attente'
    ORDER BY r.date_reservation
")->fetchAll();

// Récupérer les livres non disponibles
$livres_indisponibles = $pdo->query("
    SELECT l.* 
    FROM livres l
    WHERE l.disponible = FALSE
    AND NOT EXISTS (
        SELECT 1 FROM emprunts e 
        WHERE e.livre_id = l.id AND e.date_retour IS NULL
    )
")->fetchAll();

// Récupérer les étudiants
$etudiants = $pdo->query("SELECT * FROM etudiants ORDER BY nom")->fetchAll();

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

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Nouvelle Réservation</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Livre (indisponible)</label>
                        <select name="livre_id" class="form-control" required>
                            <option value="">Sélectionner un livre</option>
                            <?php foreach ($livres_indisponibles as $livre): ?>
                            <option value="<?= $livre['id'] ?>">
                                <?= htmlspecialchars($livre['titre']) ?> (<?= $livre['isbn'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Étudiant</label>
                        <select name="etudiant_id" class="form-control" required>
                            <option value="">Sélectionner un étudiant</option>
                            <?php foreach ($etudiants as $etudiant): ?>
                            <option value="<?= $etudiant['id'] ?>">
                                <?= htmlspecialchars($etudiant['nom']) ?> (<?= $etudiant['matricule'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="add_reservation" class="btn btn-primary">
                        Enregistrer la réservation
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Réservations en attente</h3>
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
                                <button type="submit" name="valider_reservation" class="btn btn-sm btn-success">
                                    <i class="fas fa-check"></i> Valider
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