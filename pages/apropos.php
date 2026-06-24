<?php
// pages/apropos.php

session_start();
require_once '../php/db.php';

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À propos - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- HERO À PROPOS -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-left">
            <h1>À propos de <span>Mon Chemin</span></h1>
            <p class="hero-text">
                Découvrez notre mission, nos valeurs et l'équipe qui travaille 
                pour vous accompagner dans votre orientation scolaire.
            </p>
            <div class="hero-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div>
                    <h3>Une plateforme humaine</h3>
                    <p>
                        Mon Chemin est né d'un constat simple : l'orientation après le bac 
                        est trop complexe. Nous avons créé un outil simple, inclusif et validé.
                    </p>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <img src="../assets/illustrations/about.svg" alt="À propos" onerror="this.src='../assets/illustrations/hero1.svg'">
        </div>
    </div>
</section>

<!-- NOTRE MISSION -->
<section class="mission-section">
    <div class="container">
        <h2 class="section-title">Notre mission</h2>
        <div class="mission-content">
            <div class="mission-text">
                <p>
                    Mon Chemin a pour ambition de proposer une plateforme web permettant 
                    d'aider les élèves à identifier des parcours adaptés à leur profil 
                    grâce à un quiz d'orientation validé par les services administratifs compétents.
                </p>
                <p>
                    Nous croyons que chaque élève mérite un accompagnement personnalisé, 
                    accessible et inclusif, quelle que soit sa situation.
                </p>
            </div>
            <div class="mission-image">
                <img src="../assets/illustrations/mission.svg" alt="Mission" onerror="this.style.display='none'">
            </div>
        </div>
    </div>
</section>

<!-- NOS VALEURS -->
<section class="values-section">
    <div class="container">
        <h2 class="section-title">Nos valeurs</h2>
        <div class="values-grid">
            <div class="value-card">
                <i class="fa-solid fa-user-check"></i>
                <h3>Personnalisé</h3>
                <p>Des recommandations adaptées à chaque profil unique.</p>
            </div>
            <div class="value-card">
                <i class="fa-solid fa-wheelchair"></i>
                <h3>Inclusif</h3>
                <p>Une plateforme pensée pour tous, y compris les élèves en situation de handicap.</p>
            </div>
            <div class="value-card">
                <i class="fa-solid fa-hand-holding-heart"></i>
                <h3>Accompagnement</h3>
                <p>Un suivi personnalisé après le quiz avec validation administrative.</p>
            </div>
            <div class="value-card">
                <i class="fa-solid fa-shield-halved"></i>
                <h3>Fiable</h3>
                <p>Des informations vérifiées et conformes aux parcours existants.</p>
            </div>
        </div>
    </div>
</section>

<!-- L'ÉQUIPE -->
<section class="team-section">
    <div class="container">
        <h2 class="section-title">L'équipe</h2>
        <div class="team-grid">
            <div class="team-card">
                <div class="team-avatar">
                    <img src="../assets/illustrations/avatar1.svg" alt="Développeur" onerror="this.src='../assets/illustrations/hero1.svg'">
                </div>
                <h3>Développeur 1</h3>
                <p>Étudiant en développement web</p>
                <p class="team-bio">Passionné par les technologies web et l'éducation.</p>
            </div>
            <div class="team-card">
                <div class="team-avatar">
                    <img src="../assets/illustrations/avatar2.svg" alt="Développeuse" onerror="this.src='../assets/illustrations/hero1.svg'">
                </div>
                <h3>Développeur 2</h3>
                <p>Étudiant en développement web</p>
                <p class="team-bio">Spécialisé dans l'expérience utilisateur et le design.</p>
            </div>
        </div>
        <p class="team-note">Projet réalisé en binôme dans le cadre d'un projet d'étude.</p>
    </div>
</section>

<!-- CHIFFRES CLÉS -->
<section class="stats-section">
    <div class="container">
        <h2 class="section-title">Mon Chemin en chiffres</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fa-solid fa-graduation-cap"></i>
                <div class="stat-number">500+</div>
                <p>Élèves accompagnés</p>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-building-columns"></i>
                <div class="stat-number">20+</div>
                <p>Universités partenaires</p>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-file-pdf"></i>
                <div class="stat-number">100%</div>
                <p>Validé par l'administration</p>
            </div>
            <div class="stat-card">
                <i class="fa-solid fa-star"></i>
                <div class="stat-number">4.8/5</div>
                <p>Note de satisfaction</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container cta-content">
        <div>
            <h2>Prêt à trouver votre voie ?</h2>
            <p>Commencez dès maintenant votre orientation personnalisée.</p>
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