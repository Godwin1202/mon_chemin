<?php
// pages/attente.php

require_once '../php/db.php';
require_once '../php/auth.php';

redirigerSiNonConnecte();

$user_id = $_SESSION['user_id'];
$user_prenom = $_SESSION['user_prenom'];

// Récupérer le statut du dernier quiz
$statut = 'en_attente';
$statut_message = '';
$statut_icon = '';
$statut_class = '';

try {
    $stmt = $pdo->prepare("
        SELECT statut, commentaire_admin, date_validation
        FROM quiz_resultats 
        WHERE utilisateur_id = ? 
        ORDER BY date_creation DESC 
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $last_quiz = $stmt->fetch();

    if ($last_quiz) {
        $statut = $last_quiz['statut'];
    }
} catch (PDOException $e) {
    // Table peut ne pas exister
}

// Définir les messages selon le statut
switch ($statut) {
    case 'en_attente':
        $statut_message = 'Votre questionnaire a été transmis avec succès. Nos conseillers vont analyser votre profil et valider les recommandations proposées par le système. Vous recevrez une notification dès que votre résultat sera disponible.';
        $statut_icon = 'fa-regular fa-clock';
        $statut_class = 'en-attente';
        break;
    case 'valide':
        $statut_message = 'Votre quiz a été validé par nos conseillers. Il sera publié prochainement. Revenez dans quelques instants pour consulter vos résultats.';
        $statut_icon = 'fa-regular fa-check-circle';
        $statut_class = 'valide';
        break;
    case 'publie':
        $statut_message = 'Vos résultats sont disponibles ! Consultez votre profil pour découvrir vos recommandations.';
        $statut_icon = 'fa-regular fa-circle-check';
        $statut_class = 'publie';
        break;
    case 'rejete':
        $statut_message = 'Votre quiz a été examiné. Pour des raisons administratives, nous ne pouvons pas valider vos résultats. Vous pouvez contacter l\'administration pour plus d\'informations.';
        $statut_icon = 'fa-regular fa-circle-xmark';
        $statut_class = 'rejete';
        break;
    default:
        $statut_message = 'Votre quiz est en cours de traitement. Merci de patienter.';
        $statut_icon = 'fa-regular fa-clock';
        $statut_class = 'en-attente';
}

$page_prefix = '../';
include '../includes/header.php';
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
            padding: 0 20px;
        }
        .attente-card {
            background: white;
            border-radius: 24px;
            padding: 50px 40px;
            border: 1px solid var(--border);
        }
        .attente-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        .attente-icon.en-attente { background: #fef3c7; color: #d97706; }
        .attente-icon.valide { background: #dbeafe; color: #2563eb; }
        .attente-icon.publie { background: #d1fae5; color: #059669; }
        .attente-icon.rejete { background: #fee2e2; color: #dc2626; }
        .attente-icon i {
            font-size: 45px;
        }
        .attente-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .attente-title span {
            color: var(--primary);
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
        .btn-success {
            background: #059669;
            color: white;
            border: none;
        }
        .btn-success:hover {
            background: #047857;
        }
        @media (max-width: 600px) {
            .attente-card {
                padding: 30px 20px;
            }
            .attente-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="attente-container">
    <div class="attente-card">
        <div class="attente-icon <?= $statut_class ?>">
            <i class="<?= $statut_icon ?>"></i>
        </div>
        <h1 class="attente-title">
            <?php if ($statut === 'publie'): ?>
                Résultats <span>disponibles</span>
            <?php elseif ($statut === 'rejete'): ?>
                Quiz <span>non validé</span>
            <?php else: ?>
                Quiz <span>transmis</span>
            <?php endif; ?>
        </h1>
        <p class="attente-text"><?= $statut_message ?></p>
        <div class="attente-buttons">
            <?php if ($statut === 'publie'): ?>
                <a href="resultats.php" class="btn btn-success">
                    <i class="fa-solid fa-eye"></i> Voir mes résultats
                </a>
            <?php endif; ?>
            <a href="../index.php" class="btn white-btn">
                <i class="fa-solid fa-house"></i> Retour à l'accueil
            </a>
            <a href="profil.php" class="btn blue-btn">
                <i class="fa-solid fa-user"></i> Mon profil
            </a>
        </div>
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