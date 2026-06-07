<?php
// pages/connexion.php

session_start();
require_once '../php/db.php';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}

$error = '';
$success = '';

// Vérifier si un message de succès d'inscription existe
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
        // Chercher l'utilisateur par email
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Vérifier le mot de passe (en production, utiliser password_verify)
            if ($password === $user['mot_de_passe']) {
                // Connexion réussie
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_role'] = $user['role'];
                
                // Mettre à jour la dernière connexion
                $stmt = $pdo->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);
                
                // Journaliser la connexion
                $stmt = $pdo->prepare("INSERT INTO logs (utilisateur_id, action, ip_address) VALUES (?, 'connexion', ?)");
                $stmt->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);
                
                // Redirection selon le rôle
                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: ../index.php');
                }
                exit();
            } else {
                $error = "Mot de passe incorrect.";
            }
        } else {
            $error = "Aucun compte trouvé avec cet email.";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .auth-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f7 100%);
        }
        
        .auth-card {
            background: white;
            border-radius: 32px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 32px;
        }
        
        .auth-header i {
            font-size: 48px;
            color: #2563eb;
            margin-bottom: 16px;
        }
        
        .auth-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }
        
        .auth-header p {
            color: #6b7280;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 24px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #2563eb;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 15px;
            transition: 0.2s;
            font-family: inherit;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-auth {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            margin-top: 8px;
        }
        
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #e5e7eb;
        }
        
        .auth-footer a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .error-message {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .success-message {
            background: #d1fae5;
            color: #059669;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }
        
        .password-link {
            text-align: right;
            margin-top: -16px;
            margin-bottom: 16px;
        }
        
        .password-link a {
            font-size: 12px;
            color: #6b7280;
            text-decoration: none;
        }
        
        .password-link a:hover {
            color: #2563eb;
        }
        
        @media (max-width: 480px) {
            .auth-card {
                padding: 32px 24px;
            }
            .auth-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="container header-content">
        <div class="logo">
            <a href="../index.php" style="display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit;">
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <h2>Mon Chemin</h2>
                    <p>Aide à l'orientation scolaire</p>
                </div>
            </a>
        </div>
        <nav class="navbar">
            <a href="../index.php">Accueil</a>
            <a href="quiz.php">Quiz</a>
            <a href="universites.php">Universités</a>
            <a href="conseils.php">Conseils</a>
            <a href="apropos.php">À propos</a>
        </nav>
        <div class="header-buttons">
            <a href="connexion.php" class="btn white-btn active">Se connecter</a>
            <a href="inscription.php" class="btn blue-btn">S'inscrire</a>
        </div>
    </div>
</header>

<!-- FORMULAIRE DE CONNEXION -->
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fa-solid fa-circle-user"></i>
            <h1>Connexion</h1>
            <p>Accédez à votre espace personnel</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fa-solid fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label><i class="fa-solid fa-envelope"></i> Adresse email</label>
                <input type="email" name="email" required placeholder="exemple@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label><i class="fa-solid fa-lock"></i> Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            
            <div class="password-link">
                <a href="mot-de-passe-oublie.php"><i class="fa-solid fa-key"></i> Mot de passe oublié ?</a>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> Se connecter
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Pas encore de compte ? <a href="inscription.php">Créer un compte</a></p>
            <p style="margin-top: 12px; font-size: 12px; color: #9ca3af;">
                <i class="fa-solid fa-shield-alt"></i> Vos données sont protégées
            </p>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="container footer-grid">
        <div>
            <h3>Mon Chemin</h3>
            <p>Votre partenaire pour une orientation scolaire moderne.</p>
        </div>
        <div>
            <h4>Liens utiles</h4>
            <a href="quiz.php">Quiz</a>
            <a href="universites.php">Universités</a>
            <a href="conseils.php">Conseils</a>
        </div>
        <div>
            <h4>Informations</h4>
            <a href="#">FAQ</a>
            <a href="#">Contact</a>
            <a href="#">Mentions légales</a>
        </div>
        <div>
            <h4>Mon compte</h4>
            <a href="connexion.php">Se connecter</a>
            <a href="inscription.php">S'inscrire</a>
        </div>
    </div>
</footer>

</body>
</html>