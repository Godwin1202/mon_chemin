<?php
// admin/quiz_detail.php

session_start();
require_once '../php/db.php';
require_once '../php/auth.php';
require_once '../php/quiz_algorithm.php';
require_once '../php/generate_pdf.php';
require_once '../php/notifications.php';
require_once '../php/envoi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

$quiz_id = $_GET['id'] ?? 0;

// Récupérer les infos du quiz depuis quiz_resultats
$stmt = $pdo->prepare("
    SELECT qr.*, u.nom, u.prenom, u.email, u.telephone, u.parent_email, u.parent_whatsapp
    FROM quiz_resultats qr
    JOIN utilisateurs u ON qr.utilisateur_id = u.id
    WHERE qr.id = ?
");
$stmt->execute([$quiz_id]);
$resultat = $stmt->fetch();

if (!$resultat) {
    header('Location: validations.php');
    exit();
}

// Décoder les JSON
$scores = json_decode($resultat['scores_domaines'], true);
$domaines_principaux = json_decode($resultat['domaines_principaux'], true);
$filieres_data = json_decode($resultat['filieres_proposees'], true);
$universites_data = json_decode($resultat['universites_proposees'], true);

// Récupérer le nombre de quiz en attente pour le badge
$stmt = $pdo->query("SELECT COUNT(*) FROM quiz_resultats WHERE statut = 'en_attente'");
$en_attente_total = $stmt->fetchColumn();

// Traitement des actions admin
$message_success = '';
$message_error = '';
$show_pdf_link = false;
$pdf_path = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $commentaire = $_POST['commentaire'] ?? '';
    $filieres_modifiees = $_POST['filieres_modifiees'] ?? null;
    
    if ($filieres_modifiees && is_string($filieres_modifiees)) {
        $filieres_modifiees = explode(',', $filieres_modifiees);
        $filieres_modifiees = array_map('trim', $filieres_modifiees);
        $filieres_modifiees = array_filter($filieres_modifiees);
    }
    
    $nouveau_statut = 'en_attente';
    switch ($action) {
        case 'valider':
            $nouveau_statut = 'valide';
            break;
        case 'publier':
            $nouveau_statut = 'publie';
            break;
        case 'rejeter':
            $nouveau_statut = 'rejete';
            break;
        case 'modifier':
            $nouveau_statut = 'valide';
            break;
    }
    
    try {
        // Mettre à jour le statut
        $stmt = $pdo->prepare("
            UPDATE quiz_resultats 
            SET statut = ?, 
                date_validation = NOW(), 
                admin_id = ?, 
                commentaire_admin = ?
            WHERE id = ?
        ");
        $stmt->execute([$nouveau_statut, $_SESSION['user_id'], $commentaire, $quiz_id]);
        
        // Si modification des filières
        if ($filieres_modifiees && !empty($filieres_modifiees)) {
            $stmt = $pdo->prepare("
                UPDATE quiz_resultats 
                SET filieres_proposees = ?
                WHERE id = ?
            ");
            $stmt->execute([json_encode($filieres_modifiees), $quiz_id]);
        }
        
        // Si publication, générer PDF et notifications
        if ($action === 'publier') {
            // 1. Générer le PDF
            $pdf_path = genererPDF($quiz_id, $pdo);
            
            if ($pdf_path) {
                $message_success = ' Quiz publié avec succès ! ';
                
                // 2. Ajouter notification pour l'élève
                $notif_result = ajouterNotification(
                    $resultat['utilisateur_id'], 
                    'Résultat disponible', 
                    'Votre orientation a été validée ! Consultez votre profil pour voir les résultats.', 
                    $pdo
                );
                
                if ($notif_result) {
                    $message_success .= 'Notification envoyée. ';
                }
                
                // 3. Envoyer email au parent
                $email_result = envoyerEmailParent($resultat['utilisateur_id'], $pdf_path, $pdo);
                if ($email_result) {
                    $message_success .= 'Email envoyé. ';
                } else {
                    $message_success .= 'Aucun email parent. ';
                }
                
                // 4. Envoyer WhatsApp au parent
                $whatsapp_link = envoyerWhatsAppParent($resultat['utilisateur_id'], $pdf_path, $pdo);
                if ($whatsapp_link) {
                    $message_success .= 'WhatsApp envoyé.';
                } else {
                    $message_success .= 'Aucun WhatsApp parent.';
                }
                
                $show_pdf_link = true;
            } else {
                $message_error = 'Erreur lors de la génération du PDF.';
            }
        }
        
        if ($action === 'valider') {
            $message_success = 'Quiz validé avec succès !';
        }
        
        if ($action === 'modifier') {
            $message_success = 'Filières modifiées avec succès !';
        }
        
        if ($action === 'rejeter') {
            $message_success = 'Quiz rejeté.';
        }
        
        // Redirection après succès
        if ($action === 'publier' || $action === 'rejeter') {
            header('Location: validations.php?success=' . urlencode($message_success));
        } else {
            header("Location: quiz_detail.php?id=$quiz_id&success=" . urlencode($message_success));
        }
        exit();
        
    } catch (Exception $e) {
        $message_error = 'Erreur : ' . $e->getMessage();
    }
}

$page_prefix = '../';
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail du quiz - Administration</title>

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

        .alert-success {
            padding: 12px 16px;
            background: var(--primary-light);
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            color: var(--primary);
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-success i {
            margin-right: 8px;
        }

        .alert-error {
            padding: 12px 16px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #991b1b;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .alert-error i {
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

        .scores-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
        }

        .score-item {
            background: #f8fafc;
            padding: 12px 16px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .score-item .label {
            font-weight: 500;
            font-size: 14px;
        }

        .score-item .value {
            font-weight: 700;
            color: var(--primary);
            font-size: 18px;
        }

        .filieres-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .filiere-tag {
            background: var(--primary-light);
            color: var(--primary);
            padding: 4px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            background: var(--primary-light);
            color: var(--primary);
        }

        .btn-admin {
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            transition: 0.2s;
            color: white;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
        }

        .btn-validate { background: var(--primary); }
        .btn-publish { background: var(--primary); }
        .btn-reject { background: #ef4444; }
        .btn-modify { background: #f59e0b; }

        .validation-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        textarea, input[type="text"] {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            transition: 0.2s;
        }

        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
        }

        .comment-block {
            margin-top: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 14px;
            color: #475569;
        }

        .comment-block strong {
            color: #334155;
        }

        .parent-info {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }

        .parent-info span {
            font-size: 13px;
            color: var(--gray);
        }

        .parent-info strong {
            color: var(--text);
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
            .scores-grid {
                grid-template-columns: 1fr 1fr;
            }
            .parent-info {
                flex-direction: column;
                gap: 6px;
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
            <a href="validations.php" class="active">
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
            <a href="filieres_admin.php">
                <i class="fa-solid fa-graduation-cap"></i> <span>Filières</span>
            </a>
        </nav>
    </aside>

    <!-- Main -->
    <main class="admin-main">
        <div class="header-bar">
            <h1><i class="fa-solid fa-file-lines"></i> Détail du quiz</h1>
            <a href="validations.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Retour</a>
        </div>

        <!-- Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert-success">
                <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>

        <?php if ($message_error): ?>
            <div class="alert-error">
                <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($message_error) ?>
            </div>
        <?php endif; ?>

        <!-- Informations élève -->
        <div class="admin-card">
            <h2><i class="fa-solid fa-user"></i> Élève</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="label">Nom complet</span>
                    <span class="value"><?= htmlspecialchars($resultat['prenom'] . ' ' . $resultat['nom']) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Email</span>
                    <span class="value"><?= htmlspecialchars($resultat['email']) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Téléphone</span>
                    <span class="value"><?= htmlspecialchars($resultat['telephone'] ?? '—') ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Date du quiz</span>
                    <span class="value"><?= date('d/m/Y H:i', strtotime($resultat['date_creation'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="label">Statut</span>
                    <span class="value">
                        <span class="status-badge">
                            <?= ucfirst(str_replace('_', ' ', $resultat['statut'])) ?>
                        </span>
                    </span>
                </div>
            </div>
            
            <!-- Informations parent -->
            <div class="parent-info">
                <span><strong>Email parent :</strong> <?= htmlspecialchars($resultat['parent_email'] ?? 'Non renseigné') ?></span>
                <span><strong>WhatsApp parent :</strong> <?= htmlspecialchars($resultat['parent_whatsapp'] ?? 'Non renseigné') ?></span>
            </div>
        </div>

        <!-- Scores -->
        <div class="admin-card">
            <h2><i class="fa-solid fa-chart-bar"></i> Scores par domaine</h2>
            <div class="scores-grid">
                <?php if ($scores): ?>
                    <?php foreach ($scores as $domaine => $score): ?>
                        <div class="score-item">
                            <span class="label"><?= ucfirst($domaine) ?></span>
                            <span class="value"><?= $score ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:14px;">Aucun score disponible</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filières proposées -->
        <div class="admin-card">
            <h2><i class="fa-solid fa-lightbulb"></i> Proposition automatique</h2>
            <div class="filieres-list">
                <?php if ($filieres_data): ?>
                    <?php foreach ($filieres_data as $filiere): ?>
                        <span class="filiere-tag"><?= htmlspecialchars($filiere) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--gray);font-size:14px;">Aucune filière proposée</p>
                <?php endif; ?>
            </div>

            <!-- Formulaire de validation -->
            <?php if (in_array($resultat['statut'], ['en_attente', 'valide'])): ?>
                <form method="POST" style="margin-top:18px;padding-top:18px;border-top:1px solid var(--border);">
                    <div style="margin-bottom:14px;">
                        <label style="font-weight:600;font-size:14px;display:block;margin-bottom:6px;">Modifier les filières</label>
                        <input type="text" name="filieres_modifiees" 
                               placeholder="Séparer par des virgules (ex: IA, Data Science, Cybersécurité)" 
                               value="<?= implode(', ', $filieres_data ?? []) ?>">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label style="font-weight:600;font-size:14px;display:block;margin-bottom:6px;">Commentaire (optionnel)</label>
                        <textarea name="commentaire" rows="2" placeholder="Ajouter un commentaire pour l'élève..."></textarea>
                    </div>
                    <div class="validation-actions">
                        <?php if ($resultat['statut'] === 'en_attente'): ?>
                            <button type="submit" name="action" value="valider" class="btn-admin btn-validate">
                                <i class="fa-solid fa-check"></i> Valider
                            </button>
                            <button type="submit" name="action" value="modifier" class="btn-admin btn-modify">
                                <i class="fa-solid fa-pen"></i> Modifier
                            </button>
                            <button type="submit" name="action" value="rejeter" class="btn-admin btn-reject" onclick="return confirm('Confirmer le rejet ?')">
                                <i class="fa-solid fa-xmark"></i> Rejeter
                            </button>
                        <?php elseif ($resultat['statut'] === 'valide'): ?>
                            <button type="submit" name="action" value="publier" class="btn-admin btn-publish">
                                <i class="fa-solid fa-paper-plane"></i> Publier
                            </button>
                            <button type="submit" name="action" value="rejeter" class="btn-admin btn-reject" onclick="return confirm('Confirmer le rejet ?')">
                                <i class="fa-solid fa-xmark"></i> Rejeter
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            <?php endif; ?>

            <!-- Message si publié -->
            <?php if ($resultat['statut'] === 'publie'): ?>
                <div class="alert-success" style="margin-top:14px;border:1px solid #bfdbfe;">
                    <i class="fa-solid fa-check-circle"></i> Ce résultat a été publié. L'élève peut le consulter dans son profil.
                </div>
                <?php if ($resultat['commentaire_admin']): ?>
                    <div class="comment-block">
                        <strong>Commentaire :</strong><br>
                        <?= htmlspecialchars($resultat['commentaire_admin']) ?>
                    </div>
                <?php endif; ?>
                
                <!-- Vérifier si le PDF a été généré -->
                <?php
                $stmt = $pdo->prepare("SELECT * FROM documents_pdf WHERE resultat_id = ? ORDER BY date_creation DESC LIMIT 1");
                $stmt->execute([$quiz_id]);
                $pdf = $stmt->fetch();
                ?>
                <?php if ($pdf): ?>
                    <div style="margin-top:12px;padding:10px 14px;background:#f8fafc;border-radius:10px;font-size:13px;">
                        <i class="fa-solid fa-file-pdf" style="color:#ef4444;"></i>
                        PDF généré : <a href="<?= $pdf['chemin_fichier'] ?>" target="_blank" style="color:var(--primary);">Voir le PDF</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Message si rejeté -->
            <?php if ($resultat['statut'] === 'rejete'): ?>
                <div class="alert-error" style="margin-top:14px;">
                    <i class="fa-solid fa-xmark-circle"></i> Ce quiz a été rejeté.
                </div>
                <?php if ($resultat['commentaire_admin']): ?>
                    <div class="comment-block">
                        <strong>Motif du rejet :</strong><br>
                        <?= htmlspecialchars($resultat['commentaire_admin']) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

</body>
</html>