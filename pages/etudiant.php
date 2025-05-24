<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

checkAuth();

// Vérifier si l'utilisateur est connecté et est un étudiant
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
//     header('Location: login.php');
//     exit();
// }

// Connexion à la base de données


// Fonction pour récupérer les livres
function getLivres($pdo) {
    $stmt = $pdo->query("SELECT * FROM livres WHERE disponible = 1");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour réserver un livre
function reserverLivre($pdo, $livre_id, $etudiant_id) {
    // Vérifier si le livre est disponible
    $stmt = $pdo->prepare("SELECT disponible FROM livres WHERE id = ?");
    $stmt->execute([$livre_id]);
    $livre = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($livre && $livre['disponible'] == 1) {
        // Mettre à jour le statut du livre
        $stmt = $pdo->prepare("UPDATE livres SET disponible = 0 WHERE id = ?");
        $stmt->execute([$livre_id]);
        
        // Créer une réservation
        $date_reservation = date('Y-m-d H:i:s');
        $date_retour = date('Y-m-d H:i:s', strtotime('+14 days'));
        
        $stmt = $pdo->prepare("INSERT INTO reservations (livre_id, etudiant_id, date_reservation, date_retour) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$livre_id, $etudiant_id, $date_reservation, $date_retour]);
    }
    return false;
}

// Traitement de la réservation
if (isset($_POST['reserver'])) {
    $livre_id = $_POST['livre_id'];
    $etudiant_id = $_SESSION['user_id'];
    
    if (reserverLivre($pdo, $livre_id, $etudiant_id)) {
        $message_success = "Livre réservé avec succès!";
    } else {
        $message_error = "Erreur lors de la réservation ou livre non disponible.";
    }
}

// Récupérer la liste des livres
$livres = getLivres($pdo);

// Récupérer les réservations de l'étudiant
$reservations = [];
$stmt = $pdo->prepare("SELECT l.*, r.date_retour 
                      FROM livres l 
                      JOIN reservations r ON l.id = r.livre_id 
                      WHERE r.etudiant_id = ? AND r.date_retour > NOW()");
$stmt->execute([$_SESSION['user_id']]);
$reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Étudiant - Bibliothèque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1, h2 {
            color: #333;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: #fff;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p>Espace étudiant - Gestion des livres</p>
        
        <?php if (isset($message_success)): ?>
            <div class="message success"><?php echo $message_success; ?></div>
        <?php endif; ?>
        
        <?php if (isset($message_error)): ?>
            <div class="message error"><?php echo $message_error; ?></div>
        <?php endif; ?>
        
        <h2>Mes réservations en cours</h2>
        <?php if (count($reservations) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Emplacement</th>
                        <th>Date de retour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $reservation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reservation['titre']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['auteur']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['emplacement']); ?></td>
                            <td><?php echo htmlspecialchars($reservation['date_retour']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Vous n'avez aucune réservation en cours.</p>
        <?php endif; ?>
        
        <h2>Livres disponibles</h2>
        <?php if (count($livres) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Auteur</th>
                        <th>Année</th>
                        <th>Emplacement</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($livres as $livre): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($livre['titre']); ?></td>
                            <td><?php echo htmlspecialchars($livre['auteur']); ?></td>
                            <td><?php echo htmlspecialchars($livre['annee']); ?></td>
                            <td><?php echo htmlspecialchars($livre['emplacement']); ?></td>
                            <td><?php echo htmlspecialchars($livre['description']); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="livre_id" value="<?php echo $livre['id']; ?>">
                                    <button type="submit" name="reserver" class="btn">Réserver</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Aucun livre disponible pour le moment.</p>
        <?php endif; ?>
        
        <p><a href="logout.php" class="btn btn-danger">Déconnexion</a></p>
    </div>
</body>
</html>