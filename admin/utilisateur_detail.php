<?php
// admin/utilisateur_detail.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$user_id = $_GET['id'] ?? 0;

// Récupérer les informations de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: utilisateurs.php');
    exit();
}

// Récupérer les quiz de l'utilisateur
$stmt = $pdo->prepare("
    SELECT qr.*, qs.date_debut 
    FROM quiz_resultats qr
    JOIN quiz_sessions qs ON qr.session_id = qs.id
    WHERE qr.utilisateur_id = ?
    ORDER BY qr.date_creation DESC
");
$stmt->execute([$user_id]);
$quiz_list = $stmt->fetchAll();

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
    <title>Détail de l'élève - Administration</title>

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

        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        .status-badge.en_attente { background: #fef3c7; color: #92400e; }
        .status-badge.valide { background: #dbeafe; color: #1d4ed8; }
        .status-badge.publie { background: #d1fae5; color: #065f46; }
        .status-badge.rejete { background: #fee2e2; color: #991b1b; }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .admin-table th {
            text-align: left;
            padding: 10px 18px;
            background: #f8fafc;
            font-weight: 600;
            color: var(--gray);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .admin-table td {
            padding: 10px 18px;
            border-top: 1px solid #f1f5f9;
        }

        .admin-table tr:hover td {
            background: #fafafa;
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
    <!-- Sidebar -->
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
            <a href="utilisateurs.php" class="active">
                <i class="fa-solid fa-users"></i> <span>Élèves</span>
            </a>
            <a href="universites_admin.php">
                <i class="fa-solid fa-building-columns"></i> <span>Universités</span>
            </a>
            <a href="filieres_admin.php">
                <i class="fa-solid fa-graduation-cap"></i> <span>Filières</span>
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="admin-main">
        <div class="header-bar">
            <h1><i class="fa-regular fa-user"></i> Détail de l'élève</h1>
            <a href="utilisateurs.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>

        <!-- Informations -->
        <div class="admin-card">
            <h2><i class="fa-regular fa-circle-user"></i> Informations personnelles</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Nom complet</span>
                    <span class="value"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Email</span>
                    <span class="value"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Téléphone</span>
                    <span class="value"><?= htmlspecialchars($user['telephone'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Niveau</span>
                    <span class="value"><?= htmlspecialchars($user['niveau'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Handicap</span>
                    <span class="value"><?= $user['situation_handicap'] ? 'Oui' : 'Non' ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Date d'inscription</span>
                    <span class="value"><?= date('d/m/Y', strtotime($user['date_creation'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="status-badge <?= $user['actif'] == 1 ? '' : 'inactif' ?>">
                            <?= $user['actif'] == 1 ? 'Actif' : 'Inactif' ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quiz de l'élève -->
        <div class="admin-card">
            <h2><i class="fa-regular fa-file-lines"></i> Quiz de l'élève</h2>
            
            <?php if (empty($quiz_list)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-check-circle"></i>
                    Aucun quiz trouvé pour cet élève
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Statut</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quiz_list as $quiz): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($quiz['date_creation'])) ?></td>
                                <td>
                                    <span class="status-badge <?= $quiz['statut'] ?>">
                                        <?= ucfirst(str_replace('_', ' ', $quiz['statut'])) ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="quiz_detail.php?id=<?= $quiz['id'] ?>" class="btn-sm">
                                        <i class="fa-regular fa-eye"></i> Voir
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>