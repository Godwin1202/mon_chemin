<?php
// admin/universite_editer.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$univ_id = $_GET['id'] ?? 0;

// Récupérer les informations de l'université
$stmt = $pdo->prepare("SELECT * FROM universites WHERE id = ?");
$stmt->execute([$univ_id]);
$universite = $stmt->fetch();

if (!$universite) {
    header('Location: universites_admin.php');
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $pays = trim($_POST['pays'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $type = $_POST['type_universite'] ?? 'publique';
    $site_web = trim($_POST['site_web'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (empty($nom) || empty($pays)) {
        $message = "Le nom et le pays sont obligatoires.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE universites 
            SET nom = ?, pays = ?, ville = ?, type_universite = ?, site_web = ?, description = ?, actif = ?
            WHERE id = ?
        ");
        $stmt->execute([$nom, $pays, $ville, $type, $site_web, $description, $actif, $univ_id]);
        $message = "Université mise à jour avec succès !";
        
        // Recharger les données
        $stmt = $pdo->prepare("SELECT * FROM universites WHERE id = ?");
        $stmt->execute([$univ_id]);
        $universite = $stmt->fetch();
    }
}

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
    <title>Éditer université - Administration</title>

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

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
            color: #334155;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            transition: 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }

        .checkbox-group input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .btn-save {
            padding: 12px 30px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
            transition: 0.2s;
        }

        .btn-save:hover {
            background: var(--primary-dark);
        }

        .alert-success {
            padding: 12px 16px;
            background: var(--primary-light);
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            color: var(--primary);
            font-size: 14px;
            margin-bottom: 20px;
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
            .form-row {
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
            <h1><i class="fa-regular fa-pen-to-square"></i> Éditer l'université</h1>
            <a href="universites_admin.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>

        <?php if ($message): ?>
            <div class="alert-success">
                <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="admin-card">
            <form method="POST">
                <div class="form-group">
                    <label>Nom de l'université *</label>
                    <input type="text" name="nom" required value="<?= htmlspecialchars($universite['nom']) ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Pays *</label>
                        <input type="text" name="pays" required value="<?= htmlspecialchars($universite['pays']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Ville</label>
                        <input type="text" name="ville" value="<?= htmlspecialchars($universite['ville'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Type d'université</label>
                        <select name="type_universite">
                            <option value="publique" <?= $universite['type_universite'] === 'publique' ? 'selected' : '' ?>>Publique</option>
                            <option value="privee" <?= $universite['type_universite'] === 'privee' ? 'selected' : '' ?>>Privée</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Site web</label>
                        <input type="url" name="site_web" value="<?= htmlspecialchars($universite['site_web'] ?? '') ?>" placeholder="https://www.exemple.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3"><?= htmlspecialchars($universite['description'] ?? '') ?></textarea>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" name="actif" id="actif" value="1" <?= $universite['actif'] == 1 ? 'checked' : '' ?>>
                    <label for="actif">Actif</label>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fa-solid fa-save"></i> Enregistrer les modifications
                </button>
            </form>
        </div>
    </main>
</div>

</body>
</html>