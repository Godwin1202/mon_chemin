<?php
// admin/quiz_detail.php

require_once '../php/db.php';
require_once '../php/auth.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$quiz_id = $_GET['id'] ?? 0;

// Récupérer le quiz
$stmt = $pdo->prepare("
    SELECT qs.*, u.nom, u.prenom, u.email, u.telephone, u.niveau, u.situation_handicap
    FROM quiz_sessions qs
    JOIN utilisateurs u ON qs.utilisateur_id = u.id
    WHERE qs.id = ?
");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    header('Location: validations.php');
    exit();
}

// Récupérer les scores
$scores = json_decode($quiz['scores'], true);
$recommandations = json_decode($quiz['filieres_proposees'], true);

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $commentaire = $_POST['commentaire'] ?? '';
    
    if ($action === 'valider') {
        $stmt = $pdo->prepare("UPDATE quiz_sessions SET statut_validation = 'valide', date_validation = NOW(), admin_id = ?, commentaire_admin = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $commentaire, $quiz_id]);
        header("Location: quiz_detail.php?id=$quiz_id&success=valide");
        exit();
    }
    
    if ($action === 'publier') {
        $stmt = $pdo->prepare("UPDATE quiz_sessions SET statut_validation = 'publie', date_validation = NOW(), admin_id = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $quiz_id]);
        header("Location: validations.php");
        exit();
    }
    
    if ($action === 'rejeter') {
        $stmt = $pdo->prepare("UPDATE quiz_sessions SET statut_validation = 'rejete', date_validation = NOW(), admin_id = ?, commentaire_admin = ? WHERE id = ?");
        $stmt->execute([$_SESSION['user_id'], $commentaire, $quiz_id]);
        header("Location: validations.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Détail du quiz</title>
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
        </nav>
        <div class="sidebar-footer">
            <a href="../php/deconnexion.php" class="logout-link"><i class="fa-solid fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1><i class="fa-solid fa-file-alt"></i> Détail du quiz</h1>
            <a href="validations.php" class="btn-back">← Retour</a>
        </div>

        <!-- Infos élève -->
        <div class="admin-card">
            <h2>Informations de l'élève</h2>
            <div class="info-grid">
                <div><label>Nom</label><p><?= htmlspecialchars($quiz['prenom'] . ' ' . $quiz['nom']) ?></p></div>
                <div><label>Email</label><p><?= htmlspecialchars($quiz['email']) ?></p></div>
                <div><label>Téléphone</label><p><?= htmlspecialchars($quiz['telephone'] ?? 'Non renseigné') ?></p></div>
                <div><label>Niveau</label><p><?= htmlspecialchars($quiz['niveau'] ?? 'Non renseigné') ?></p></div>
                <div><label>Situation handicap</label><p><?= $quiz['situation_handicap'] ? 'Oui' : 'Non' ?></p></div>
                <div><label>Date du quiz</label><p><?= date('d/m/Y H:i', strtotime($quiz['date_fin'])) ?></p></div>
            </div>
        </div>

        <!-- Scores -->
        <div class="admin-card">
            <h2>Scores par domaine</h2>
            <div class="scores-container">
                <?php if ($scores): ?>
                    <?php $max_score = max($scores); ?>
                    <?php foreach ($scores as $domaine => $score): ?>
                        <div class="score-item">
                            <div class="score-label"><span><?= ucfirst($domaine) ?></span><span><?= $score ?> pts</span></div>
                            <div class="score-bar"><div class="score-fill" style="width: <?= $max_score > 0 ? ($score / $max_score) * 100 : 0 ?>%"></div></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Scores non disponibles</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filières proposées -->
        <div class="admin-card">
            <h2>Filières proposées</h2>
            <div class="propositions-list">
                <?php if ($recommandations): ?>
                    <?php $i = 1; foreach ($recommandations as $domaine => $score): ?>
                        <div class="proposition-item">
                            <div class="proposition-rank">#<?= $i++ ?></div>
                            <div><strong><?= ucfirst($domaine) ?></strong> - Score : <?= $score ?> pts</div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune proposition</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="admin-card">
            <h2>Validation du quiz</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Commentaire (optionnel)</label>
                    <textarea name="commentaire" rows="3" placeholder="Ajouter une note..."></textarea>
                </div>
                
                <?php if ($quiz['statut_validation'] === 'en_attente'): ?>
                    <div class="validation-actions">
                        <button type="submit" name="action" value="valider" class="btn-success">✓ Valider</button>
                        <button type="submit" name="action" value="rejeter" class="btn-danger" onclick="return confirm('Confirmer le rejet ?')">✗ Rejeter</button>
                    </div>
                <?php elseif ($quiz['statut_validation'] === 'valide'): ?>
                    <button type="submit" name="action" value="publier" class="btn-primary">📢 Publier les résultats</button>
                <?php elseif ($quiz['statut_validation'] === 'publie'): ?>
                    <div class="alert-success">✓ Ce quiz a été publié</div>
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>

</body>
</html>