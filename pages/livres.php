<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/function.php';

checkAuth();

// Gestion des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_livre'])) {
        // Vérification si l'ISBN est vide
        if (empty($_POST['isbn'])) {
            $_SESSION['error'] = "ISBN invalide";
        } else {
            // Vérifier si l'ISBN existe déjà
            $checkStmt = $pdo->prepare("SELECT id FROM livres WHERE isbn = ?");
            $checkStmt->execute([$_POST['isbn']]);
            $existingBook = $checkStmt->fetch();
            
            if ($existingBook) {
                $_SESSION['error'] = "Un livre avec cet ISBN existe déjà";
            } else {
                $stmt = $pdo->prepare("INSERT INTO livres (isbn, titre, auteur, categorie) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['isbn'],
                    $_POST['titre'],
                    $_POST['auteur'],
                    $_POST['categorie']
                ]);
                $_SESSION['message'] = "Livre ajouté avec succès";
            }
        }
        redirect('livres.php');
    } elseif (isset($_POST['update_livre'])) {
        // Vérification si l'ISBN est vide
        if (empty($_POST['isbn'])) {
            $_SESSION['error'] = "ISBN invalide";
        } else {
            // Vérifier si l'ISBN existe déjà pour un autre livre
            $checkStmt = $pdo->prepare("SELECT id FROM livres WHERE isbn = ? AND id != ?");
            $checkStmt->execute([$_POST['isbn'], $_POST['id']]);
            $existingBook = $checkStmt->fetch();
            
            if ($existingBook) {
                $_SESSION['error'] = "Un autre livre avec cet ISBN existe déjà";
            } else {
                $stmt = $pdo->prepare("UPDATE livres SET isbn = ?, titre = ?, auteur = ?, categorie = ? WHERE id = ?");
                $stmt->execute([
                    $_POST['isbn'],
                    $_POST['titre'],
                    $_POST['auteur'],
                    $_POST['categorie'],
                    $_POST['id']
                ]);
                $_SESSION['message'] = "Livre mis à jour avec succès";
            }
        }
        redirect('livres.php');
    }
}

if (isset($_GET['delete'])) {
    $pdo->beginTransaction();
    try {
        // Vérifier si le livre est emprunté
        $emprunts = $pdo->query("SELECT COUNT(*) FROM emprunts WHERE livre_id = {$_GET['delete']} AND date_retour IS NULL")->fetchColumn();
        
        if ($emprunts > 0) {
            $_SESSION['error'] = "Impossible de supprimer: livre actuellement emprunté";
        } else {
            // Supprimer les réservations associées
            $pdo->exec("DELETE FROM reservations WHERE livre_id = {$_GET['delete']}");
            // Supprimer le livre
            $pdo->exec("DELETE FROM livres WHERE id = {$_GET['delete']}");
            $_SESSION['message'] = "Livre supprimé avec succès";
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
    }
    redirect('livres.php');
}

// Récupérer les livres
$livres = $pdo->query("
    SELECT l.*, 
           (SELECT COUNT(*) FROM emprunts e WHERE e.livre_id = l.id AND e.date_retour IS NULL) as emprunts_actifs
    FROM livres l
    ORDER BY l.titre
")->fetchAll();

// Récupérer le livre à éditer
$livre_to_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM livres WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $livre_to_edit = $stmt->fetch();
}

// Catégories disponibles
$categories = [
    'Science', 'Littérature', 'Histoire', 'Informatique', 
    'Philosophie', 'Art', 'Économie', 'Droit', 'Médecine'
];

$title = "Gestion des Livres";
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

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h3><?= isset($_GET['edit']) ? 'Modifier' : 'Ajouter' ?> un livre</h3>
    </div>


    <!-- editer un livre -->
    
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $livre_to_edit['id'] ?? '' ?>">
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>ISBN</label>
                    <input type="text" name="isbn" class="form-control" 
                           value="<?= htmlspecialchars($livre_to_edit['isbn'] ?? '') ?>" required>
                    <small class="form-text text-muted">Format ISBN-10 ou ISBN-13 valide</small>
                </div>
                <div class="form-group col-md-6">
                    <label>Titre</label>
                    <input type="text" name="titre" class="form-control" 
                           value="<?= htmlspecialchars($livre_to_edit['titre'] ?? '') ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Auteur</label>
                    <input type="text" name="auteur" class="form-control" 
                           value="<?= htmlspecialchars($livre_to_edit['auteur'] ?? '') ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Catégorie</label>
                    <select name="categorie" class="form-control" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categories as $categorie): ?>
                        <option value="<?= $categorie ?>" 
                            <?= isset($livre_to_edit) && $livre_to_edit['categorie'] === $categorie ? 'selected' : '' ?>>
                            <?= $categorie ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="<?= isset($_GET['edit']) ? 'update_livre' : 'add_livre' ?>" 
                    class="btn btn-primary">
                <i class="fas fa-save"></i> <?= isset($_GET['edit']) ? 'Mettre à jour' : 'Ajouter' ?>
            </button>
            
            <?php if (isset($_GET['edit'])): ?>
                <a href="livres.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h3>Liste des Livres</h3>
        <div class="input-group mt-2">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
            <div class="input-group-append">
                <button class="btn btn-light" type="button" id="searchButton">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="livresTable">
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Catégorie</th>
                        <th>Disponibilité</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livres as $livre): ?>
                    <tr>
                        <td><?= htmlspecialchars($livre['isbn']) ?></td>
                        <td><?= htmlspecialchars($livre['titre']) ?></td>
                        <td><?= htmlspecialchars($livre['auteur']) ?></td>
                        <td><?= htmlspecialchars($livre['categorie']) ?></td>
                        <td>
                            <?php if ($livre['emprunts_actifs'] > 0): ?>
                                <span class="badge badge-danger">Emprunté</span>
                            <?php else: ?>
                                <span class="badge badge-success">Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?edit=<?= $livre['id'] ?>" class="btn btn-sm btn-warning" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?= $livre['id'] ?>" class="btn btn-sm btn-danger" 
                               title="Supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce livre?')">
                                <i class="fas fa-trash"></i>
                            </a>
                            <a href="emprunts.php?livre_id=<?= $livre['id'] ?>" class="btn btn-sm btn-info" title="Historique">
                                <i class="fas fa-history"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Recherche en temps réel
document.getElementById('searchInput').addEventListener('input', function() {
    const searchValue = this.value.toLowerCase();
    const rows = document.querySelectorAll('#livresTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>