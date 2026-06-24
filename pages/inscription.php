<?php
// pages/inscription.php

session_start();
require_once '../php/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$form_data = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    
    if (empty($form_data['nom']) || empty($form_data['prenom']) || empty($form_data['email']) || empty($password)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse email invalide.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$form_data['email']]);
        if ($stmt->fetch()) {
            $error = "Cette adresse email est déjà utilisée.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, telephone, niveau, situation_handicap, type_handicap, parent_email, parent_whatsapp, mot_de_passe, role, date_creation)
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
                $password
            ]);
            
            $_SESSION['inscription_success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            header('Location: connexion.php');
            exit();
        }
    }
}

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="auth-page">
    <div class="auth-container auth-wide">
        <div class="auth-card">
            
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <h1>Inscription</h1>
                <p>Créez votre compte en quelques instants</p>
            </div>
            
            <?php if ($error): ?>
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom <span class="required">*</span></label>
                        <input type="text" name="nom" required placeholder="Votre nom" value="<?= htmlspecialchars($form_data['nom'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Prénom <span class="required">*</span></label>
                        <input type="text" name="prenom" required placeholder="Votre prénom" value="<?= htmlspecialchars($form_data['prenom'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required placeholder="exemple@email.com" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="tel" name="telephone" placeholder="+229 XX XX XX XX" value="<?= htmlspecialchars($form_data['telephone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Niveau scolaire</label>
                        <select name="niveau">
                            <option value="">Sélectionnez</option>
                            <option value="Terminale" <?= ($form_data['niveau'] ?? '') === 'Terminale' ? 'selected' : '' ?>>Terminale</option>
                            <option value="Bac" <?= ($form_data['niveau'] ?? '') === 'Bac' ? 'selected' : '' ?>>Baccalauréat</option>
                            <option value="DT" <?= ($form_data['niveau'] ?? '') === 'DT' ? 'selected' : '' ?>>DT</option>
                            <option value="Licence 1" <?= ($form_data['niveau'] ?? '') === 'Licence 1' ? 'selected' : '' ?>>Licence 1</option>
                            <option value="Licence 2" <?= ($form_data['niveau'] ?? '') === 'Licence 2' ? 'selected' : '' ?>>Licence 2</option>
                            <option value="Licence 3" <?= ($form_data['niveau'] ?? '') === 'Licence 3' ? 'selected' : '' ?>>Licence 3</option>
                            <option value="Master" <?= ($form_data['niveau'] ?? '') === 'Master' ? 'selected' : '' ?>>Master</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Mot de passe <span class="required">*</span></label>
                        <input type="password" name="password" required placeholder="Minimum 6 caractères">
                    </div>
                    <div class="form-group">
                        <label>Confirmer <span class="required">*</span></label>
                        <input type="password" name="password_confirm" required placeholder="Retapez votre mot de passe">
                    </div>
                </div>
                
                <div style="margin: 16px 0 8px;">
                    <hr style="border: none; border-top: 1px solid #e2e8f0;">
                    <p style="font-size: 13px; font-weight: 600; color: #64748b; margin: 12px 0 4px;">
                        <i class="fa-regular fa-envelope"></i> Contact des parents (recommandé)
                    </p>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Email du parent</label>
                        <input type="email" name="parent_email" placeholder="parent@email.com" value="<?= htmlspecialchars($form_data['parent_email'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>WhatsApp du parent</label>
                        <input type="text" name="parent_whatsapp" placeholder="+229 XX XX XX XX" value="<?= htmlspecialchars($form_data['parent_whatsapp'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="auth-checkbox">
                    <input type="checkbox" name="situation_handicap" id="situation_handicap" value="1" <?= isset($form_data['situation_handicap']) && $form_data['situation_handicap'] ? 'checked' : '' ?>>
                    <label for="situation_handicap">Je suis en situation de handicap</label>
                </div>
                
                <div class="auth-handicap-field" id="handicap_field">
                    <div class="form-group">
                        <label>Type de handicap (optionnel)</label>
                        <input type="text" name="type_handicap" placeholder="Précisez si vous le souhaitez" value="<?= htmlspecialchars($form_data['type_handicap'] ?? '') ?>">
                    </div>
                </div>
                
                <button type="submit" class="auth-btn">
                    <i class="fa-solid fa-paper-plane"></i> Créer mon compte
                </button>
            </form>
            
            <div class="auth-footer">
                Déjà inscrit ? <a href="connexion.php">Se connecter</a>
            </div>
            
        </div>
    </div>
</div>

<script>
const handicapCheckbox = document.getElementById('situation_handicap');
const handicapField = document.getElementById('handicap_field');

if (handicapCheckbox) {
    if (handicapCheckbox.checked) handicapField.classList.add('show');
    handicapCheckbox.addEventListener('change', function() {
        this.checked ? handicapField.classList.add('show') : handicapField.classList.remove('show');
    });
}
</script>

</body>
</html>