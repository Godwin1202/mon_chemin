<?php
// admin/universites_admin.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Récupérer le nombre de quiz en attente pour le badge
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'en_attente'");
$en_attente_total = $stmt->fetchColumn();

// Récupérer la liste des universités
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM universites_filieres uf WHERE uf.universite_id = u.id) as nb_filieres
        FROM universites u";

if (!empty($search)) {
    $sql .= " WHERE u.nom LIKE :search OR u.pays LIKE :search OR u.ville LIKE :search";
}
$sql .= " ORDER BY u.pays, u.nom";

$stmt = $pdo->prepare($sql);
if (!empty($search)) {
    $stmt->execute(['search' => '%' . $search . '%']);
} else {
    $stmt->execute();
}
$universites = $stmt->fetchAll();

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Universités - Administration</title>

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

        .admin-main .header-bar .btn-add {
            padding: 8px 18px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }

        .admin-main .header-bar .btn-add:hover {
            background: var(--primary-dark);
        }

        .search-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-bar input {
            flex: 1;
            max-width: 350px;
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .search-bar button {
            padding: 8px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: 0.2s;
        }

        .search-bar button:hover {
            background: var(--primary-dark);
        }

        .admin-table-wrap {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
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

        .badge-filieres {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
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

        .btn-sm-outline {
            display: inline-block;
            padding: 4px 12px;
            background: white;
            color: var(--gray);
            border: 1px solid var(--border);
            border-radius: 6px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: 0.2s;
        }

        .btn-sm-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
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
            .search-bar {
                flex-direction: column;
            }
            .search-bar input {
                max-width: 100%;
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
            <a href="universites_admin.php" class="active">
                <i class="fa-solid fa-building-columns"></i> <span>Universités</span>
            </a>
            <a href="filieres_admin.php">
                <i class="fa-solid fa-graduation-cap"></i> <span>Filières</span>
            </a>
        </nav>
    </aside>

    <main class="admin-main">
        <div class="header-bar">
            <h1><i class="fa-solid fa-building-columns"></i> Universités</h1>
            <a href="universite_ajouter.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Ajouter
            </a>
        </div>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Rechercher une université..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fa-solid fa-search"></i> Rechercher</button>
            <?php if (!empty($search)): ?>
                <a href="universites_admin.php" class="btn-sm-outline" style="padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-xmark"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Pays</th>
                        <th>Ville</th>
                        <th>Type</th>
                        <th>Filières</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($universites)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-building-columns"></i>
                                Aucune université trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($universites as $univ): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($univ['nom']) ?></strong></td>
                                <td><?= htmlspecialchars($univ['pays']) ?></td>
                                <td><?= htmlspecialchars($univ['ville'] ?? '—') ?></td>
                                <td>
                                    <span class="badge-status">
                                        <?= $univ['type_universite'] === 'publique' ? 'Publique' : 'Privée' ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge-filieres"><?= $univ['nb_filieres'] ?> filière<?= $univ['nb_filieres'] > 1 ? 's' : '' ?></span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="universite_detail.php?id=<?= $univ['id'] ?>" class="btn-sm">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                    <a href="universite_editer.php?id=<?= $univ['id'] ?>" class="btn-sm-outline">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>