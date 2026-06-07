<?php
// admin/validations.php

require_once '../php/db.php';
require_once '../php/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$statut_filtre = $_GET['statut'] ?? 'en_attente';

$sql = "
    SELECT qs.*, u.nom, u.prenom, u.email 
    FROM quiz_sessions qs
    JOIN utilisateurs u ON qs.utilisateur_id = u.id
";
if ($statut_filtre !== 'tous') {
    $sql .= " WHERE qs.statut_validation = :statut";
    $sql .= " ORDER BY qs.date_fin DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['statut' => $statut_filtre]);
} else {
    $sql .= " ORDER BY qs.date_fin DESC";
    $stmt = $pdo->query($sql);
}
$quiz_list = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Validations</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <aside class="admin-sidebar">
        <div class="sidebar-header">
            <i class="fa-solid fa-graduation-cap"></i>
            <h3>Mon Chemin</h3>
            <span>Administration</span>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a>
            <a href="validations.php" class="active"><i class="fa-solid fa-clock"></i> Validations</a>
            <a href="utilisateurs.php"><i class="fa-solid fa-users"></i> Utilisateurs</a>
            <a href="quiz_liste.php"><i class="fa-solid fa-list"></i> Tous les quiz</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../php/deconnexion.php" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fa-solid fa-clock"></i> Validations des quiz</h1>
        </div>

        <div class="filter-tabs">
            <a href="?statut=en_attente" class="filter-tab <?= $statut_filtre === 'en_attente' ? 'active' : '' ?>">En attente</a>
            <a href="?statut=valide" class="filter-tab <?= $statut_filtre === 'valide' ? 'active' : '' ?>">Validés</a>
            <a href="?statut=publie" class="filter-tab <?= $statut_filtre === 'publie' ? 'active' : '' ?>">Publiés</a>
            <a href="?statut=rejete" class="filter-tab <?= $statut_filtre === 'rejete' ? 'active' : '' ?>">Rejetés</a>
            <a href="?statut=tous" class="filter-tab <?= $statut_filtre === 'tous' ? 'active' : '' ?>">Tous</a>
        </div>

        <div class="admin-card">
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr><th>Élève</th><th>Email</th><th>Date</th><th>Statut</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quiz_list as $quiz): ?>
                        <tr>
                            <td><?= htmlspecialchars($quiz['prenom'] . ' ' . $quiz['nom']) ?></td>
                            <td><?= htmlspecialchars($quiz['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($quiz['date_fin'])) ?></td>
                            <td>
                                <?php
                                $status_class = '';
                                $status_text = '';
                                switch ($quiz['statut_validation']) {
                                    case 'en_attente': $status_class = 'status-warning'; $status_text = 'En attente'; break;
                                    case 'valide': $status_class = 'status-info'; $status_text = 'Validé'; break;
                                    case 'publie': $status_class = 'status-success'; $status_text = 'Publié'; break;
                                    case 'rejete': $status_class = 'status-danger'; $status_text = 'Rejeté'; break;
                                }
                                ?>
                                <span class="status <?= $status_class ?>"><?= $status_text ?></span>
                            </td>
                            <td><a href="quiz_detail.php?id=<?= $quiz['id'] ?>" class="btn-sm">Voir</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($quiz_list)): ?>
                        <tr><td colspan="5" class="text-center">Aucun quiz trouvé</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>