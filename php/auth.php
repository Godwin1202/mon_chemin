<?php
// php/auth.php

// Démarrer la session seulement si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estConnecte() {
    return isset($_SESSION['user_id']);
}

function estAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function connecterUtilisateur($user_id, $role, $nom, $prenom) {
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_nom'] = $nom;
    $_SESSION['user_prenom'] = $prenom;
}

function deconnecterUtilisateur() {
    session_destroy();
    header('Location: ../index.php');
    exit();
}

function redirigerSiNonConnecte() {
    if (!estConnecte()) {
        header('Location: connexion.php');
        exit();
    }
}

function redirigerSiNonAdmin() {
    if (!estAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}
?>