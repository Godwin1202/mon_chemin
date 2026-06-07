<?php
// pages/attente.php

require_once '../php/db.php';
require_once '../php/auth.php';

redirigerSiNonConnecte();

$user_id = $_SESSION['user_id'];
$user_prenom = $_SESSION['user_prenom'];

// Vérifier si l'utilisateur a un quiz en attente
$stmt = $pdo->prepare("
    SELECT statut_validation, date_fin 
    FROM quiz_sessions 
    WHERE utilisateur_id = ? 
    ORDER BY date_fin DESC 
    LIMIT 1
");
$stmt->execute([$user_id]);
$last_quiz = $stmt->fetch();

// Si aucun quiz n'existe, rediriger vers le quiz
if (!$last_quiz) {
    header('Location: quiz.php');
    exit();
}
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
            <div class="user-menu">
                <a href="profil.php" class="btn white-btn">
                    <i class="fa-solid fa-user"></i> <span>Mon profil</span>
                </a>
                <a href="../php/deconnexion.php" class="btn logout-btn">
                    <i class="fa-solid fa-sign-out-alt"></i> <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- PAGE D'ATTENTE -->
<section class="hero">
    <div class="container hero-content" style="justify-content: center; text-align: center;">
        <div class="hero-left" style="text-align: center; max-width: 700px; margin: 0 auto;">
            
            <!-- Animation / Illustration -->
            <div style="margin-bottom: 40px;">
                <div style="width: 120px; height: 120px; background: #eff6ff; border-radius: 60px; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                    <i class="fa-solid fa-paper-plane" style="font-size: 50px; color: #2563eb;"></i>
                </div>
            </div>
            
            <h1 style="margin-bottom: 20px;">Quiz <span>envoyé</span> !</h1>
            
            <div class="hero-card" style="margin: 0 auto 30px auto; max-width: 500px; text-align: left;">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div>
                    <h3>Merci <?= htmlspecialchars($user_prenom) ?> !</h3>
                    <p>Vos réponses ont été enregistrées avec succès.</p>
                </div>
            </div>
            
            <div style="background: #f8fafc; border-radius: 20px; padding: 30px; margin-bottom: 30px;">
                <i class="fa-solid fa-hourglass-half" style="font-size: 30px; color: #2563eb; margin-bottom: 15px;"></i>
                <p style="font-size: 16px; color: #4b5563;">
                    Les résultats sont en cours d'analyse et de validation par notre équipe administrative.
                </p>
                <p style="font-size: 14px; color: #9ca3af; margin-top: 15px;">
                    Délai moyen de traitement : 24 à 48 heures
                </p>
            </div>
            
            <p style="font-size: 15px; color: #6b7280; margin-bottom: 15px;">
                <i class="fa-solid fa-envelope"></i> Vous serez notifié par email dès que votre orientation sera validée.
            </p>
            <p style="font-size: 14px; color: #9ca3af;">
                En attendant, découvrez nos conseils d'orientation ou explorez les universités partenaires.
            </p>
            
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <a href="conseils.php" class="btn white-btn" style="text-decoration: none;">
                    <i class="fa-solid fa-lightbulb"></i> Voir les conseils
                </a>
                <a href="universites.php" class="btn blue-btn" style="text-decoration: none;">
                    <i class="fa-solid fa-building-columns"></i> Explorer les universités
                </a>
            </div>
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