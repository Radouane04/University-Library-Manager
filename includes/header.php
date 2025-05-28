<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bibliothèque Universitaire</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Styles pour la sidebar */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: -250px;
            background-color: #2c3e50;
            overflow-x: hidden;
            transition: 0.5s;
            padding-top: 20px;
            color: white;
        }

        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 16px;
            color: #ecf0f1;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
            color: #f39c12;
        }

        .sidebar .closebtn {
            position: absolute;
            top: 0;
            right: 15px;
            font-size: 30px;
            margin-left: 50px;
        }

        .side-header {
            text-align: center;
            padding: 20px 0;
        }

        .side-header h5 {
            margin-top: 10px;
            color: #ecf0f1;
        }

        .openbtn {
            font-size: 20px;
            cursor: pointer;
            background-color: #2c3e50;
            color: white;
            padding: 10px 15px;
            border: none;
            position: fixed;
            left: 10px;
            top: 10px;
            z-index: 2;
        }

        .openbtn:hover {
            background-color: #34495e;
        }

        #main {
            transition: margin-left .5s;
        }

        /* Ajustements pour le contenu principal */
        main.container {
            margin-left: 0;
            padding: 20px;
            transition: margin-left .5s;
        }

        .sidebar-open main.container {
            margin-left: 250px;
        }

        /* Style pour le header */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #2c3e50;
            color: white;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo i {
            font-size: 24px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            color: white;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <button class="openbtn" onclick="openNav()"><i class="fas fa-bars"></i></button>

    <div class="sidebar" id="mySidebar">
        <div class="side-header">
            <i class="fas fa-book-open" style="font-size: 60px; color:rgb(255, 255, 255);"></i>
            <h5>Bibliothèque Universitaire</h5>
        </div>
        <hr style="border:1px solid; background-color:#8a7b6d; border-color:#3B3131;">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">×</a>
        <a href="accueil.php"><i class="fas fa-home"></i> Accueil</a>
        <a href="livres.php"><i class="fas fa-book"></i> Livres</a>
        <a href="emprunts.php"><i class="fas fa-exchange-alt"></i> Emprunts</a>
        <a href="etudiants.php"><i class="fas fa-users"></i> Étudiants</a>
        <a href="reservations.php"><i class="fas fa-calendar-check"></i> Réservations</a>
        <a href="penalites.php"><i class="fas fa-exclamation-triangle"></i> Pénalités</a>
    </div>

    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-book-open"></i>
                <a href="accueil.php" class="logo-link" style="text-decoration: none;">
                    <h1 style="color: white; margin: 0;">Bibliothèque Universitaire</h1>
                </a>
            </div>
            <?php if (isLoggedIn()): ?>
                <div class="user-info">
                    <span>Bonjour, <?= htmlspecialchars($_SESSION['user']['nom']) ?></span>
                    <a href="logout.php" class="logout-btn" title="Se déconnecter">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container" id="mainContent">
        <!-- Le contenu de votre page ira ici -->
    </main>

    <script>
        function openNav() {
            document.getElementById("mySidebar").style.left = "0";
            document.getElementById("mainContent").classList.add("sidebar-open");
        }

        function closeNav() {
            document.getElementById("mySidebar").style.left = "-250px";
            document.getElementById("mainContent").classList.remove("sidebar-open");
        }
    </script>
</body>
</html>