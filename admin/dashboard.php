<?php
// admin/dashboard.php

require_once '../php/db.php';
require_once '../php/auth.php';

// Vérifier que l'utilisateur est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Statistiques
$stmt = $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'eleve'");
$stats['total_eleves'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_sessions WHERE statut_validation = 'en_attente'");
$stats['en_attente'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_sessions WHERE statut_validation IN ('valide', 'publie')");
$stats['traites'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_sessions");
$stats['total_quiz'] = $stmt->fetchColumn();

// Récupérer les quiz en attente
$stmt = $pdo->query("
    SELECT qs.*, u.nom, u.prenom, u.email 
    FROM quiz_sessions qs
    JOIN utilisateurs u ON qs.utilisateur_id = u.id
    WHERE qs.statut_validation = 'en_attente'
    ORDER BY qs.date_fin DESC
    LIMIT 10
");
$quiz_attente = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tableau de bord</title>

    <link rel="stylesheet" href="../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Styles spécifiques à l'admin */
        .admin-wrapper {
            display: flex;
            min-height: calc(100vh - 90px);
        }
        
        .admin-sidebar {
            width: 280px;
            background: white;
            border-right: 1px solid var(--border);
            padding: 30px 0;
        }
        
        .admin-sidebar .sidebar-header {
            padding: 0 25px 25px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        
        .admin-sidebar .sidebar-header h3 {
            font-size: 20px;
            margin: 10px 0 5px;
        }
        
        .admin-sidebar .sidebar-header p {
            color: var(--gray);
            font-size: 13px;
        }
        
        .sidebar-nav {
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 25px;
            color: var(--text);
            text-decoration: none;
            transition: 0.2s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-nav a:hover {
            background: var(--primary-light);
            color: var(--primary);
        }
        
        .sidebar-nav a.active {
            background: var(--primary-light);
            color: var(--primary);
            border-left-color: var(--primary);
        }
        
        .sidebar-nav a i {
            width: 22px;
        }
        
        .sidebar-nav a .badge {
            background: var(--primary);
            color: white;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 11px;
            margin-left: auto;
        }
        
        .admin-main {
            flex: 1;
            padding: 30px;
            background: #f8fafc;
        }
        
        .admin-header {
            margin-bottom: 30px;
        }
        
        .admin-header h1 {
            font-size: 28px;
            font-weight: 700;
        }
        
        .admin-header h1 i {
            color: var(--primary);
            margin-right: 12px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            transition: 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        
        .stat-icon.blue { background: #eff6ff; color: var(--primary); }
        .stat-icon.orange { background: #fff7ed; color: #f97316; }
        .stat-icon.green { background: #ecfdf5; color: #10b981; }
        .stat-icon.purple { background: #f5f3ff; color: #8b5cf6; }
        
        .stat-info h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: var(--gray);
            font-size: 14px;
        }
        
        .admin-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border);
        }
        
        .card-header h2 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-header h2 i {
            color: var(--primary);
            margin-right: 8px;
        }
        
        .btn-link {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .admin-table th {
            background: #f8fafc;
            font-weight: 600;
            color: var(--text);
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-warning { background: #fef3c7; color: #d97706; }
        .status-success { background: #d1fae5; color: #059669; }
        .status-info { background: #dbeafe; color: var(--primary); }
        .status-danger { background: #fee2e2; color: #dc2626; }
        
        .btn-sm {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: var(--primary);
            color: white;
            border-radius: 30px;
            text-decoration: none;
            font-size: 13px;
            transition: 0.2s;
        }
        
        .btn-sm:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 60px;
            margin-bottom: 15px;
            color: var(--border);
        }
        
        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .admin-sidebar {
                width: 80px;
            }
            .admin-sidebar .sidebar-header h3,
            .admin-sidebar .sidebar-header p,
            .sidebar-nav a span {
                display: none;
            }
            .sidebar-nav a {
                justify-content: center;
                padding: 15px;
            }
            .admin-main {
                margin-left: 0;
            }
        }
        
        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="container header-content">
        <div class="logo">
            <a href="../index.php" style="display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit;">
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <h2>Mon Chemin</h2>
                    <p>Espace administration</p>
                </div>
            </a>
        </div>

        <nav class="navbar">
            <a href="../index.php">Accueil</a>
            <a href="../pages/quiz.php">Quiz</a>
            <a href="../pages/universites.php">Universités</a>
            <a href="../pages/conseils.php">Conseils</a>
            <a href="../pages/apropos.php">À propos</a>
        </nav>

        <div class="header-buttons">
            <div class="user-menu">
                <div class="user-info">
                    <i class="fa-solid fa-user-shield"></i>
                    <span class="user-name"><?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?></span>
                    <span class="badge-admin">Admin</span>
                </div>
                <a href="../php/deconnexion.php" class="btn logout-btn">
                    <i class="fa-solid fa-sign-out-alt"></i> <span>Déconnexion</span>
                </a>
            </div>
        </div>
    </div>
</header>

<!-- ADMIN CONTENT -->
<div class="admin-wrapper">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-gauge-high" style="font-size: 32px; color: var(--primary);"></i>
            <h3>Administration</h3>
            <p>Gestion du site</p>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active">
                <i class="fa-solid fa-chart-line"></i>
                <span>Tableau de bord</span>
            </a>
            <a href="validations.php">
                <i class="fa-solid fa-clock"></i>
                <span>Validations</span>
                <?php if ($stats['en_attente'] > 0): ?>
                    <span class="badge"><?= $stats['en_attente'] ?></span>
                <?php endif; ?>
            </a>
            <a href="utilisateurs.php">
                <i class="fa-solid fa-users"></i>
                <span>Utilisateurs</span>
            </a>
            <a href="quiz_liste.php">
                <i class="fa-solid fa-list"></i>
                <span>Tous les quiz</span>
            </a>
        </nav>
    </aside>

    <!-- Main content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fa-solid fa-chart-line"></i> Tableau de bord</h1>
        </div>

        <!-- Stats cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_eleves'] ?></h3>
                    <p>Élèves inscrits</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['en_attente'] ?></h3>
                    <p>Quiz en attente</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['traites'] ?></h3>
                    <p>Quiz traités</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?= $stats['total_quiz'] ?></h3>
                    <p>Quiz total</p>
                </div>
            </div>
        </div>

        <!-- Quiz en attente -->
        <div class="admin-card">
            <div class="card-header">
                <h2><i class="fa-solid fa-clock"></i> Quiz en attente de validation</h2>
                <a href="validations.php" class="btn-link">Voir tout →</a>
            </div>
            
            <?php if (empty($quiz_attente)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-check-circle"></i>
                    <p>Aucun quiz en attente</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr><th>Élève</th><th>Email</th><th>Date</th><th>Statut</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quiz_attente as $quiz): ?>
                            <tr>
                                <td><?= htmlspecialchars($quiz['prenom'] . ' ' . $quiz['nom']) ?></td>
                                <td><?= htmlspecialchars($quiz['email']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($quiz['date_fin'])) ?></td>
                                <td><span class="status status-warning">En attente</span></td>
                                <td><a href="quiz_detail.php?id=<?= $quiz['id'] ?>" class="btn-sm"><i class="fa-solid fa-eye"></i> Analyser</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- FOOTER -->
<footer>
    <div class="container footer-grid">
        <div>
            <h3>Mon Chemin</h3>
            <p>Votre partenaire pour une orientation scolaire moderne.</p>
        </div>
        <div>
            <h4>Liens utiles</h4>
            <a href="../pages/quiz.php">Quiz</a>
            <a href="../pages/universites.php">Universités</a>
            <a href="../pages/conseils.php">Conseils</a>
        </div>
        <div>
            <h4>Informations</h4>
            <a href="#">Contact</a>
            <a href="#">Mentions légales</a>
        </div>
        <div>
            <h4>Administration</h4>
            <a href="dashboard.php">Tableau de bord</a>
            <a href="validations.php">Validations</a>
        </div>
    </div>
</footer>

</body>
</html>