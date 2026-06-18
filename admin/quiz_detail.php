<?php
// admin/quiz_detail.php

require_once '../php/db.php';
require_once '../php/auth.php';
require_once '../php/quiz_algorithm.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$session_id = $_GET['id'] ?? 0;
$resultat = getResultatsComplets($session_id, $pdo);

if (!$resultat) {
    header('Location: validations.php');
    exit();
}

$scores = $resultat['scores'];
$classement = classerDomaines($scores);
$top_domaines = array_keys(array_slice($classement, 0, 3, true));
$filieres_data = json_decode($resultat['filieres_proposees'], true) ?? [];

// Traitement des actions admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $commentaire = $_POST['commentaire'] ?? '';
    $filieres_modifiees = $_POST['filieres_modifiees'] ?? null;
    
    if ($filieres_modifiees && is_string($filieres_modifiees)) {
        $filieres_modifiees = explode(',', $filieres_modifiees);
        $filieres_modifiees = array_map('trim', $filieres_modifiees);
    }
    
    validerResultatAdmin($session_id, $_SESSION['user_id'], $action, $commentaire, $filieres_modifiees, $pdo);
    
    if ($action === 'publier' || $action === 'rejeter') {
        header('Location: validations.php');
    } else {
        header("Location: quiz_detail.php?id=$session_id&success=1");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du quiz - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }
        .admin-sidebar {
            width: 260px;
            background: white;
            border-right: 1px solid #e5e7eb;
            padding: 30px 0;
        }
        .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid #e5e7eb;
        }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: #374151;
            text-decoration: none;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: #eff6ff;
            color: #2563eb;
        }
        .admin-main {
            flex: 1;
            padding: 30px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .admin-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        .scores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .score-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 16px;
        }
        .score-item .label { font-weight: 600; }
        .score-item .value { color: #2563eb; font-size: 20px; font-weight: 700; }
        .filieres-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filiere-badge {
            background: #eff6ff;
            color: #2563eb;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 14px;
        }
        .validation-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        .btn-admin {
            padding: 10px 24px;
            border: none;
            border-radius: 30px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-validate { background: #10b981; color: white; }
        .btn-publish { background: #2563eb; color: white; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-modify { background: #f59e0b; color: white; }
        .btn-admin:hover { transform: translateY(-2px); }
        textarea { width: 100%; padding: 12px; border-radius: 12px; border: 1px solid #e5e7eb; font-family: inherit; }
        .status-badge {
            display: inline-block;
            padding: 4px 14px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 13px;
        }
        .status-en_attente { background: #fef3c7; color: #d97706; }
        .status-valide { background: #dbeafe; color: #2563eb; }
        .status-publie { background: #d1fae5; color: #059669; }
        .status-rejete { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>

<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h3><i class="fa-solid fa-graduation-cap"></i> Mon Chemin</h3>
            <p style="color:#6b7280;font-size:13px;">Espace admin</p>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="validations.php" class="active"><i class="fa-solid fa-clock"></i> Validations</a>
            <a href="utilisateurs.php"><i class="fa-solid fa-users"></i> Élèves</a>
            <a href="universites_admin.php"><i class="fa-solid fa-building-columns"></i> Universités</a>
            <a href="filieres_admin.php"><i class="fa-solid fa-graduation-cap"></i> Filières</a>
            <a href="questions_admin.php"><i class="fa-solid fa-question"></i> Questions</a>
            <a href="statistiques.php"><i class="fa-solid fa-chart-bar"></i> Statistiques</a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fa-solid fa-file-alt"></i> Détail du quiz</h1>
            <a href="validations.php" class="btn white-btn" style="text-decoration:none;">← Retour</a>
        </div>

        <!-- Infos élève -->
        <div class="admin-card">
            <h2 style="font-size:18px;margin-bottom:15px;"><i class="fa-solid fa-user"></i> Élève</h2>
            <p><strong><?= htmlspecialchars($resultat['prenom'] . ' ' . $resultat['nom']) ?></strong></p>
            <p style="color:#6b7280;"><?= htmlspecialchars($resultat['email']) ?></p>
            <p style="color:#6b7280;">Tél: <?= htmlspecialchars($resultat['telephone'] ?? 'Non renseigné') ?></p>
            <p style="margin-top:10px;">
                Statut: 
                <span class="status-badge status-<?= $resultat['statut_validation'] ?>">
                    <?= $resultat['statut_validation'] === 'en_attente' ? 'En attente' : ucfirst($resultat['statut_validation']) ?>
                </span>
            </p>
        </div>

        <!-- Scores -->
        <div class="admin-card">
            <h2 style="font-size:18px;margin-bottom:15px;"><i class="fa-solid fa-chart-simple"></i> Scores par domaine</h2>
            <div class="scores-grid">
                <?php foreach ($classement as $domaine => $score): ?>
                    <div class="score-item">
                        <div class="label"><?= ucfirst($domaine) ?></div>
                        <div class="value"><?= $score ?> pts</div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Proposition automatique -->
        <div class="admin-card">
            <h2 style="font-size:18px;margin-bottom:15px;"><i class="fa-solid fa-lightbulb"></i> Proposition automatique</h2>
            <div class="filieres-list">
                <?php 
                $filieres_affichees = $filieres_data ?: [];
                if (empty($filieres_affichees)) {
                    $domaine_principal = $top_domaines[0] ?? 'informatique';
                    $filieres_affichees = $domaine_filieres[$domaine_principal] ?? [];
                    $filieres_affichees = array_slice($filieres_affichees, 0, 3);
                }
                foreach ($filieres_affichees as $filiere): 
                ?>
                    <span class="filiere-badge"><?= htmlspecialchars($filiere) ?></span>
                <?php endforeach; ?>
            </div>
            
            <?php if ($resultat['statut_validation'] === 'en_attente' || $resultat['statut_validation'] === 'valide'): ?>
            <form method="POST" style="margin-top:20px;padding-top:20px;border-top:1px solid #e5e7eb;">
                <div style="margin-bottom:15px;">
                    <label style="font-weight:600;display:block;margin-bottom:8px;">Modifier les filières proposées</label>
                    <input type="text" name="filieres_modifiees" placeholder="Séparer par des virgules (ex: IA, Data Science, Cybersécurité)" 
                           style="width:100%;padding:12px;border-radius:12px;border:1px solid #e5e7eb;" 
                           value="<?= implode(', ', $filieres_affichees) ?>">
                </div>
                <div style="margin-bottom:15px;">
                    <label style="font-weight:600;display:block;margin-bottom:8px;">Commentaire (optionnel)</label>
                    <textarea name="commentaire" rows="3" placeholder="Ajouter un commentaire pour l'élève..."></textarea>
                </div>
                <div class="validation-actions">
                    <?php if ($resultat['statut_validation'] === 'en_attente'): ?>
                        <button type="submit" name="action" value="valider" class="btn-admin btn-validate"><i class="fa-solid fa-check"></i> Valider</button>
                        <button type="submit" name="action" value="modifier" class="btn-admin btn-modify"><i class="fa-solid fa-pen"></i> Modifier</button>
                        <button type="submit" name="action" value="rejeter" class="btn-admin btn-reject" onclick="return confirm('Confirmer le rejet ?')"><i class="fa-solid fa-times"></i> Rejeter</button>
                    <?php elseif ($resultat['statut_validation'] === 'valide'): ?>
                        <button type="submit" name="action" value="publier" class="btn-admin btn-publish"><i class="fa-solid fa-paper-plane"></i> Publier</button>
                        <button type="submit" name="action" value="rejeter" class="btn-admin btn-reject" onclick="return confirm('Confirmer le rejet ?')"><i class="fa-solid fa-times"></i> Rejeter</button>
                    <?php endif; ?>
                </div>
            </form>
            <?php endif; ?>
            
            <?php if ($resultat['statut_validation'] === 'publie'): ?>
                <div style="margin-top:15px;padding:15px;background:#d1fae5;border-radius:12px;color:#059669;">
                    <i class="fa-solid fa-check-circle"></i> Ce résultat a été publié. L'élève peut le consulter dans son profil.
                </div>
                <?php if ($resultat['commentaire_admin']): ?>
                    <div style="margin-top:15px;padding:15px;background:#f3f4f6;border-radius:12px;">
                        <strong>Commentaire admin:</strong> <?= htmlspecialchars($resultat['commentaire_admin']) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>