<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Gestion du paiement des pénalités
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payer_penalite'])) {
    $stmt = $pdo->prepare("UPDATE emprunts SET penalite = 0 WHERE id = ?");
    $stmt->execute([$_POST['emprunt_id']]);
    $_SESSION['message'] = "Pénalité payée avec succès";
    redirect('penalites.php');
}

// Récupérer les pénalités impayées
$penalites = $pdo->query("
    SELECT e.*, 
           l.titre as livre_titre, 
           et.nom as etudiant_nom,
           et.matricule as etudiant_matricule,
           DATEDIFF(e.date_retour, e.date_retour_prevue) as jours_retard
    FROM emprunts e
    JOIN livres l ON e.livre_id = l.id
    JOIN etudiants et ON e.etudiant_id = et.id
    WHERE e.penalite > 0
    ORDER BY e.date_retour DESC
")->fetchAll();

include '../includes/header.php';
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['message'] ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<h2>Gestion des Pénalités</h2>

<div class="card">
    <div class="card-header">
        <h3>Pénalités impayées</h3>
    </div>
    <div class="card-body">
        <?php if (empty($penalites)): ?>
            <div class="alert alert-info">
                Aucune pénalité impayée pour le moment.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Étudiant</th>
                            <th>Date Emprunt</th>
                            <th>Date Retour Prévue</th>
                            <th>Date Retour Effectif</th>
                            <th>Jours de retard</th>
                            <th>Montant</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($penalites as $penalite): ?>
                        <tr>
                            <td><?= htmlspecialchars($penalite['livre_titre']) ?></td>
                            <td><?= htmlspecialchars($penalite['etudiant_nom']) ?> (<?= $penalite['etudiant_matricule'] ?>)</td>
                            <td><?= date('d/m/Y', strtotime($penalite['date_emprunt'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($penalite['date_retour_prevue'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($penalite['date_retour'])) ?></td>
                            <td><?= $penalite['jours_retard'] ?></td>
                            <td><?= $penalite['penalite'] ?> €</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="emprunt_id" value="<?= $penalite['id'] ?>">
                                    <button type="submit" name="payer_penalite" class="btn btn-sm btn-success">
                                        <i class="fas fa-money-bill-wave"></i> Payer
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>