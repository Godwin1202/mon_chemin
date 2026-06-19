<?php
// index.php

session_start();
require_once 'php/db.php';

// Vérifier si l'utilisateur est connecté
$is_connected = isset($_SESSION['user_id']);
$user_role = '';
$user_prenom = '';
$has_pending = 0;
$has_result = 0;

if ($is_connected) {
    $user_role = $_SESSION['user_role'] ?? 'eleve';
    $user_prenom = $_SESSION['user_prenom'] ?? '';
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_sessions WHERE utilisateur_id = ? AND statut = 'en_attente'");
    $stmt->execute([$_SESSION['user_id']]);
    $has_pending = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_sessions WHERE utilisateur_id = ? AND statut = 'publie'");
    $stmt->execute([$_SESSION['user_id']]);
    $has_result = $stmt->fetchColumn();
}

// Récupérer les universités
$stmt = $pdo->query("SELECT * FROM universites LIMIT 6");
$universites = $stmt->fetchAll();

// Récupérer les domaines pour la section
$stmt = $pdo->query("SELECT * FROM domaines ORDER BY nom");
$domaines = $stmt->fetchAll();

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'eleve'");
$total_eleves = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_sessions WHERE statut = 'publie'");
$total_validations = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Chemin - Orientation Afrique de l'Ouest</title>

    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
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
                    <p>Orientation Afrique de l'Ouest</p>
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
                <div class="user-menu">
                    <a href="pages/profil.php" class="btn white-btn" style="text-decoration: none;">
                        <i class="fa-solid fa-user"></i> <span><?= htmlspecialchars($user_prenom) ?></span>
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
            
            <?php if ($is_connected): ?>
                <?php if ($has_pending > 0): ?>
                    <div class="badge-notification badge-pending">
                        <i class="fa-solid fa-clock"></i>
                        Votre quiz est en cours d'analyse.
                    </div>
                <?php elseif ($has_result > 0): ?>
                    <div class="badge-notification badge-result">
                        <i class="fa-solid fa-check-circle"></i>
                        Votre résultat est disponible. <a href="pages/resultats.php">Consultez-le ici</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
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

<section class="universities-section">
    <div class="container">
        <h2 class="section-title">Universités partenaires</h2>
        <div class="universities-grid">
            <?php foreach ($universites as $univ): ?>
                <a href="pages/universites.php" class="university-card" style="text-decoration: none; color: inherit;">
                    <?php 
                    // Générer le nom du fichier image à partir du nom de l'université
                    $image_name = strtolower(str_replace([' ', "'", 'é', 'è', 'ê', 'à', 'â', 'î', 'ô', 'û'], ['', '', 'e', 'e', 'e', 'a', 'a', 'i', 'o', 'u'], $univ['nom']));
                    $image_path = 'assets/logos/' . $image_name . '.png';
                    ?>
                    <img src="<?= $image_path ?>" 
                         alt="<?= htmlspecialchars($univ['nom']) ?>"
                         onerror="this.style.display='none'">
                    <h3><?= htmlspecialchars($univ['nom']) ?></h3>
                    <p style="font-size:13px;color:#94a3b8;">
                        <?= htmlspecialchars($univ['ville'] ?? '') ?><?= (!empty($univ['ville']) && !empty($univ['pays'])) ? ', ' : '' ?><?= htmlspecialchars($univ['pays'] ?? '') ?>
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:30px;">
            <a href="pages/universites.php" class="btn blue-btn" style="text-decoration:none;">
                Voir toutes les universités
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
                    <p style="color:#6b7280;font-size:14px;margin-bottom:12px;">Nos conseils pour faire le bon choix.</p>
                    <a href="pages/conseils.php">Lire l'article →</a>
                </div>
            </div>
            <div class="tip-card">
                <img src="assets/images/conseil2.svg" alt="Débouchés">
                <div class="tip-content">
                    <h3>Les débouchés après le bac</h3>
                    <p style="color:#6b7280;font-size:14px;margin-bottom:12px;">Découvrez les métiers de demain.</p>
                    <a href="pages/conseils.php">Lire l'article →</a>
                </div>
            </div>
            <div class="tip-card">
                <img src="assets/images/conseil3.svg" alt="Ressources">
                <div class="tip-content">
                    <h3>Ressources pour les élèves</h3>
                    <p style="color:#6b7280;font-size:14px;margin-bottom:12px;">Guides, fiches métiers et plus encore.</p>
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
            <p>Votre partenaire pour une orientation scolaire moderne en Afrique de l'Ouest.</p>
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