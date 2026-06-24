<?php
// admin/filieres_admin.php

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

// Récupérer la liste des filières
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$domaine_filter = isset($_GET['domaine']) ? (int)$_GET['domaine'] : 0;

$sql = "
    SELECT f.*, d.nom as domaine_nom,
           (SELECT COUNT(*) FROM universites_filieres uf WHERE uf.filiere_id = f.id) as nb_universites
    FROM filieres f
    LEFT JOIN domaines d ON f.domaine_id = d.id
    WHERE 1=1
";

$params = [];
if (!empty($search)) {
    $sql .= " AND (f.nom LIKE :search)";
    $params['search'] = '%' . $search . '%';
}
if ($domaine_filter > 0) {
    $sql .= " AND f.domaine_id = :domaine_id";
    $params['domaine_id'] = $domaine_filter;
}

$sql .= " ORDER BY d.nom, f.nom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$filieres = $stmt->fetchAll();

// Récupérer les domaines pour le filtre
$stmt = $pdo->query("SELECT id, nom FROM domaines ORDER BY nom");
$domaines = $stmt->fetchAll();

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filières - Administration</title>

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
            max-width: 300px;
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

        .search-bar select {
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            background: white;
            min-width: 180px;
        }

        .search-bar select:focus {
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

        .badge-domain {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        .badge-univ {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 600;
            background: #f1f5f9;
            color: #64748b;
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
            .search-bar select {
                width: 100%;
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
            <h1><i class="fa-solid fa-graduation-cap"></i> Filières</h1>
            <a href="filiere_ajouter.php" class="btn-add">
                <i class="fa-solid fa-plus"></i> Ajouter
            </a>
        </div>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Rechercher une filière..." value="<?= htmlspecialchars($search) ?>">
            <select name="domaine">
                <option value="0">Tous les domaines</option>
                <?php foreach ($domaines as $domaine): ?>
                    <option value="<?= $domaine['id'] ?>" <?= $domaine_filter == $domaine['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($domaine['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="fa-solid fa-search"></i> Rechercher</button>
            <?php if (!empty($search) || $domaine_filter > 0): ?>
                <a href="filieres_admin.php" class="btn-sm-outline" style="padding:8px 16px;display:inline-flex;align-items:center;gap:6px;">
                    <i class="fa-solid fa-xmark"></i> Réinitialiser
                </a>
            <?php endif; ?>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Filière</th>
                        <th>Domaine</th>
                        <th>Durée</th>
                        <th>Universités</th>
                        <th>Statut</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($filieres)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="fa-solid fa-graduation-cap"></i>
                                Aucune filière trouvée
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($filieres as $filiere): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($filiere['nom']) ?></strong></td>
                                <td>
                                    <span class="badge-domain"><?= htmlspecialchars($filiere['domaine_nom'] ?? 'Non défini') ?></span>
                                </td>
                                <td><?= htmlspecialchars($filiere['duree_etudes'] ?? '—') ?></td>
                                <td>
                                    <span class="badge-univ"><?= $filiere['nb_universites'] ?> université<?= $filiere['nb_universites'] > 1 ? 's' : '' ?></span>
                                </td>
                                <td>
                                    <span class="badge-status <?= $filiere['actif'] == 1 ? '' : 'inactif' ?>">
                                        <?= $filiere['actif'] == 1 ? 'Actif' : 'Inactif' ?>
                                    </span>
                                </td>
                                <td style="text-align:right;">
                                    <a href="filiere_detail.php?id=<?= $filiere['id'] ?>" class="btn-sm">
                                        <i class="fa-solid fa-eye"></i> Voir
                                    </a>
                                    <a href="filiere_editer.php?id=<?= $filiere['id'] ?>" class="btn-sm-outline">
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