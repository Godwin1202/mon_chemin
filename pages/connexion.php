<?php
// pages/connexion.php

session_start();
require_once '../php/db.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}
// Définir le préfixe pour les chemins
$page_prefix = '../';
// Inclure le header
include '../includes/header.php';

$error = '';
$success = '';

if (isset($_SESSION['inscription_success'])) {
    $success = $_SESSION['inscription_success'];
    unset($_SESSION['inscription_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && $password === $user['mot_de_passe']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_role'] = $user['role'];
            
            $stmt = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            if ($user['role'] === 'admin') {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit();
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fa-regular fa-circle-user"></i>
                </div>
                <h1>Connexion</h1>
                <p>Accédez à votre espace personnel</p>
            </div>
            
            <?php if ($error): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="auth-alert auth-alert-success">
                    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label>Adresse email</label>
                    <input type="email" name="email" required placeholder="exemple@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required placeholder="••••••••">
                </div>
                
                <div class="auth-password-link">
                    <a href="mot-de-passe-oublie.php">Mot de passe oublié ?</a>
                </div>
                
                <button type="submit" class="auth-btn">
                     Se connecter
                </button>
            </form>
            
            <div class="auth-footer">
                Pas encore de compte ? <a href="inscription.php">Créer un compte</a>
            </div>
            
        </div>
    </div>
</div>

</body>
</html>