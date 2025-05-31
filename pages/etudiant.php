<?php
require_once '../config/database.php';

// Authentication check
$isLoggedIn = isset($_SESSION['user']);
$username = $isLoggedIn ? $_SESSION['user']['nom'] : '';

// Search parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$availability = $_GET['availability'] ?? '';
$sort = $_GET['sort'] ?? 'titre';

// Secure sort options
$sortOptions = [
    'titre' => 'titre ASC',
    'auteur' => 'auteur ASC',
    'recent' => 'created_at DESC'
];
$sortOrder = $sortOptions[$sort] ?? $sortOptions['titre'];

// Build SQL query
$sql = "SELECT * FROM livres WHERE 1=1";
$params = [];

// Search handling - uniquement par titre maintenant
if (!empty($search)) {
    $searchTerms = array_filter(array_map('trim', explode(' ', $search)));
    $searchConditions = [];
    foreach ($searchTerms as $i => $term) {
        if (!empty($term)) {
            $param = ":search$i";
            $searchConditions[] = "titre LIKE $param";
            $params[$param] = "%$term%";
        }
    }
    if (!empty($searchConditions)) {
        $sql .= " AND (" . implode(' AND ', $searchConditions) . ")";
    }
}

if (!empty($category)) {
    $sql .= " AND categorie = :category";
    $params[':category'] = $category;
}

if ($availability !== '') {
    $sql .= " AND disponible = :disponible";
    $params[':disponible'] = (int)$availability;
}

$sql .= " ORDER BY " . $sortOrder;

