<?php
// pages/conseils.php

session_start();
require_once '../php/db.php';

$is_connected = isset($_SESSION['user_id']);
$user_role = $_SESSION['user_role'] ?? 'eleve';

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conseils - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- HERO CONSEILS -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-left">
            <h1>Conseils et <span>Ressources</span></h1>
            <p class="hero-text">
                Retrouvez nos guides pratiques pour mieux préparer votre avenir,
                choisir votre filière et réussir votre parcours universitaire.
            </p>
            <div class="hero-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-lightbulb"></i>
                </div>
                <div>
                    <h3>Orientation intelligente</h3>
                    <p>
                        Découvrez des conseils concrets pour faire les meilleurs
                        choix d'études et construire votre projet professionnel.
                    </p>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <img src="../assets/illustrations/conseils.svg" alt="Conseils" onerror="this.src='../assets/illustrations/hero1.svg'">
        </div>
    </div>
</section>

<!-- ARTICLES -->
<section class="articles-section">
    <div class="container">
        <h2 class="section-title">Nos conseils d'orientation</h2>

        <div class="articles-grid">
            <div class="article-card">
                <i class="fa-solid fa-compass"></i>
                <h3>Comment choisir sa filière ?</h3>
                <p>Découvrez une méthode simple pour identifier votre parcours idéal.</p>
                <a href="#">Lire plus →</a>
            </div>

            <div class="article-card">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <h3>Les erreurs à éviter</h3>
                <p>Les pièges les plus fréquents après l'obtention du baccalauréat.</p>
                <a href="#">Lire plus →</a>
            </div>

            <div class="article-card">
                <i class="fa-solid fa-briefcase"></i>
                <h3>Les métiers d'avenir</h3>
                <p>Découvrez les secteurs les plus porteurs pour les prochaines années.</p>
                <a href="#">Lire plus →</a>
            </div>

            <div class="article-card">
                <i class="fa-solid fa-laptop-code"></i>
                <h3>Pourquoi choisir l'informatique ?</h3>
                <p>Un secteur dynamique offrant de nombreuses opportunités.</p>
                <a href="#">Lire plus →</a>
            </div>

            <div class="article-card">
                <i class="fa-solid fa-graduation-cap"></i>
                <h3>Réussir sa première année</h3>
                <p>Des conseils pratiques pour bien démarrer à l'université.</p>
                <a href="#">Lire plus →</a>
            </div>

            <div class="article-card">
                <i class="fa-solid fa-award"></i>
                <h3>Obtenir une bourse</h3>
                <p>Les démarches essentielles pour financer vos études.</p>
                <a href="#">Lire plus →</a>
            </div>
        </div>
    </div>
</section>

<!-- CONSEIL DU JOUR -->
<section class="quote-section">
    <div class="container">
        <div class="quote-card">
            <i class="fa-solid fa-lightbulb"></i>
            <h2>Conseil du jour</h2>
            <p>
                Ne choisissez pas une filière uniquement parce qu'elle est populaire.
                Choisissez une voie qui correspond à vos compétences, vos intérêts
                et vos objectifs professionnels.
            </p>
        </div>
    </div>
</section>

<!-- RESSOURCES -->
<section class="resources-section">
    <div class="container">
        <h2 class="section-title">Ressources utiles</h2>

        <div class="resources-grid">
            <div class="resource-card">
                <i class="fa-solid fa-book"></i>
                <h3>Guide d'orientation</h3>
            </div>
            <div class="resource-card">
                <i class="fa-solid fa-calendar-days"></i>
                <h3>Calendrier universitaire</h3>
            </div>
            <div class="resource-card">
                <i class="fa-solid fa-circle-question"></i>
                <h3>FAQ Étudiants</h3>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container cta-content">
        <div>
            <h2>Besoin d'une orientation personnalisée ?</h2>
            <p>Répondez à notre quiz et recevez une recommandation adaptée à votre profil.</p>
            <a href="quiz.php" class="cta-btn">Commencer le quiz</a>
        </div>
        <img src="../assets/illustrations/student1.svg" alt="Quiz">
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
            <a href="apropos.php">À propos</a>
        </div>
        <div>
            <h4>Informations</h4>
            <a href="#">FAQ</a>
            <a href="#">Contact</a>
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
        </div>
        <div>
            <h4>Réseaux sociaux</h4>
            <div class="socials">
                <i class="fab fa-facebook"></i>
                <i class="fab fa-instagram"></i>
                <i class="fab fa-linkedin"></i>
            </div>
        </div>
    </div>
</footer>

<script src="../js/script.js"></script>

</body>
</html>