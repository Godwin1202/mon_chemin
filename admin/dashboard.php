<?php
// admin/dashboard.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Statistiques
$stats = [];

// Nombre total d'élèves
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'eleve'");
$stats['total_eleves'] = $stmt->fetchColumn();

// Quiz en attente (depuis quiz_resultats)
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'en_attente'");
$stats['en_attente'] = $stmt->fetchColumn();

// Quiz validés
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'valide'");
$stats['valides'] = $stmt->fetchColumn();

// Quiz publiés
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'publie'");
$stats['publies'] = $stmt->fetchColumn();

// Quiz rejetés
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'rejete'");
$stats['rejetes'] = $stmt->fetchColumn();

// Total quiz
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats");
$stats['total_quiz'] = $stmt->fetchColumn();

// Récupérer les quiz en attente
$stmt = $pdo->query("
    SELECT qr.*, u.nom, u.prenom, u.email 
    FROM quiz_resultats qr
    JOIN utilisateurs u ON qr.utilisateur_id = u.id
    WHERE qr.statut = 'en_attente'
    ORDER BY qr.date_creation DESC
    LIMIT 10
");
$quiz_attente = $stmt->fetchAll();

// Récupérer les derniers quiz traités
$stmt = $pdo->query("
    SELECT qr.*, u.nom, u.prenom, u.email 
    FROM quiz_resultats qr
    JOIN utilisateurs u ON qr.utilisateur_id = u.id
    WHERE qr.statut IN ('valide', 'publie')
    ORDER BY qr.date_validation DESC
    LIMIT 5
");
$quiz_recents = $stmt->fetchAll();

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Administration</title>

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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 16px;
        }

        .stat-card .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: var(--gray);
            margin-top: 2px;
        }

        .admin-table-wrap {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            margin-top: 20px;
        }

        .admin-table-wrap .table-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-table-wrap .table-header h2 {
            font-size: 15px;
            font-weight: 700;
        }

        .admin-table-wrap .table-header a {
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
        }

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

        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
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

        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
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
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 500px) {
            .stats-grid {
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
            <a href="dashboard.php" class="active">
                <i class="fa-solid fa-chart-line"></i> <span>Dashboard</span>
            </a>
            <a href="validations.php">
                <i class="fa-solid fa-clock"></i> <span>Validations</span>
                <?php if ($stats['en_attente'] > 0): ?>
                    <span class="badge"><?= $stats['en_attente'] ?></span>
                <?php endif; ?>
            </a>
            <a href="utilisateurs.php">
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
            <h1><i class="fa-regular fa-chart-bar"></i> Tableau de bord</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_eleves'] ?></div>
                <div class="stat-label">Élèves inscrits</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['en_attente'] ?></div>
                <div class="stat-label">En attente</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['publies'] ?></div>
                <div class="stat-label">Publiés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['valides'] ?></div>
                <div class="stat-label">Validés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['rejetes'] ?></div>
                <div class="stat-label">Rejetés</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['total_quiz'] ?></div>
                <div class="stat-label">Quiz total</div>
            </div>
        </div>

        <!-- Quiz en attente -->
        <div class="admin-table-wrap">
            <div class="table-header">
                <h2><i class="fa-regular fa-clock" style="color:var(--gray);margin-right:6px;"></i> Quiz en attente</h2>
                <a href="validations.php?statut=en_attente">Voir tout →</a>
            </div>
            <?php if (empty($quiz_attente)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-check-circle"></i>
                    Aucun quiz en attente
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quiz_attente as $quiz): ?>
                        <tr>
                            <td><?= htmlspecialchars($quiz['prenom'] . ' ' . $quiz['nom']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($quiz['date_creation'])) ?></td>
                            <td><span class="status-badge">En attente</span></td>
                            <td style="text-align:right;">
                                <a href="quiz_detail.php?id=<?= $quiz['id'] ?>" class="btn-sm">Analyser</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Derniers quiz traités -->
        <div class="admin-table-wrap" style="margin-top:20px;">
            <div class="table-header">
                <h2><i class="fa-regular fa-check-circle" style="color:var(--gray);margin-right:6px;"></i> Derniers quiz traités</h2>
            </div>
            <?php if (empty($quiz_recents)): ?>
                <div class="empty-state">
                    <i class="fa-regular fa-file-lines"></i>
                    Aucun quiz traité
                </div>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Élève</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th style="text-align:right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quiz_recents as $quiz): ?>
                        <tr>
                            <td><?= htmlspecialchars($quiz['prenom'] . ' ' . $quiz['nom']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($quiz['date_validation'] ?? $quiz['date_creation'])) ?></td>
                            <td><span class="status-badge"><?= $quiz['statut'] === 'publie' ? 'Publié' : 'Validé' ?></span></td>
                            <td style="text-align:right;">
                                <a href="quiz_detail.php?id=<?= $quiz['id'] ?>" class="btn-sm">Voir</a>
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