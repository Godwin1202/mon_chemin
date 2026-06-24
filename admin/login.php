<?php
// admin/login.php

session_start();
require_once '../php/db.php';

// Si déjà connecté en tant qu'admin, rediriger
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND role = 'admin' AND actif = 1");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && $password === $admin['mot_de_passe']) {
        $_SESSION['user_id'] = $admin['id'];
        $_SESSION['user_email'] = $admin['email'];
        $_SESSION['user_nom'] = $admin['nom'];
        $_SESSION['user_prenom'] = $admin['prenom'];
        $_SESSION['user_role'] = 'admin';
        
        header('Location: dashboard.php');
        exit();
    } else {
        $error = "Email ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Connexion</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <h1>Espace administration</h1>
                <p>Connectez-vous à votre espace d'administration</p>
            </div>
            
            <?php if ($error): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label>Adresse email</label>
                    <input type="email" name="email" required placeholder="admin@monchemin.com">
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Se connecter
                </button>
            </form>
            
            <div class="auth-footer">
                <a href="../index.php"><i class="fa-solid fa-arrow-left"></i> Retour au site</a>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>