<?php
// admin/filiere_detail.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$filiere_id = $_GET['id'] ?? 0;

// Récupérer les informations de la filière
$stmt = $pdo->prepare("
    SELECT f.*, d.nom as domaine_nom
    FROM filieres f
    LEFT JOIN domaines d ON f.domaine_id = d.id
    WHERE f.id = ?
");
$stmt->execute([$filiere_id]);
$filiere = $stmt->fetch();

if (!$filiere) {
    header('Location: filieres_admin.php');
    exit();
}

// Récupérer les universités qui proposent cette filière
$stmt = $pdo->prepare("
    SELECT u.*
    FROM universites u
    JOIN universites_filieres uf ON u.id = uf.universite_id
    WHERE uf.filiere_id = ?
    ORDER BY u.pays, u.nom
");
$stmt->execute([$filiere_id]);
$universites = $stmt->fetchAll();

// Récupérer le nombre de quiz en attente pour le badge
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'en_attente'");
$en_attente_total = $stmt->fetchColumn();

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail filière - Administration</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .admin-wrap {
            display: flex;
            min-height: calc(100vh - 90px);
            background: #f8fafc;
        }

        .admin-sidebar {
            width: 220px;
            background: white;
            border-right: 1px solid var(--border);
            padding: 25px 0;
            flex-shrink: 0;
        }

        .admin-sidebar .sidebar-header {
            padding: 0 20px 18px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .admin-sidebar .sidebar-header h3 {
            font-size: 17px;
            font-weight: 700;
        }

        .admin-sidebar .sidebar-header p {
            font-size: 13px;
            color: var(--gray);
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            color: var(--text);
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
            border-left: 3px solid transparent;
        }

        .sidebar-nav a:hover {
            background: #f1f5f9;
            color: var(--primary);
        }

        .sidebar-nav a.active {
            background: #f1f5f9;
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .sidebar-nav a .badge {
            margin-left: auto;
            background: var(--primary);
            color: white;
            font-size: 11px;
            padding: 1px 8px;
            border-radius: 30px;
        }

        .admin-main {
            flex: 1;
            padding: 30px;
        }

        .admin-main .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .admin-main .header-bar h1 {
            font-size: 22px;
            font-weight: 700;
        }

        .admin-main .header-bar h1 i {
            color: var(--primary);
            margin-right: 8px;
        }

        .admin-main .header-bar .btn-back {
            padding: 8px 18px;
            background: var(--primary);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .admin-main .header-bar .btn-back:hover {
            background: var(--primary-dark);
        }

        .admin-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px 25px;
            margin-bottom: 20px;
        }

        .admin-card h2 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
        }

        .admin-card h2 i {
            color: var(--gray);
            margin-right: 8px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px 30px;
        }

        .info-grid .info-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }

        .info-grid .info-item .label {
            color: var(--gray);
        }

        .info-grid .info-item .value {
            font-weight: 500;
        }

        .badge-status {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        .badge-status.inactif {
            background: #f1f5f9;
            color: #94a3b8;
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

        .btn-sm {
            display: inline-block;
            padding: 4px 12px;
            background: var(--primary);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-sm:hover {
            background: var(--primary-dark);
        }

        .empty-state {
            text-align: center;
            padding: 35px;
            color: var(--gray);
            font-size: 14px;
        }

        .empty-state i {
            font-size: 28px;
            color: #e5e7eb;
            display: block;
            margin-bottom: 8px;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: 60px;
            }
            .admin-sidebar .sidebar-header h3,
            .admin-sidebar .sidebar-header p,
            .sidebar-nav a span {
                display: none;
            }
            .sidebar-nav a {
                justify-content: center;
                padding: 12px;
            }
            .admin-main {
                padding: 20px;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="admin-wrap">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <h3>Mon Chemin</h3>
            <p>Administration</p>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fa-solid fa-chart-line"></i> <span>Dashboard</span>
            </a>
            <a href="validations.php">
                <i class="fa-solid fa-clock"></i> <span>Validations</span>
                <?php if ($en_attente_total > 0): ?>
                    <span class="badge"><?= $en_attente_total ?></span>
                <?php endif; ?>
            </a>
            <a href="utilisateurs.php">
                <i class="fa-solid fa-users"></i> <span>Élèves</span>
            </a>
            <a href="universites_admin.php">
                <i class="fa-solid fa-building-columns"></i> <span>Universités</span>
            </a>
            <a href="filieres_admin.php" class="active">
                <i class="fa-solid fa-graduation-cap"></i> <span>Filières</span>
            </a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="header-bar">
            <h1><i class="fa-regular fa-graduation-cap"></i> Détail de la filière</h1>
            <a href="filieres_admin.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>

        <div class="admin-card">
            <h2><i class="fa-regular fa-circle-info"></i> Informations</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Nom</span>
                    <span class="value"><strong><?= htmlspecialchars($filiere['nom']) ?></strong></span>
                </div>
                <div class="info-item">
                    <span class="label">Domaine</span>
                    <span class="value"><?= htmlspecialchars($filiere['domaine_nom'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Durée des études</span>
                    <span class="value"><?= htmlspecialchars($filiere['duree_etudes'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Coût moyen</span>
                    <span class="value"><?= htmlspecialchars($filiere['cout_moyen'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="badge-status <?= $filiere['actif'] == 1 ? '' : 'inactif' ?>">
                            <?= $filiere['actif'] == 1 ? 'Actif' : 'Inactif' ?>
                        </span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="label">Description</span>
                    <span class="value"><?= htmlspecialchars($filiere['description'] ?? '—') ?></span>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2><i class="fa-regular fa-building-columns"></i> Universités qui proposent cette filière</h2>
            
            <?php if (empty($universites)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-check-circle"></i>
                    Aucune université ne propose cette filière
                </div>
            <?php else: ?>
                <div>
                    <?php foreach ($universites as $univ): ?>
                        <span class="univ-tag">
                            <?= htmlspecialchars($univ['nom']) ?>
                            <span style="font-weight:400;opacity:0.6;">(<?= htmlspecialchars($univ['pays']) ?>)</span>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>