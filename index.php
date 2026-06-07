<?php
// index.php

session_start();

// Vérifier si l'utilisateur est connecté
$is_connected = isset($_SESSION['user_id']);
$user_role = '';

if ($is_connected) {
    $user_role = $_SESSION['user_role'] ?? 'eleve';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Chemin - Accueil</title>

    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- HEADER -->
<header class="header">
    <div class="container header-content">
        <div class="logo">
            <a href="index.php" style="display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit;">
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <h2>Mon Chemin</h2>
                    <p>Aide à l'orientation scolaire</p>
                </div>
            </a>
        </div>

        <nav class="navbar">
            <a href="index.php" class="active">Accueil</a>
            <a href="pages/quiz.php">Quiz</a>
            <a href="pages/universites.php">Universités</a>
            <a href="pages/conseils.php">Conseils</a>
            <a href="pages/apropos.php">À propos</a>
        </nav>

        <div class="header-buttons">
            <?php if ($is_connected): ?>
                <!-- Utilisateur connecté -->
                <div class="user-menu">
                    <a href="pages/profil.php" class="btn white-btn" style="text-decoration: none">
                        <i class="fa-solid fa-user"></i> <span>Mon profil</span>
                    </a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn blue-btn">
                            <i class="fa-solid fa-gauge-high"></i> <span>Admin</span>
                        </a>
                    <?php endif; ?>
                    <a href="php/deconnexion.php" class="btn logout-btn">
                        <i class="fa-solid fa-sign-out-alt"></i> <span>Déconnexion</span>
                    </a>
                </div>
            <?php else: ?>
                <!-- Utilisateur non connecté -->
                <a href="pages/connexion.php" class="btn white-btn">Se connecter</a>
                <a href="pages/inscription.php" class="btn blue-btn">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</header>


<!-- HERO -->
<section class="hero">
    <div class="container hero-content">
        <div class="hero-left">
            <h1>
                Trouvez votre voie,<br>
                <span>pas à pas</span>
            </h1>
            <p class="hero-text">
                L'orientation après le bac ou le DT est une étape complexe. Mon Chemin vous aide à identifier les parcours adaptés à votre profil grâce à un quiz validé par l'administration.
            </p>
            <div class="hero-card">
                <div class="hero-card-icon">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <div>
                    <h3>Orientation simplifiée</h3>
                    <p>
                        Mon Chemin vous accompagne pour trouver
                        les filières adaptées à votre profil,
                        vos compétences et vos ambitions.
                    </p>
                </div>
            </div>
        </div>
        <div class="hero-right">
            <img src="assets/illustrations/hero1.svg" alt="Illustration">
        </div>
    </div>
</section>

<!-- ACTIONS -->
<section class="action-section">
    <div class="container action-grid">
        <a href="pages/quiz.php" class="action-card blue-card" style="text-decoration: none;">
            <i class="fa-solid fa-pen"></i>
            <div>
                <h3>Commencer le quiz</h3>
                <p>Trouvez votre orientation idéale</p>
            </div>
        </a>

        <a href="pages/universites.php" class="action-card" style="text-decoration: none;">
            <i class="fa-solid fa-building-columns"></i>
            <div>
                <h3>Explorer les universités</h3>
                <p>Découvrez les filières disponibles</p>
            </div>
        </a>

        <a href="pages/conseils.php" class="action-card" style="text-decoration: none;">
            <i class="fa-solid fa-lightbulb"></i>
            <div>
                <h3>Voir les conseils</h3>
                <p>Guides et ressources utiles</p>
            </div>
        </a>
    </div>
</section>

<!-- COMMENT ÇA MARCHE -->
<section class="steps-section">
    <div class="container">
        <h2 class="section-title">Comment ça marche ?</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Je réponds au quiz</h3>
                <p>Je complète un questionnaire adapté à mon profil.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>L'administration valide</h3>
                <p>Les résultats sont vérifiés et analysés.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Je reçois mon orientation</h3>
                <p>Je découvre les parcours recommandés.</p>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES -->
<section class="features-section">
    <div class="container">
        <h2 class="section-title">Pourquoi choisir Mon Chemin ?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <i class="fa-solid fa-user-check"></i>
                <h3>Personnalisé</h3>
                <p>Des recommandations adaptées à votre profil.</p>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-wheelchair"></i>
                <h3>Inclusif</h3>
                <p>Une plateforme pensée pour tous les étudiants.</p>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-shield-halved"></i>
                <h3>Sécurisé</h3>
                <p>Vos données sont protégées et confidentielles.</p>
            </div>
            <div class="feature-card">
                <i class="fa-solid fa-award"></i>
                <h3>Validé</h3>
                <p>Un accompagnement reconnu par l'administration.</p>
            </div>
        </div>
    </div>
</section>

<!-- UNIVERSITÉS -->
<section class="universities-section">
    <div class="container">
        <h2 class="section-title">Universités partenaires</h2>
        <div class="universities-grid">
            <a href="pages/universites.php" class="university-card" style="text-decoration: none; color: inherit;">
                <img src="assets/logos/uac.png" alt="Université d'Abomey-Calavi">
                <h3>Université d'Abomey-Calavi</h3>
            </a>
            <a href="pages/universites.php" class="university-card" style="text-decoration: none; color: inherit;">
                <img src="assets/logos/parakou.jpg" alt="Université de Parakou">
                <h3>Université de Parakou</h3>
            </a>
            <a href="pages/universites.php" class="university-card" style="text-decoration: none; color: inherit;">
                <img src="assets/logos/kara.jpg" alt="Université de Kara">
                <h3>Université de Kara</h3>
            </a>
            <a href="pages/universites.php" class="university-card" style="text-decoration: none; color: inherit;">
                <img src="assets/logos/lome.png" alt="Université de Lomé">
                <h3>Université de Lomé</h3>
            </a>
        </div>
    </div>
</section>

<!-- CONSEILS -->
<section class="tips-section">
    <div class="container">
        <h2 class="section-title">Conseils et ressources</h2>
        <div class="tips-grid">
            <div class="tip-card">
                <img src="assets/images/conseil1.svg" alt="Conseil orientation">
                <div class="tip-content">
                    <h3>Comment bien choisir son orientation ?</h3>
                    <a href="pages/conseils.php">Lire l'article →</a>
                </div>
            </div>
            <div class="tip-card">
                <img src="assets/images/conseil2.svg" alt="Débouchés">
                <div class="tip-content">
                    <h3>Les débouchés après le bac</h3>
                    <a href="pages/conseils.php">Lire l'article →</a>
                </div>
            </div>
            <div class="tip-card">
                <img src="assets/images/conseil3.svg" alt="Ressources">
                <div class="tip-content">
                    <h3>Ressources pour les élèves</h3>
                    <a href="pages/conseils.php">Lire l'article →</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container cta-content">
        <div>
            <h2>Prêt à trouver votre chemin ?</h2>
            <p>Commencez dès maintenant votre orientation personnalisée.</p>
            <a href="pages/quiz.php" class="cta-btn">Commencer le quiz</a>
        </div>
        <img src="assets/illustrations/student1.svg" alt="Étudiant">
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
            <a href="pages/quiz.php">Quiz</a>
            <a href="pages/universites.php">Universités</a>
            <a href="pages/conseils.php">Conseils</a>
            <a href="pages/apropos.php">À propos</a>
        </div>
        <div>
            <h4>Informations</h4>
            <a href="pages/contact.php">Contact</a>
            <a href="pages/mentions-legales.php">Mentions légales</a>
            <a href="pages/confidentialite.php">Politique de confidentialité</a>
        </div>
        <div>
            <h4>Mon compte</h4>
            <a href="pages/connexion.php">Se connecter</a>
            <a href="pages/inscription.php">S'inscrire</a>
            <a href="pages/profil.php">Mon profil</a>
        </div>
    </div>
</footer>

<script src="js/script.js"></script>

</body>
</html>