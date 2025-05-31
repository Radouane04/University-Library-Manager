<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Gestion des emprunts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new_emprunt'])) {
        // Nouvel emprunt
        $date_retour_prevue = date('Y-m-d H:i:s', strtotime('+15 days'));
        
        $pdo->beginTransaction();
        try {
            // Vérifier que le livre est bien disponible
            $stmt = $pdo->prepare("SELECT disponible FROM livres WHERE id = ?");
            $stmt->execute([$_POST['livre_id']]);
            $livre = $stmt->fetch();
            
            if (!$livre || !$livre['disponible']) {
                throw new Exception("Ce livre n'est pas disponible pour l'emprunt");
            }

            // Créer l'emprunt
            $stmt = $pdo->prepare("INSERT INTO emprunts 
                                  (livre_id, etudiant_id, agent_id, date_retour_prevue) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $_POST['livre_id'],
                $_POST['etudiant_id'],
                $_SESSION['user']['id'],
                $date_retour_prevue
            ]);
            
            // Mettre à jour la disponibilité du livre
            $stmt = $pdo->prepare("UPDATE livres SET disponible = FALSE WHERE id = ?");
            $stmt->execute([$_POST['livre_id']]);
            
            $pdo->commit();
            $_SESSION['message'] = "Emprunt enregistré avec succès";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors de l'emprunt: " . $e->getMessage();
        }
        
        redirect('emprunts.php');
    } elseif (isset($_POST['return_emprunt'])) {
        // Retour d'un emprunt
        $pdo->beginTransaction();
        try {
            // Marquer comme retourné
            $stmt = $pdo->prepare("UPDATE emprunts SET date_retour = NOW() WHERE id = ?");
            $stmt->execute([$_POST['emprunt_id']]);
            
            // Calculer la pénalité si retard
            $emprunt = $pdo->query("SELECT * FROM emprunts WHERE id = " . $_POST['emprunt_id'])->fetch();
            $penalite = 0;
            
            if (strtotime($emprunt['date_retour_prevue']) < time()) {
                $jours_retard = floor((time() - strtotime($emprunt['date_retour_prevue'])) / (60 * 60 * 24));
                $penalite = $jours_retard * 15; // 15 DHS par jour de retard
                
                $stmt = $pdo->prepare("UPDATE emprunts SET penalite = ? WHERE id = ?");
                $stmt->execute([$penalite, $_POST['emprunt_id']]);
            }
            
            // Rendre le livre disponible
            $stmt = $pdo->prepare("UPDATE livres SET disponible = TRUE WHERE id = ?");
            $stmt->execute([$emprunt['livre_id']]);
            
            $pdo->commit();
            $_SESSION['message'] = "Retour enregistré" . ($penalite > 0 ? " (Pénalité: $penalite DHS)" : "");
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors du retour: " . $e->getMessage();
        }
        
        redirect('emprunts.php');
    }
}

// Récupérer les emprunts
$emprunts = $pdo->query("
    SELECT e.*, 
           l.titre as livre_titre, 
           l.isbn as livre_isbn,
           et.nom as etudiant_nom,
           et.matricule as etudiant_matricule,
           DATEDIFF(NOW(), e.date_retour_prevue) as jours_retard
    FROM emprunts e
    JOIN livres l ON e.livre_id = l.id
    JOIN etudiants et ON e.etudiant_id = et.id
    ORDER BY e.date_retour IS NULL DESC, e.date_emprunt DESC
")->fetchAll();

// Récupérer les livres vraiment disponibles (non empruntés)
$livres_disponibles = $pdo->query("
    SELECT l.* 
    FROM livres l
    WHERE l.disponible = TRUE 
    AND NOT EXISTS (
        SELECT 1 
        FROM emprunts e 
        WHERE e.livre_id = l.id 
        AND e.date_retour IS NULL
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

<h2>Gestion des Emprunts</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Nouvel Emprunt</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label>Livre</label>
                        <select name="livre_id" class="form-control" required>
                            <option value="">Sélectionner un livre</option>
                            <?php foreach ($livres_disponibles as $livre): ?>
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
                    <button type="submit" name="new_emprunt" class="btn btn-primary">
                        Enregistrer l'emprunt
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h3>Statistiques</h3>
            </div>
            <div class="card-body">
                <div class="stats">
                    <div class="stat-card">
                        <h4>Emprunts en cours</h4>
                        <p><?= count(array_filter($emprunts, fn($e) => $e['date_retour'] === null)) ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Retards</h4>
                        <p><?= count(array_filter($emprunts, fn($e) => $e['date_retour'] === null && $e['jours_retard'] > 0)) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Historique des Emprunts</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Étudiant</th>
                        <th>Date Emprunt</th>
                        <th>Date Retour Prévue</th>
                        <th>Date Retour</th>
                        <th>Pénalité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprunts as $emprunt): ?>
                    <tr class="<?= $emprunt['date_retour'] === null && $emprunt['jours_retard'] > 0 ? 'table-danger' : '' ?>">
                        <td><?= htmlspecialchars($emprunt['livre_titre']) ?> (<?= $emprunt['livre_isbn'] ?>)</td>
                        <td><?= htmlspecialchars($emprunt['etudiant_nom']) ?> (<?= $emprunt['etudiant_matricule'] ?>)</td>
                        <td><?= date('d/m/Y', strtotime($emprunt['date_emprunt'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($emprunt['date_retour_prevue'])) ?></td>
                        <td><?= $emprunt['date_retour'] ? date('d/m/Y', strtotime($emprunt['date_retour'])) : '-' ?></td>
                        <td><?= $emprunt['penalite'] ? $emprunt['penalite'] . ' DHS' : '-' ?></td>
                        <td>
                            <?php if ($emprunt['date_retour']): ?>
                                <span class="badge badge-success">Retourné</span>
                            <?php elseif ($emprunt['jours_retard'] > 0): ?>
                                <span class="badge badge-danger">En retard (<?= $emprunt['jours_retard'] ?> jours)</span>
                            <?php else: ?>
                                <span class="badge badge-primary">En cours</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!$emprunt['date_retour']): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="emprunt_id" value="<?= $emprunt['id'] ?>">
                                <button type="submit" name="return_emprunt" class="btn btn-sm btn-success">
                                    <i class="fas fa-undo"></i> Retour
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>