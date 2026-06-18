<?php
// includes/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_connected = isset($_SESSION['user_id']);
$user_role = '';
$user_prenom = '';

if ($is_connected) {
    $user_role = $_SESSION['user_role'] ?? 'eleve';
    $user_prenom = $_SESSION['user_prenom'] ?? '';
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<header class="header">
    <div class="container header-content">
        <div class="logo">
            <a href="<?= $page_prefix ?? '' ?>index.php" style="display: flex; align-items: center; gap: 14px; text-decoration: none; color: inherit;">
                <i class="fa-solid fa-graduation-cap"></i>
                <div>
                    <h2>Mon Chemin</h2>
                    <p>Orientation Afrique de l'Ouest</p>
                </div>
            </a>
        </div>

        <nav class="navbar">
            <a href="<?= $page_prefix ?? '' ?>index.php" class="<?= $current_page === 'index.php' ? 'active' : '' ?>">Accueil</a>
            <a href="<?= $page_prefix ?? '' ?>pages/quiz.php" class="<?= $current_page === 'quiz.php' ? 'active' : '' ?>">Quiz</a>
            <a href="<?= $page_prefix ?? '' ?>pages/universites.php" class="<?= $current_page === 'universites.php' ? 'active' : '' ?>">Universités</a>
            <a href="<?= $page_prefix ?? '' ?>pages/conseils.php" class="<?= $current_page === 'conseils.php' ? 'active' : '' ?>">Conseils</a>
            <a href="<?= $page_prefix ?? '' ?>pages/apropos.php" class="<?= $current_page === 'apropos.php' ? 'active' : '' ?>">À propos</a>
        </nav>

        <div class="header-buttons">
            <!-- BOUTON THEME -->
            <button id="themeToggle" class="theme-btn" aria-label="Thème">
                <i class="fa-solid fa-moon"></i>
            </button>

            <?php if ($is_connected): ?>
                <div class="user-menu">
                    <a href="<?= $page_prefix ?? '' ?>pages/profil.php" class="btn white-btn" style="text-decoration: none;">
                        <i class="fa-solid fa-user"></i> <span><?= htmlspecialchars($user_prenom) ?></span>
                    </a>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="<?= $page_prefix ?? '' ?>admin/dashboard.php" class="btn blue-btn">
                            <i class="fa-solid fa-gauge-high"></i> <span>Admin</span>
                        </a>
                    <?php endif; ?>
                    <a href="<?= $page_prefix ?? '' ?>php/deconnexion.php" class="btn logout-btn">
                        <i class="fa-solid fa-sign-out-alt"></i> <span>Déconnexion</span>
                    </a>
                </div>
            <?php else: ?>
                <a href="<?= $page_prefix ?? '' ?>pages/connexion.php" class="btn white-btn">Se connecter</a>
                <a href="<?= $page_prefix ?? '' ?>pages/inscription.php" class="btn blue-btn">S'inscrire</a>
            <?php endif; ?>
        </div>
    </div>
</header>