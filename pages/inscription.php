<?php
// pages/inscription.php

session_start();
require_once '../php/db.php';

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données
    $form_data = [
        'nom' => trim($_POST['nom'] ?? ''),
        'prenom' => trim($_POST['prenom'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telephone' => trim($_POST['telephone'] ?? ''),
        'niveau' => $_POST['niveau'] ?? '',
        'situation_handicap' => isset($_POST['situation_handicap']) ? 1 : 0,
        'type_handicap' => trim($_POST['type_handicap'] ?? ''),
        'parent_email' => trim($_POST['parent_email'] ?? ''),
        'parent_whatsapp' => trim($_POST['parent_whatsapp'] ?? '')
    ];
    
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // Validation
    if (empty($form_data['nom']) || empty($form_data['prenom']) || empty($form_data['email']) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Veuillez saisir une adresse email valide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifier si l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$form_data['email']]);
        if ($stmt->fetch()) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            // Insérer l'utilisateur (mot de passe non hashé pour le moment, à modifier en production)
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, telephone, niveau, situation_handicap, type_handicap, parent_email, parent_whatsapp, mot_de_passe, role, date_inscription)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'eleve', NOW())
            ");
            
            $stmt->execute([
                $form_data['nom'],
                $form_data['prenom'],
                $form_data['email'],
                $form_data['telephone'],
                $form_data['niveau'],
                $form_data['situation_handicap'],
                $form_data['type_handicap'],
                $form_data['parent_email'],
                $form_data['parent_whatsapp'],
                $password // À remplacer par password_hash() en production
            ]);
            
            $_SESSION['inscription_success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header('Location: connexion.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Mon Chemin</title>

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
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.85), rgba(29, 78, 216, 0.9)), url('../assets/images/login-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        
        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 32px;
            padding: 48px 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.5s ease;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group.full-width {
            grid-column: span 2;
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
        
        .form-group label .required {
            color: #ef4444;
        }
        
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px;
            transition: 0.2s;
            font-family: inherit;
        }
        
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 15px 0;
        }
        
        .checkbox-group input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
        }
        
        .handicap-field {
            display: none;
            margin-top: 15px;
            margin-left: 30px;
        }
        
        .handicap-field.show {
            display: block;
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
        
        @media (max-width: 600px) {
            .auth-card {
                padding: 32px 24px;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group.full-width {
                grid-column: span 1;
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
            <a href="connexion.php" class="btn white-btn">Se connecter</a>
            <a href="inscription.php" class="btn blue-btn active">S'inscrire</a>
        </div>
    </div>
</header>

<!-- FORMULAIRE D'INSCRIPTION -->
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <i class="fa-solid fa-user-plus"></i>
            <h1>Inscription</h1>
            <p>Créez votre compte pour commencer le quiz</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group">
                    <label><i class="fa-solid fa-user"></i> Nom <span class="required">*</span></label>
                    <input type="text" name="nom" required placeholder="Votre nom" value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-user"></i> Prénom <span class="required">*</span></label>
                    <input type="text" name="prenom" required placeholder="Votre prénom" value="<?= htmlspecialchars($form_data['prenom'] ?? '') ?>">
                </div>
                
                <div class="form-group full-width">
                    <label><i class="fa-solid fa-envelope"></i> Email <span class="required">*</span></label>
                    <input type="email" name="email" required placeholder="exemple@email.com" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-phone"></i> Téléphone</label>
                    <input type="tel" name="telephone" placeholder="+229 XX XX XX XX" value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-graduation-cap"></i> Niveau scolaire</label>
                    <select name="niveau">
                        <option value="">Sélectionnez votre niveau</option>
                        <option value="Terminale" <?= ($form_data['niveau'] ?? '') === 'Terminale' ? 'selected' : '' ?>>Terminale</option>
                        <option value="Bac" <?= ($form_data['niveau'] ?? '') === 'Bac' ? 'selected' : '' ?>>Baccalauréat</option>
                        <option value="DT" <?= ($form_data['niveau'] ?? '') === 'DT' ? 'selected' : '' ?>>DT (Diplôme de Technicien)</option>
                        <option value="Licence 1" <?= ($form_data['niveau'] ?? '') === 'Licence 1' ? 'selected' : '' ?>>Licence 1</option>
                        <option value="Licence 2" <?= ($form_data['niveau'] ?? '') === 'Licence 2' ? 'selected' : '' ?>>Licence 2</option>
                        <option value="Licence 3" <?= ($form_data['niveau'] ?? '') === 'Licence 3' ? 'selected' : '' ?>>Licence 3</option>
                        <option value="Master" <?= ($form_data['niveau'] ?? '') === 'Master' ? 'selected' : '' ?>>Master</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-lock"></i> Mot de passe <span class="required">*</span></label>
                    <input type="password" name="password" required placeholder="Minimum 6 caractères">
                </div>
                
                <div class="form-group">
                    <label><i class="fa-solid fa-lock"></i> Confirmer le mot de passe <span class="required">*</span></label>
                    <input type="password" name="password_confirm" required placeholder="Retapez votre mot de passe">
                </div>
            </div>
            
            <!-- Parents -->
            <div style="margin: 20px 0 15px 0;">
                <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 10px 0;">
                <p style="font-weight: 600; margin-bottom: 15px; color: #374151;">
                    <i class="fa-solid fa-family"></i> Informations des parents (recommandé)
                </p>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label><i class="fa-solid fa-envelope"></i> Email du parent</label>
                        <input type="email" name="parent_email" placeholder="parent@email.com" value="<?= htmlspecialchars($form_data['parent_email'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa-brands fa-whatsapp"></i> WhatsApp du parent</label>
                        <input type="text" name="parent_whatsapp" placeholder="+229 XX XX XX XX" value="<?= htmlspecialchars($form_data['parent_whatsapp'] ?? '') ?>">
                    </div>
                </div>
            </div>
            
            <!-- Handicap -->
            <div class="checkbox-group">
                <input type="checkbox" name="situation_handicap" id="situation_handicap" value="1" <?= isset($form_data['situation_handicap']) && $form_data['situation_handicap'] ? 'checked' : '' ?>>
                <label for="situation_handicap">Je suis en situation de handicap</label>
            </div>
            
            <div class="handicap-field" id="handicap_field">
                <div class="form-group">
                    <label><i class="fa-solid fa-wheelchair"></i> Type de handicap (optionnel)</label>
                    <input type="text" name="type_handicap" placeholder="Précisez si vous le souhaitez" value="<?= htmlspecialchars($form_data['type_handicap'] ?? '') ?>">
                </div>
            </div>
            
            <button type="submit" class="btn-auth">
                <i class="fa-solid fa-user-plus"></i> S'inscrire
            </button>
        </form>
        
        <div class="auth-footer">
            <p>Déjà inscrit ? <a href="connexion.php">Se connecter</a></p>
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

<script>
    // Afficher/masquer le champ handicap
    const handicapCheckbox = document.getElementById('situation_handicap');
    const handicapField = document.getElementById('handicap_field');
    
    if (handicapCheckbox) {
        if (handicapCheckbox.checked) {
            handicapField.classList.add('show');
        }
        
        handicapCheckbox.addEventListener('change', function() {
            if (this.checked) {
                handicapField.classList.add('show');
            } else {
                handicapField.classList.remove('show');
            }
        });
    }
</script>

</body>
</html>