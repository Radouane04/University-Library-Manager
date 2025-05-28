<?php
require_once '../config/database.php';
 // Utilise votre fichier de configuration existant

// Traitement des paramètres de recherche
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$availability = isset($_GET['availability']) ? (int)$_GET['availability'] : null;
$sort = $_GET['sort'] ?? 'titre';

// Options de tri
$sortOptions = [
    'titre' => 'titre ASC',
    'auteur' => 'auteur ASC',
    'recent' => 'created_at DESC'
];
$sortOrder = $sortOptions[$sort] ?? $sortOptions['titre'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue - Bibliothèque Universitaire</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #1a252f;
            --gray-color: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            line-height: 1.6;
            color: var(--dark-color);
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        /* Header Styles */
        header {
            background-color: var(--secondary-color);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
        
        .logo img {
            height: 50px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .logo-text span {
            color: var(--primary-color);
        }
        
        nav ul {
            display: flex;
            list-style: none;
            gap: 2rem;
        }
        
        nav a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 0;
            position: relative;
            transition: color 0.3s;
        }
        
        nav a:hover {
            color: var(--primary-color);
        }
        
        nav a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--primary-color);
            transition: width 0.3s;
        }
        
        nav a:hover::after {
            width: 100%;
        }
        
        /* Main Content */
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
        }
        
        .book-cover img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
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
            color: var(--gray-color);
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
            background-color: var(--gray-color);
            cursor: not-allowed;
        }
        
        .no-results {
            grid-column: 1/-1;
            text-align: center;
            padding: 2rem;
            color: var(--gray-color);
        }
        
        .error {
            grid-column: 1/-1;
            text-align: center;
            padding: 2rem;
            color: var(--accent-color);
        }
        
        /* Footer Styles */
        footer {
            background-color: var(--dark-color);
            color: var(--light-color);
            padding: 3rem 0 0;
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
            color: var(--gray-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-links a:hover {
            color: var(--primary-color);
        }
        
        .footer-bottom {
            background-color: #1a252f;
            padding: 1.5rem 2rem;
            text-align: center;
        }
        
        .footer-bottom p {
            margin-bottom: 0.5rem;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            nav ul {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .filter-group {
                min-width: 100%;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <!-- <i class="fas fa-book-open"></i> -->
            <i class="fas fa-book-open" style="font-size: 60px; color:rgb(255, 255, 255);"></i>

                <div class="logo-text">Bibliothèque <span>Universitaire</span></div>
            </div>
            <nav>
                <ul>
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#" class="active">Catalogue</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="reservations.php">Réservations</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>Catalogue des Livres</h1>
            <p>Recherchez et réservez les livres disponibles dans notre bibliothèque</p>
        </section>

        <div class="search-container">
            <form class="search-form" method="GET" action="">
                <input type="text" name="search" placeholder="Rechercher par titre, auteur, ISBN..." 
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit"><i class="fas fa-search"></i> Rechercher</button>
            </form>
            
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
                        <option value="">Tous</option>
                        <option value="1" <?= $availability === 1 ? 'selected' : '' ?>>Disponibles</option>
                        <option value="0" <?= $availability === 0 ? 'selected' : '' ?>>Indisponibles</option>
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
        </div>

        <div class="container">
            <div class="catalogue">
                <?php
                // Construction de la requête
                $sql = "SELECT * FROM livres WHERE 1=1";
                $params = [];
                
                if (!empty($search)) {
                    $sql .= " AND (titre LIKE :search OR auteur LIKE :search OR isbn LIKE :search)";
                    $params[':search'] = "%$search%";
                }
                
                if (!empty($category)) {
                    $sql .= " AND categorie = :category";
                    $params[':category'] = $category;
                }
                
                if ($availability !== null) {
                    $sql .= " AND disponible = :disponible";
                    $params[':disponible'] = $availability;
                }
                
                $sql .= " ORDER BY $sortOrder";
                
                try {
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    if ($stmt->rowCount() > 0) {
                        while ($book = $stmt->fetch()):
                            $cover = !empty($book['cover']) ? $book['cover'] : 
                                    'https://via.placeholder.com/150x200?text='.urlencode($book['titre']);
                ?>
                            <div class="book-card">
                                <div class="book-cover">
                                    <img src="<?= htmlspecialchars($cover) ?>" alt="<?= htmlspecialchars($book['titre']) ?>">
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

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="footer-title">Bibliothèque Universitaire</h3>
                <p>Votre portail vers la connaissance et la découverte</p>
                <address>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Avenue de l'Université</p>
                    <p><i class="fas fa-phone"></i> +33 1 23 45 67 89</p>
                    <p><i class="fas fa-envelope"></i> contact@biblio-univ.fr</p>
                </address>
            </div>
            
            <div class="footer-section">
                <h3 class="footer-title">Liens rapides</h3>
                <ul class="footer-links">
                    <li><a href="#">Catalogue en ligne</a></li>
                    <li><a href="#">Horaires d'ouverture</a></li>
                    <li><a href="#">Demande de document</a></li>
                    <li><a href="#">Prêt entre bibliothèques</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Bibliothèque Universitaire. Tous droits réservés.</p>
        </div>
    </footer>

    <script>
        // Fonction de réservation
        function reserveBook(button) {
            const bookId = button.getAttribute('data-book-id');
            const bookTitle = button.closest('.book-card').querySelector('.book-title').textContent;
            
            if (confirm(`Voulez-vous réserver le livre "${bookTitle}" ?`)) {
                button.disabled = true;
                button.textContent = 'En cours...';
                
                // Envoi AJAX
                fetch('reserver.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `book_id=${bookId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        button.textContent = 'Réservé';
                        button.closest('.book-availability').querySelector('span').textContent = 'Indisponible';
                        button.closest('.book-availability').querySelector('span').className = 'unavailable';
                        alert(data.message);
                    } else {
                        button.disabled = false;
                        button.textContent = 'Réserver';
                        alert('Erreur: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.disabled = false;
                    button.textContent = 'Réserver';
                    alert('Erreur lors de la réservation');
                });
            }
        }
    </script>
</body>
</html>