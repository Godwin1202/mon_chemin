<?php
// pages/traiter_quiz.php

require_once '../php/db.php';
require_once '../php/auth.php';
require_once '../php/quiz_algorithm.php';

// Vérifier que l'utilisateur est connecté
redirigerSiNonConnecte();
$user_id = $_SESSION['user_id'];

// Vérifier que le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quiz.php');
    exit();
}

// Récupérer toutes les réponses
$reponses = [];
foreach ($_POST as $key => $value) {
    if (preg_match('/^q(\d+)$/', $key, $matches)) {
        $question_num = (int)$matches[1];
        $reponses[$question_num] = trim($value);
    }
}

// Vérifier qu'on a 22 réponses
if (count($reponses) < 22) {
    $_SESSION['quiz_error'] = "Veuillez répondre à toutes les questions.";
    header('Location: quiz.php');
    exit();
}

// Vérifier que l'utilisateur n'a pas déjà un quiz en attente
$stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_sessions WHERE utilisateur_id = ? AND statut_validation = 'en_attente'");
$stmt->execute([$user_id]);
$has_pending = $stmt->fetchColumn();

if ($has_pending > 0) {
    header('Location: attente.php');
    exit();
}

// Sauvegarder les résultats et calculer les scores
$session_id = sauvegarderResultatsQuiz($user_id, $reponses, $pdo);

// Rediriger vers la page d'attente
header('Location: attente.php');
exit();
?>