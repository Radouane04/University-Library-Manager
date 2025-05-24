<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Gestion des opérations CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_etudiant'])) {
        $stmt = $pdo->prepare("INSERT INTO etudiants (matricule, nom, email, telephone) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['matricule'],
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone']
        ]);
        $_SESSION['message'] = "Étudiant ajouté avec succès";
        redirect('etudiants.php');
    } elseif (isset($_POST['update_etudiant'])) {
        $stmt = $pdo->prepare("UPDATE etudiants SET matricule = ?, nom = ?, email = ?, telephone = ? WHERE id = ?");
        $stmt->execute([
            $_POST['matricule'],
            $_POST['nom'],
            $_POST['email'],
            $_POST['telephone'],
            $_POST['id']
        ]);
        $_SESSION['message'] = "Étudiant mis à jour avec succès";
        redirect('etudiants.php');
    }
}

if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $_SESSION['message'] = "Étudiant supprimé avec succès";
    redirect('etudiants.php');
}

// Récupérer tous les étudiants
$etudiants = $pdo->query("SELECT * FROM etudiants ORDER BY nom")->fetchAll();

// Récupérer l'étudiant à modifier
$etudiant_to_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $etudiant_to_edit = $stmt->fetch();
}

include '../includes/header.php';
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['message'] ?>
        <?php unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<h2>Gestion des Étudiants</h2>

<div class="card mb-4">
    <div class="card-header">
        <h3><?= isset($_GET['edit']) ? 'Modifier' : 'Ajouter' ?> un étudiant</h3>
    </div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id" value="<?= $etudiant_to_edit['id'] ?? '' ?>">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Matricule</label>
                    <input type="text" name="matricule" class="form-control" 
                           value="<?= htmlspecialchars($etudiant_to_edit['matricule'] ?? '') ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Nom complet</label>
                    <input type="text" name="nom" class="form-control" 
                           value="<?= htmlspecialchars($etudiant_to_edit['nom'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" 
                           value="<?= htmlspecialchars($etudiant_to_edit['email'] ?? '') ?>" required>
                </div>
                <div class="form-group col-md-6">
                    <label>Téléphone</label>
                    <input type="text" name="telephone" class="form-control" 
                           value="<?= htmlspecialchars($etudiant_to_edit['telephone'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" name="<?= isset($_GET['edit']) ? 'update_etudiant' : 'add_etudiant' ?>" 
                    class="btn btn-primary">
                <?= isset($_GET['edit']) ? 'Mettre à jour' : 'Ajouter' ?>
            </button>
            <?php if (isset($_GET['edit'])): ?>
                <a href="etudiants.php" class="btn btn-secondary">Annuler</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Liste des Étudiants</h3>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Rechercher...">
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="etudiantsTable">
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= htmlspecialchars($etudiant['matricule']) ?></td>
                        <td><?= htmlspecialchars($etudiant['nom']) ?></td>
                        <td><?= htmlspecialchars($etudiant['email']) ?></td>
                        <td><?= htmlspecialchars($etudiant['telephone']) ?></td>
                        <td>
                            <a href="?edit=<?= $etudiant['id'] ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?= $etudiant['id'] ?>" class="btn btn-sm btn-danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet étudiant?')">
                                <i class="fas fa-trash"></i>
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
    const rows = document.querySelectorAll('#etudiantsTable tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchValue) ? '' : 'none';
    });
});
</script>

<?php include '../includes/footer.php'; ?>