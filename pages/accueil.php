<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/function.php';
checkAuth();

$stats = getStats();
$derniers_livres = $pdo->query("SELECT * FROM livres ORDER BY id DESC LIMIT 5")->fetchAll();
$emprunts_en_retard = $pdo->query("
    SELECT e.*, l.titre as livre_titre, et.nom as etudiant_nom
    FROM emprunts e
    JOIN livres l ON e.livre_id = l.id
    JOIN etudiants et ON e.etudiant_id = et.id
    WHERE e.date_retour IS NULL AND e.date_retour_prevue < NOW()
    ORDER BY e.date_retour_prevue ASC
    LIMIT 5
")->fetchAll();

$title = "Accueil";
include '../includes/header.php';
?>

<div class="row">
    <div class="col-md-3 mb-4">
        <div class="stat-card bg-info">
            <h3>Livres</h3>
            <p><?= $stats['total_livres'] ?></p>
            <a href="livres.php" class="text-white">Voir détails <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card bg-success">
            <h3>Étudiants</h3>
            <p><?= $stats['total_etudiants'] ?></p>
            <a href="etudiants.php" class="text-white">Voir détails <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card bg-warning">
            <h3>Emprunts</h3>
            <p><?= $stats['emprunts_actifs'] ?></p>
            <a href="emprunts.php" class="text-white">Voir détails <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="stat-card bg-danger">
            <h3>Retards</h3>
            <p><?= count($emprunts_en_retard) ?></p>
            <a href="penalites.php" class="text-white">Voir détails <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3>Derniers livres ajoutés</h3>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($derniers_livres as $livre): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?= htmlspecialchars($livre['titre']) ?></strong>
                            <div class="text-muted small"><?= $livre['isbn'] ?> - <?= $livre['categorie'] ?></div>
                        </div>
                        <span class="badge <?= $livre['disponible'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $livre['disponible'] ? 'Disponible' : 'Emprunté' ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <div class="text-center mt-3">
                    <a href="livres.php" class="btn btn-sm btn-info">Voir tous les livres</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3>Emprunts en retard</h3>
            </div>
            <div class="card-body">
                <?php if (empty($emprunts_en_retard)): ?>
                    <div class="alert alert-success">
                        Aucun emprunt en retard actuellement.
                    </div>
                <?php else: ?>
                    <ul class="list-group">
                        <?php foreach ($emprunts_en_retard as $emprunt): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($emprunt['livre_titre']) ?></strong>
                                    <div class="text-muted small">
                                        Par <?= htmlspecialchars($emprunt['etudiant_nom']) ?>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-danger">
                                        <?= formatDate($emprunt['date_retour_prevue']) ?>
                                    </div>
                                    <span class="badge badge-danger">
                                        <?= floor((time() - strtotime($emprunt['date_retour_prevue'])) / (60*60*24)) ?> jours
                                    </span>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="text-center mt-3">
                        <a href="penalites.php" class="btn btn-sm btn-danger">Gérer les retards</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>