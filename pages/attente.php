<?php
// pages/attente.php

require_once '../php/db.php';
require_once '../php/auth.php';

redirigerSiNonConnecte();
$user_id = $_SESSION['user_id'];
$user_prenom = $_SESSION['user_prenom'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz envoyé - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .attente-container {
            max-width: 700px;
            margin: 60px auto;
            text-align: center;
        }
        .attente-card {
            background: white;
            border-radius: 32px;
            padding: 50px 40px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        .attente-icon {
            width: 100px;
            height: 100px;
            background: #eff6ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        .attente-icon i {
            font-size: 45px;
            color: #2563eb;
        }
        .attente-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .attente-title span {
            color: #2563eb;
        }
        .attente-text {
            color: #6b7280;
            line-height: 1.8;
            margin-bottom: 30px;
        }
        .attente-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .attente-buttons .btn {
            text-decoration: none;
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
            <a href="quiz.php" class="active">Quiz</a>
            <a href="universites.php">Universités</a>
            <a href="conseils.php">Conseils</a>
            <a href="apropos.php">À propos</a>
        </nav>
        <div class="header-buttons">
            <div class="user-menu">
                <a href="profil.php" class="btn white-btn">
                    <i class="fa-solid fa-user"></i> Mon compte
                </a>
                <a href="../php/deconnexion.php" class="btn logout-btn">
                    <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</header>

<!-- PAGE D'ATTENTE -->
<section class="attente-container">
    <div class="attente-card">
        <div class="attente-icon">
            <i class="fa-solid fa-paper-plane"></i>
        </div>
        <h1 class="attente-title">Quiz <span>transmis</span> !</h1>
        <p class="attente-text">
            Votre questionnaire a été transmis avec succès.<br><br>
            <strong>Nos conseillers vont analyser votre profil</strong> et valider les recommandations proposées par le système.<br><br>
            Vous recevrez une notification dès que votre résultat sera disponible.
        </p>
        <div class="attente-buttons">
            <a href="../index.php" class="btn white-btn">
                <i class="fa-solid fa-house"></i> Retour à l'accueil
            </a>
            <a href="profil.php" class="btn blue-btn">
                <i class="fa-solid fa-user"></i> Mon profil
            </a>
        </div>
    </div>
</section>

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
            <a href="profil.php">Mon profil</a>
            <a href="../php/deconnexion.php">Déconnexion</a>
        </div>
    </div>
</footer>

</body>
</html>