// Traitement de la réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
    header('Content-Type: application/json');
    
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Vous devez être connecté pour réserver un livre'
        ]);
        exit;
    }

    $bookId = (int)$_POST['book_id'];
    $userId = (int)$_SESSION['user']['id'];

    try {
        $pdo->beginTransaction();

        // Vérifier que le livre existe et est disponible
        $stmt = $pdo->prepare("SELECT id, titre FROM livres WHERE id = ? AND disponible = 1 FOR UPDATE");
        $stmt->execute([$bookId]);
        $book = $stmt->fetch();
        
        if (!$book) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Le livre n\'existe pas ou n\'est pas disponible'
            ]);
            exit;
        }

        // Vérifier si l'utilisateur n'a pas déjà réservé ce livre
        $stmt = $pdo->prepare("SELECT id FROM reservations WHERE livre_id = ? AND etudiant_id = ? AND statut IN ('en_attente', 'active')");
        $stmt->execute([$bookId, $userId]);
        
        if ($stmt->rowCount() > 0) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Vous avez déjà une réservation en cours pour ce livre'
            ]);
            exit;
        }

        // Vérifier le nombre maximal de réservations actives (3 max)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE etudiant_id = ? AND statut = 'active'");
        $stmt->execute([$userId]);
        $activeReservations = (int)$stmt->fetchColumn();
        
        if ($activeReservations >= 3) {
            $pdo->rollBack();
            echo json_encode([
                'success' => false,
                'message' => 'Vous avez déjà 3 réservations actives. Maximum autorisé atteint.'
            ]);
            exit;
        }

        // Mettre à jour la disponibilité du livre
        $stmt = $pdo->prepare("UPDATE livres SET disponible = 0 WHERE id = ?");
        $stmt->execute([$bookId]);

        // Créer la réservation
        $dateReservation = date('Y-m-d H:i:s');
        $dateRetour = date('Y-m-d H:i:s', strtotime('+14 days'));

        $stmt = $pdo->prepare("INSERT INTO reservations (livre_id, etudiant_id, date_reservation, date_retour_prevue, statut) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$bookId, $userId, $dateReservation, $dateRetour]);

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Réservation effectuée avec succès! Le livre "'.$book['titre'].'" a été réservé.',
            'return_date' => date('d/m/Y', strtotime($dateRetour))
        ]);
        exit;

    } catch (PDOException $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur technique est survenue: ' . $e->getMessage()
        ]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Bibliothèque Universitaire</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #1a252f;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f9f9f9;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo i {
            font-size: 2.5rem;
            color: white;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .logo-text span {
            color: var(--primary-color);
        }

        nav {
            display: flex;
            align-items: center;
            gap: 2rem;
            width: 100%;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 1.5rem;
            margin-right: auto;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary-color);
        }

        .nav-links a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-color);
            transition: width 0.3s;
        }

        .nav-links a:hover::after {
            width: 100%;
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-left: auto;
        }

        .user-greeting {
            color: white;
            font-weight: 500;
            white-space: nowrap;
            order: 1;
        }

        .logout-btn, .login-btn {
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            order: 2;
        }

        .logout-btn {
            background-color: var(--accent-color);
            color: white;
            border: 1px solid var(--accent-color);
        }

        .logout-btn:hover {
            background-color: #c0392b;
            border-color: #c0392b;
        }

        .login-btn {
            background-color: var(--primary-color);
            color: white;
            border: 1px solid var(--primary-color);
        }

        .login-btn:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }

        main {
            flex: 1;
            padding: 2rem 0;
        }

        .hero {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        #catalogue {
            scroll-margin-top: 80px;
        }

        .search-container {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 0 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .search-form input {
            flex: 1;
            padding: 0.8rem 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .search-form button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-form button:hover {
            background-color: #2980b9;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .filter-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .catalogue {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .book-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            color: #999;
            flex-direction: column;
        }

        .book-cover img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }

        .book-cover .placeholder-icon {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .book-cover .placeholder-text {
            text-align: center;
            font-size: 0.8rem;
            padding: 0 0.5rem;
            word-break: break-word;
        }

        .book-info {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .book-title {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            color: var(--secondary-color);
        }

        .book-author {
            color: #95a5a6;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }

        .book-isbn {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 1rem;
        }

        .book-category {
            display: inline-block;
            background-color: #e0e0e0;
            padding: 0.3rem 0.6rem;
            border-radius: 50px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .book-location {
            margin-top: auto;
            font-size: 0.9rem;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .book-location i {
            color: var(--primary-color);
        }

        .book-availability {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .available {
            color: #27ae60;
            font-weight: 600;
        }

        .unavailable {
            color: var(--accent-color);
            font-weight: 600;
        }

        .reserve-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 0.9rem;
        }

        .reserve-btn:hover {
            background-color: #2980b9;
        }

        .reserve-btn:disabled {
            background-color: #95a5a6;
            cursor: not-allowed;
        }

        .no-results {
            grid-column: 1/-1;
            text-align: center;
            padding: 2rem;
            color: #95a5a6;
        }

        .error {
            grid-column: 1/-1;
            text-align: center;
            padding: 2rem;
            color: var(--accent-color);
        }

        footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 3rem 0 0;
        }

        #contact {
            scroll-margin-top: 80px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .footer-section {
            margin-bottom: 2rem;
        }

        .footer-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #fff;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: #95a5a6;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        .contact-info {
            margin-top: 1rem;
        }

        .contact-info p {
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-icon {
            color: white;
            background-color: var(--primary-color);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s;
        }

        .social-icon:hover {
            background-color: var(--secondary-color);
        }

        .footer-bottom {
            background-color: #1a252f;
            padding: 1.5rem 2rem;
            text-align: center;
        }

        .footer-bottom p {
            margin-bottom: 0.5rem;
        }

        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }

            nav {
                width: 100%;
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
                margin-right: 0;
            }

            .user-section {
                margin: 1rem 0 0;
                justify-content: center;
                width: 100%;
                flex-direction: column-reverse;
                gap: 1rem;
            }

            .search-form {
                flex-direction: column;
            }

            .filter-group {
                min-width: 100%;
            }

            .user-greeting {
                display: none;
            }
            
            .logout-btn span, .login-btn span {
                display: none;
            }
            
            .logout-btn, .login-btn {
                padding: 0.5rem;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-book-open"></i>
                <div class="logo-text">Bibliothèque <span>Universitaire</span></div>
            </div>
            
            <nav>
                <ul class="nav-links">
                    <li><a href="#" onclick="return scrollToTop()">Accueil</a></li>
                    <li><a href="#catalogue">Catalogue</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="reservations.php">Réservations</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="user-section">
                    <?php if ($isLoggedIn): ?>
                        <a href="logout.php" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> <span>Déconnexion</span>
                        </a>
                        <span class="user-greeting">Bonjour, <?= htmlspecialchars($username) ?></span>
                    <?php else: ?>
                        <a href="login.php" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> <span>Connexion</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>Catalogue des Livres</h1>
            <p>Recherchez et réservez les livres disponibles dans notre bibliothèque</p>
        </section>

        <div id="catalogue" class="search-container">
            <form method="GET" action="" style="display: contents">
                <div class="search-form">
                    <input type="text" name="search" placeholder="Rechercher par titre..." 
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
                </div>
                
                <div class="filters">
                    <div class="filter-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category" onchange="this.form.submit()">
                            <option value="">Toutes catégories</option>
                            <?php
                            $categories = $pdo->query("SELECT DISTINCT categorie FROM livres ORDER BY categorie");
                            while ($cat = $categories->fetch()):
                            ?>
                            <option value="<?= htmlspecialchars($cat['categorie']) ?>" 
                                <?= $category === $cat['categorie'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['categorie']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="availability">Disponibilité</label>
                        <select id="availability" name="availability" onchange="this.form.submit()">
                            <option value="" <?= $availability === '' ? 'selected' : '' ?>>Tous</option>
                            <option value="1" <?= $availability === '1' ? 'selected' : '' ?>>Disponibles</option>
                            <option value="0" <?= $availability === '0' ? 'selected' : '' ?>>Indisponibles</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sort">Trier par</label>
                        <select id="sort" name="sort" onchange="this.form.submit()">
                            <option value="titre" <?= $sort === 'titre' ? 'selected' : '' ?>>Titre (A-Z)</option>
                            <option value="auteur" <?= $sort === 'auteur' ? 'selected' : '' ?>>Auteur (A-Z)</option>
                            <option value="recent" <?= $sort === 'recent' ? 'selected' : '' ?>>Plus récents</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <div class="container">
            <div class="catalogue">
                <?php
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        while ($book = $stmt->fetch()):
                ?>
                            <div class="book-card">
                                <div class="book-cover">
                                    <?php if(!empty($book['cover']) && filter_var($book['cover'], FILTER_VALIDATE_URL)): ?>
                                        <img src="<?= htmlspecialchars($book['cover']) ?>" alt="<?= htmlspecialchars($book['titre']) ?>">
                                    <?php else: ?>
                                        <i class="fas fa-book placeholder-icon"></i>
                                        <div class="placeholder-text"><?= htmlspecialchars(substr($book['titre'], 0, 30)) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="book-info">
                                    <h3 class="book-title"><?= htmlspecialchars($book['titre']) ?></h3>
                                    <p class="book-author"><?= htmlspecialchars($book['auteur']) ?></p>
                                    <p class="book-isbn">ISBN: <?= htmlspecialchars($book['isbn']) ?></p>
                                    <span class="book-category"><?= htmlspecialchars($book['categorie']) ?></span>
                                    <p class="book-location"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($book['emplacement']) ?></p>
                                    <div class="book-availability">
                                        <span class="<?= $book['disponible'] ? 'available' : 'unavailable' ?>">
                                            <?= $book['disponible'] ? 'Disponible' : 'Indisponible' ?>
                                        </span>
                                        <?php if ($book['disponible']): ?>
                                            <button class="reserve-btn" 
                                                    data-book-id="<?= $book['id'] ?>"
                                                    onclick="reserveBook(this)">
                                                Réserver
                                            </button>
                                        <?php else: ?>
                                            <button class="reserve-btn" disabled>Réservé</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                <?php 
                        endwhile;
                    } else {
                        echo '<p class="no-results">Aucun livre trouvé correspondant à vos critères de recherche.</p>';
                    }
                } catch (PDOException $e) {
                    echo '<p class="error">Erreur de connexion à la base de données: '.htmlspecialchars($e->getMessage()).'</p>';
                }
                ?>
            </div>
        </div>
    </main>

    <footer id="contact">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">Bibliothèque Universitaire</h3>
                <p>Votre portail vers la connaissance et la découverte</p>
                <div class="contact-info">
                    <p><i class="fas fa-map-marker-alt"></i> 123 Avenue de l'Université, 75000 Paris</p>
                    <p><i class="fas fa-phone"></i> +33 1 23 45 67 89</p>
                    <p><i class="fas fa-envelope"></i> contact@biblio-univ.fr</p>
                    <p><i class="fas fa-clock"></i> Lundi-Vendredi: 9h-19h | Samedi: 10h-17h</p>
                </div>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">Navigation</h3>
                <ul class="footer-links">
                    <li><a href="#" onclick="return scrollToTop()">Accueil</a></li>
                    <li><a href="#catalogue">Catalogue</a></li>
                    <li><a href="#contact">Contact</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="reservations.php">Mes réservations</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="footer-section">
                <h3 class="footer-title">Réseaux sociaux</h3>
                <div class="social-links">
                    <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <div class="newsletter" style="margin-top: 1rem;">
                    <p>Abonnez-vous à notre newsletter</p>
                    <form>
                        <input type="email" placeholder="Votre email" style="padding: 0.5rem; width: 100%; margin-bottom: 0.5rem;">
                        <button type="submit" style="padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: 4px;">S'abonner</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Bibliothèque Universitaire. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            return false;
        }

        function reserveBook(button) {
            const bookId = button.getAttribute('data-book-id');
            const bookCard = button.closest('.book-card');
            const bookTitle = bookCard.querySelector('.book-title').textContent;
            
            if (confirm(`Voulez-vous réserver le livre "${bookTitle}" ?`)) {
                button.disabled = true;
                button.textContent = 'En cours...';
                
                // Créer un formulaire dynamique pour envoyer les données
                const formData = new FormData();
                formData.append('book_id', bookId);
                formData.append('reserver', 'true');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.textContent = 'Réservé';
                        const availabilitySpan = bookCard.querySelector('.book-availability span');
                        availabilitySpan.textContent = 'Indisponible';
                        availabilitySpan.className = 'unavailable';
                        alert(data.message);
                    } else {
                        throw new Error(data.message || 'Erreur inconnue');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.textContent = 'Réserver';
                    alert('Erreur lors de la réservation: ' + error.message);
                });
            }
        }

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>