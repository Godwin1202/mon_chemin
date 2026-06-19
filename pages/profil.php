<?php
// pages/profil.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

redirigerSiNonConnecte();

$user_id = $_SESSION['user_id'];
$user_prenom = $_SESSION['user_prenom'];
$user_nom = $_SESSION['user_nom'];
$user_email = $_SESSION['user_email'];

$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT qr.* 
    FROM quiz_resultats qr
    WHERE qr.utilisateur_id = ?
    ORDER BY qr.date_creation DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$resultat = $stmt->fetch();

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - Mon Chemin</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .profil-container {
            max-width: 960px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .profil-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 1px solid var(--border);
        }

        .profil-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #64748b;
            flex-shrink: 0;
        }

        .profil-header-info h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .profil-header-info p {
            color: var(--gray);
            font-size: 14px;
        }

        .profil-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .profil-card {
            background: white;
            border-radius: 20px;
            padding: 24px 28px;
            border: 1px solid var(--border);
        }

        .profil-card h2 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .profil-card h2 i {
            color: var(--gray);
            margin-right: 8px;
            font-size: 15px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f8fafc;
            font-size: 14px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: var(--gray);
        }

        .info-row .value {
            font-weight: 500;
        }

        .badge-statut {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
        }

        .badge-statut.en_attente { background: #fef3c7; color: #92400e; }
        .badge-statut.valide { background: #dbeafe; color: #1d4ed8; }
        .badge-statut.publie { background: #d1fae5; color: #065f46; }
        .badge-statut.rejete { background: #fee2e2; color: #991b1b; }

        .resultat-item {
            padding: 10px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .resultat-item:last-child {
            border-bottom: none;
        }

        .resultat-item .domaine {
            font-size: 14px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
        }

        .resultat-item .barre {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 10px;
            margin-top: 6px;
            overflow: hidden;
        }

        .resultat-item .barre .fill {
            height: 100%;
            background: var(--primary);
            border-radius: 10px;
        }

        .btn-action {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            text-align: center;
            display: block;
            font-size: 14px;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-danger {
            background: white;
            color: #dc2626;
            border: 2px solid #dc2626;
        }

        .btn-danger:hover {
            background: #dc2626;
            color: white;
        }

        .no-result {
            text-align: center;
            padding: 25px 0;
            color: var(--gray);
            font-size: 14px;
        }

        .no-result i {
            font-size: 36px;
            color: #e5e7eb;
            margin-bottom: 12px;
            display: block;
        }

        .tag {
            display: inline-block;
            padding: 3px 12px;
            background: #f1f5f9;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            margin: 3px 4px 3px 0;
        }

        .commentaire-admin {
            margin-top: 12px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 13px;
            color: #475569;
        }

        .commentaire-admin strong {
            color: #334155;
        }

        @media (max-width: 768px) {
            .profil-header {
                flex-direction: column;
                text-align: center;
            }
            .profil-grid {
                grid-template-columns: 1fr;
            }
            .profil-card {
                padding: 18px 16px;
            }
        }
    </style>
</head>
<body>

<div class="profil-container">
    <!-- En-tête -->
    <div class="profil-header">
        <div class="profil-avatar">
            <i class="fa-solid fa-user"></i>
        </div>
        <div class="profil-header-info">
            <h1><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h1>
            <p><?= htmlspecialchars($user['email']) ?></p>
            <?php if ($user['telephone']): ?>
                <p style="font-size:13px;color:#94a3b8;"><?= htmlspecialchars($user['telephone']) ?></p>
            <?php endif; ?>
            <p style="font-size:12px;color:#cbd5e1;margin-top:2px;">
                Membre depuis <?= date('d/m/Y', strtotime($user['date_creation'])) ?>
            </p>
        </div>
    </div>

    <div class="profil-grid">
        <!-- Colonne gauche -->
        <div>
            <div class="profil-card">
                <h2><i class="fa-regular fa-circle-user"></i> Informations</h2>
                
                <div class="info-row">
                    <span class="label">Nom complet</span>
                    <span class="value"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Email</span>
                    <span class="value"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Téléphone</span>
                    <span class="value"><?= htmlspecialchars($user['telephone'] ?? '—') ?></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Niveau scolaire</span>
                    <span class="value"><?= htmlspecialchars($user['niveau'] ?? '—') ?></span>
                </div>
                
                <div class="info-row">
                    <span class="label">Handicap</span>
                    <span class="value"><?= $user['situation_handicap'] ? 'Oui' : 'Non' ?></span>
                </div>

                <?php if ($user['parent_email'] || $user['parent_whatsapp']): ?>
                <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);">
                    <p style="font-size:12px;font-weight:600;color:#94a3b8;margin-bottom:6px;">CONTACT PARENT</p>
                    <?php if ($user['parent_email']): ?>
                        <p style="font-size:13px;"><?= htmlspecialchars($user['parent_email']) ?></p>
                    <?php endif; ?>
                    <?php if ($user['parent_whatsapp']): ?>
                        <p style="font-size:13px;"><?= htmlspecialchars($user['parent_whatsapp']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <a href="../php/deconnexion.php" class="btn-action btn-danger" style="margin-top:18px;text-decoration:none;">
                    <i class="fa-solid fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <!-- Colonne droite -->
        <div>
            <div class="profil-card">
                <h2><i class="fa-regular fa-chart-bar"></i> Orientation</h2>
                
                <?php if ($resultat): ?>
                    <?php 
                    $scores = json_decode($resultat['scores_domaines'], true);
                    $domaines_principaux = json_decode($resultat['domaines_principaux'], true);
                    $filieres = json_decode($resultat['filieres_proposees'], true);
                    $statut = $resultat['statut'];
                    ?>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                        <span style="font-size:14px;color:var(--gray);">Statut</span>
                        <span class="badge-statut <?= $statut ?>"><?= ucfirst(str_replace('_', ' ', $statut)) ?></span>
                    </div>

                    <?php if ($domaines_principaux): ?>
                        <?php foreach ($domaines_principaux as $domaine): ?>
                            <div class="resultat-item">
                                <div class="domaine">
                                    <span><?= htmlspecialchars($domaine['nom']) ?></span>
                                    <span><?= $domaine['score'] ?> pts</span>
                                </div>
                                <div class="barre">
                                    <div class="fill" style="width: <?= min(($domaine['score'] / 100) * 100, 100) ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ($filieres && $statut === 'publie'): ?>
                        <div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--border);">
                            <p style="font-size:13px;font-weight:600;color:var(--gray);margin-bottom:8px;">Filières recommandées</p>
                            <div>
                                <?php foreach ($filieres as $filiere): ?>
                                    <span class="tag"><?= htmlspecialchars($filiere) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($resultat['commentaire_admin'] && $statut === 'publie'): ?>
                        <div class="commentaire-admin">
                            <strong>Note de l'équipe</strong>
                            <p style="margin-top:4px;font-size:13px;"><?= htmlspecialchars($resultat['commentaire_admin']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($statut === 'en_attente'): ?>
                        <div style="margin-top:14px;padding:12px;background:#f8fafc;border-radius:10px;text-align:center;font-size:13px;color:var(--gray);">
                            <i class="fa-regular fa-clock"></i> En attente de validation
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="no-result">
                        <i class="fa-regular fa-file-lines"></i>
                        <p>Aucun quiz complété</p>
                        <a href="quiz.php" class="btn-action btn-primary" style="display:inline-block;padding:8px 20px;margin-top:10px;width:auto;text-decoration:none;">
                            Démarrer le quiz
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container footer-grid">
        <div>
            <h3>Mon Chemin</h3>
            <p>Votre partenaire pour une orientation scolaire moderne en Afrique de l'Ouest.</p>
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