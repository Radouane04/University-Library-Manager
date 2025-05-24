<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Récupérer les statistiques
$stats = $pdo->query("
    SELECT 
        (SELECT COUNT(*) FROM livres) as total_livres,
        (SELECT COUNT(*) FROM etudiants) as total_etudiants,
        (SELECT COUNT(*) FROM emprunts WHERE date_retour IS NULL) as emprunts_actifs,
        (SELECT COUNT(*) FROM emprunts WHERE date_retour IS NOT NULL) as emprunts_termines,
        (SELECT COUNT(*) FROM reservations WHERE statut = 'en_attente') as reservations_attente,
        (SELECT SUM(penalite) FROM emprunts WHERE penalite > 0) as total_penalites
")->fetch();

// Derniers emprunts
$derniers_emprunts = $pdo->query("
    SELECT e.*, 
           l.titre as livre_titre, 
           et.nom as etudiant_nom
    FROM emprunts e
    JOIN livres l ON e.livre_id = l.id
    JOIN etudiants et ON e.etudiant_id = et.id
    ORDER BY e.date_emprunt DESC
    LIMIT 5
")->fetchAll();

include '../includes/header.php';
?>

<h2>Tableau de Bord</h2>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card bg-primary">
            <h3>Livres</h3>
            <p><?= $stats['total_livres'] ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-success">
            <h3>Étudiants</h3>
            <p><?= $stats['total_etudiants'] ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-warning">
            <h3>Emprunts Actifs</h3>
            <p><?= $stats['emprunts_actifs'] ?></p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-info">
            <h3>Réservations</h3>
            <p><?= $stats['reservations_attente'] ?></p>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Derniers Emprunts</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($derniers_emprunts as $emprunt): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($emprunt['livre_titre']) ?></strong>
                        <br>
                        Par <?= htmlspecialchars($emprunt['etudiant_nom']) ?>
                        <span class="float-right">
                            <?= date('d/m/Y', strtotime($emprunt['date_emprunt'])) ?>
                            <?php if ($emprunt['date_retour']): ?>
                                <span class="badge badge-success">Retourné</span>
                            <?php else: ?>
                                <span class="badge badge-primary">En cours</span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3>Pénalités</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-<?= $stats['total_penalites'] > 0 ? 'warning' : 'success' ?>">
                    <h4>Total des pénalités impayées: <?= $stats['total_penalites'] ?> €</h4>
                    <?php if ($stats['total_penalites'] > 0): ?>
                        <a href="penalites.php" class="btn btn-danger">Gérer les pénalités</a>
                    <?php else: ?>
                        <p>Aucune pénalité impayée</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>