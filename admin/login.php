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
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            max-width: 420px;
            width: 90%;
            background: white;
            border-radius: 32px;
            padding: 48px 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        }
        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-header i {
            font-size: 48px;
            color: #2563eb;
        }
        .login-header h1 {
            font-size: 28px;
            margin: 15px 0 5px;
        }
        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 15px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-login:hover {
            background: #1d4ed8;
        }
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <h1>Mon Chemin Admin</h1>
            <p>Connectez-vous à l'espace d'administration</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="admin@monchemin.com">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Se connecter</button>
        </form>
    </div>
</body>
</html>