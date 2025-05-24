<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ .'/../config/database.php';


if (isLoggedIn()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM agents WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && $password=$user['password']) {
        $_SESSION['user'] = $user;
        $_SESSION['message'] = "Connexion réussie!";
        redirect('dashboard.php');
    } else {
        $error = "Email ou mot de passe incorrect";
        
    }
}

$title = "Connexion";
include '../includes/header.php';
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h2><i class="fas fa-sign-in-alt"></i> Connexion</h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Mot de passe</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 80vh;
    padding: 20px;
}

.login-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
    overflow: hidden;
}

.login-header {
    background-color: var(--primary-color);
    color: white;
    padding: 20px;
    text-align: center;
}

.login-header h2 {
    margin: 0;
    font-size: 24px;
}

.login-form {
    padding: 20px;
}

.btn-block {
    width: 100%;
}
</style>

<?php include '../includes/footer.php'; ?>