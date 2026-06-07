<?php
// php/quiz_algorithm.php

$domaines = [
    'informatique' => 0,
    'sante' => 0,
    'genie_civil' => 0,
    'gestion' => 0,
    'droit' => 0,
    'enseignement' => 0,
    'agronomie' => 0,
    'communication' => 0
];

function calculerScores($reponses, $pdo) {
    global $domaines;
    $scores = $domaines;
    
    foreach ($reponses as $question_num => $reponse_valeur) {
        $stmt = $pdo->prepare("
            SELECT qo.scores_domaines 
            FROM quiz_options qo
            JOIN quiz_questions qq ON qo.question_id = qq.id
            WHERE qq.numero = ? AND qo.option_valeur = ?
        ");
        $stmt->execute([$question_num, $reponse_valeur]);
        $result = $stmt->fetch();
        
        if ($result) {
            $scores_option = json_decode($result['scores_domaines'], true);
            foreach ($scores_option as $domaine => $points) {
                $scores[$domaine] += $points;
            }
        }
    }
    
    return $scores;
}

function determinerFilieresRecommandees($scores) {
    arsort($scores);
    return array_slice($scores, 0, 3, true);
}

function sauvegarderResultatsQuiz($user_id, $reponses, $pdo) {
    global $domaines;
    
    // Créer une session de quiz
    $stmt = $pdo->prepare("
        INSERT INTO quiz_sessions (utilisateur_id, date_debut, date_fin, statut)
        VALUES (?, NOW(), NOW(), 'termine')
    ");
    $stmt->execute([$user_id]);
    $session_id = $pdo->lastInsertId();
    
    // Calculer les scores
    $scores = calculerScores($reponses, $pdo);
    $recommandations = determinerFilieresRecommandees($scores);
    
    // Sauvegarder les scores
    $stmt = $pdo->prepare("
        UPDATE quiz_sessions 
        SET scores = ?, filieres_proposees = ?, statut_validation = 'en_attente'
        WHERE id = ?
    ");
    $stmt->execute([json_encode($scores), json_encode($recommandations), $session_id]);
    
    // Sauvegarder chaque réponse
    $stmt = $pdo->prepare("
        INSERT INTO quiz_reponses (session_id, question_id, question_numero, option_id, reponse_valeur)
        SELECT ?, qq.id, qq.numero, qo.id, ?
        FROM quiz_questions qq
        JOIN quiz_options qo ON qo.question_id = qq.id
        WHERE qq.numero = ? AND qo.option_valeur = ?
    ");
    
    foreach ($reponses as $question_num => $reponse_valeur) {
        $stmt->execute([$session_id, $reponse_valeur, $question_num, $reponse_valeur]);
    }
    
    return $session_id;
}
?>