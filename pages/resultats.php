<?php
// pages/resultats.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

// EMPÊCHER L'ADMIN
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit();
}

redirigerSiNonConnecte();

$user_id = $_SESSION['user_id'];

// Récupérer le résultat publié
$stmt = $pdo->prepare("
    SELECT qr.*, u.nom, u.prenom
    FROM quiz_resultats qr
    JOIN utilisateurs u ON qr.utilisateur_id = u.id
    WHERE qr.utilisateur_id = ? AND qr.statut = 'publie'
    ORDER BY qr.date_validation DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$resultat = $stmt->fetch();

if (!$resultat) {
    header('Location: profil.php');
    exit();
}

$scores = json_decode($resultat['scores_domaines'], true);
$domaines = json_decode($resultat['domaines_principaux'], true);
$filieres = json_decode($resultat['filieres_proposees'], true);
$universites = json_decode($resultat['universites_proposees'], true);

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes résultats - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .resultats-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .resultats-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .resultats-header .icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            font-size: 32px;
            color: var(--primary);
        }

        .resultats-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .resultats-header p {
            color: var(--gray);
            font-size: 15px;
        }

        .resultat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 20px;
        }

        .resultat-card h2 {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .resultat-card h2 i {
            color: var(--primary);
            margin-right: 8px;
        }

        .domaine-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .domaine-item:last-child {
            border-bottom: none;
        }

        .domaine-item .nom {
            font-weight: 500;
        }

        .domaine-item .score {
            font-weight: 700;
            color: var(--primary);
        }

        .filiere-tag {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            margin: 3px 5px 3px 0;
        }

        .univ-tag {
            display: inline-block;
            background: #f8fafc;
            color: #334155;
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            margin: 3px 5px 3px 0;
            border: 1px solid var(--border);
        }

        .commentaire {
            padding: 14px 16px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 14px;
            color: #475569;
            margin-top: 12px;
        }

        .commentaire strong {
            color: #334155;
        }

        @media (max-width: 600px) {
            .resultat-card {
                padding: 18px 16px;
            }
        }
    </style>
</head>
<body>

<div class="resultats-container">
    <div class="resultats-header">
        <div class="icon">
            <i class="fa-solid fa-check-circle"></i>
        </div>
        <h1>Votre orientation</h1>
        <p>Voici les résultats de votre quiz d'orientation</p>
    </div>

    <!-- Scores -->
    <div class="resultat-card">
        <h2><i class="fa-solid fa-chart-bar"></i> Scores par domaine</h2>
        <?php if ($domaines): ?>
            <?php foreach ($domaines as $domaine): ?>
                <div class="domaine-item">
                    <span class="nom"><?= htmlspecialchars($domaine['nom'] ?? '') ?></span>
                    <span class="score"><?= $domaine['score'] ?? 0 ?> pts</span>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--gray);font-size:14px;">Aucun score disponible</p>
        <?php endif; ?>
    </div>

    <!-- Filières recommandées -->
    <div class="resultat-card">
        <h2><i class="fa-solid fa-lightbulb"></i> Filières recommandées</h2>
        <?php if ($filieres && is_array($filieres)): ?>
            <?php foreach ($filieres as $filiere): ?>
                <span class="filiere-tag"><?= htmlspecialchars($filiere) ?></span>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color:var(--gray);font-size:14px;">Aucune filière proposée</p>
        <?php endif; ?>
    </div>

    <!-- Universités recommandées -->
    <?php if ($universites && is_array($universites) && !empty($universites)): ?>
    <div class="resultat-card">
        <h2><i class="fa-solid fa-building-columns"></i> Universités recommandées</h2>
        <?php foreach ($universites as $univ): ?>
            <?php 
            // Si $univ est un tableau, on prend le nom
            $nom_univ = is_array($univ) ? ($univ['nom'] ?? '') : $univ; 
            ?>
            <?php if (!empty($nom_univ)): ?>
                <span class="univ-tag"><?= htmlspecialchars($nom_univ) ?></span>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Commentaire admin -->
    <?php if ($resultat['commentaire_admin']): ?>
        <div class="resultat-card">
            <h2><i class="fa-solid fa-comment"></i> Commentaire de l'équipe</h2>
            <div class="commentaire">
                <?= htmlspecialchars($resultat['commentaire_admin']) ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Bouton retour -->
    <div style="text-align:center;margin-top:20px;">
        <a href="profil.php" class="btn white-btn" style="text-decoration:none;display:inline-block;">
            <i class="fa-solid fa-arrow-left"></i> Retour à mon profil
        </a>
    </div>
</div>

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
            <a href="#">Contact</a>
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
        </div>
        <div>
            <h4>Mon compte</h4>
            <a href="profil.php">Mon profil</a>
            <a href="../php/deconnexion.php">Déconnexion</a>
        </div>
    </div>
</footer>

<script src="../js/script.js"></script>

</body>
</html>