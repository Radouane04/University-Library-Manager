<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Footer Layout Moderne</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: #f9f9f9;
    }

    main {
      flex: 1;
      padding: 2rem;
    }

    footer {
      background-color: #34495e;
      color: #ffffff;
      padding: 3rem 1rem;
    }

    .footer-container {
      display: flex;
      flex-wrap: wrap;
      max-width: 1200px;
      margin: 0 auto;
      gap: 2rem;
      justify-content: space-between;
    }

    .footer-column {
      flex: 1 1 200px;
    }

    .footer-column h3 {
      margin-bottom: 1rem;
      font-size: 1.2rem;
      border-bottom: 1px solid #555;
      padding-bottom: 0.5rem;
    }

    .footer-column p,
    .footer-column a {
      color: #ccc;
      font-size: 0.95rem;
      text-decoration: none;
      display: block;
      margin-bottom: 0.5rem;
      transition: color 0.3s;
    }

    .footer-column a:hover {
      color: #00bcd4;
    }

    .social-icons {
      display: flex;
      gap: 1rem;
      margin-top: 1rem;
    }

    .social-icons a {
      color: #ccc;
      font-size: 1.2rem;
      transition: color 0.3s;
    }

    .social-icons a:hover {
      color: #00bcd4;
    }

    .footer-bottom {
      text-align: center;
      margin-top: 2rem;
      font-size: 0.85rem;
      color: #aaa;
    }
  </style>
</head>
<body>

  <footer>
    <div class="footer-container">

      <div class="footer-column">
        <h3>Bibliothèque</h3>
        <p>Accédez à un large choix de livres, revues et ressources numériques.</p>
      </div>

      <div class="footer-column">
        <h3>Liens utiles</h3>
        <a href="#">Accueil</a>
        <a href="#">Catalogue</a>
        <a href="#">Services</a>
        <a href="#">Espace étudiant</a>
      </div>

      <div class="footer-column">
        <h3>Contact</h3>
        <p>Université X, Marrakech</p>
        <p>Email : contact@biblio.univ.ma</p>
        <p>Tél : +212 5 24 00 00 00</p>
      </div>

      <div class="footer-column">
        <h3>Suivez-nous</h3>
        <div class="social-icons">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-x-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>

    </div>

    <div class="footer-bottom">
      &copy; <?= date('Y') ?> Bibliothèque Universitaire. Tous droits réservés.
    </div>
  </footer>

</body>
</html>
