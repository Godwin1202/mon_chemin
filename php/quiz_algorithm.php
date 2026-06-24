<?php
// php/quiz_algorithm.php

// =====================================================
// 1. DÉFINITION DES DOMAINES
// =====================================================

$domaines = [
    'informatique' => 0,
    'sante' => 0,
    'genie_civil' => 0,
    'gestion' => 0,
    'droit' => 0,
    'enseignement' => 0,
    'agronomie' => 0,
    'communication' => 0,
    'sciences_fond' => 0,
    'arts' => 0
];

$domaine_to_filiere = [
    'informatique' => 'Informatique & Numérique',
    'sante' => 'Santé & Médical',
    'genie_civil' => 'Génie Civil & BTP',
    'gestion' => 'Gestion & Business',
    'droit' => 'Droit & Sciences Politiques',
    'enseignement' => 'Éducation & Enseignement',
    'agronomie' => 'Agronomie & Environnement',
    'communication' => 'Communication & Médias',
    'sciences_fond' => 'Sciences Fondamentales',
    'arts' => 'Arts, Design & Création'
];

// =====================================================
// 2. FONCTION DE CALCUL DES SCORES
// =====================================================

function calculerScores($reponses, $pdo) {
    global $domaines;
    $scores = $domaines;
    
    $coefficients = [
        1 => 3, 2 => 1, 3 => 2, 4 => 2, 5 => 2,
        6 => 2, 7 => 1, 8 => 2, 9 => 2, 10 => 1,
        11 => 1, 12 => 1, 13 => 2, 14 => 2, 15 => 2,
        16 => 1, 17 => 1, 18 => 1, 19 => 2, 20 => 1,
        21 => 0, 22 => 0
    ];
    
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
            $coef = $coefficients[$question_num] ?? 1;
            
            foreach ($scores_option as $domaine => $points) {
                if (isset($scores[$domaine])) {
                    $scores[$domaine] += $points * $coef;
                }
            }
        }
    }
    
    return $scores;
}

// =====================================================
// 3. FONCTION DE CLASSEMENT
// =====================================================

function classerDomaines($scores) {
    arsort($scores);
    return $scores;
}

// =====================================================
// 4. FONCTION POUR RÉCUPÉRER LE PAYS CHOISI
// =====================================================

function getPaysChoisi($reponses) {
    $pays_map = [
        'benin' => 'Bénin',
        'afrique_ouest' => 'Afrique de l\'Ouest',
        'europe' => 'Europe',
        'international' => 'International'
    ];
    
    if (isset($reponses[20])) {
        return $pays_map[$reponses[20]] ?? 'Bénin';
    }
    return 'Bénin';
}

// =====================================================
// 5. FONCTION POUR RÉCUPÉRER LES FILIÈRES
// =====================================================

function getFilieresParDomaine($domaine, $pdo) {
    $stmt = $pdo->prepare("
        SELECT id, nom, description 
        FROM filieres 
        WHERE domaine_id = (SELECT id FROM domaines WHERE LOWER(nom) LIKE ?)
        LIMIT 5
    ");
    $stmt->execute(['%' . strtolower($domaine) . '%']);
    return $stmt->fetchAll();
}

// =====================================================
// 6. FONCTION POUR FILTRER LES FILIÈRES SELON LE HANDICAP
// =====================================================

function filtrerFilieresParHandicap($filieres, $type_handicap, $pdo) {
    if (empty($type_handicap)) {
        return $filieres;
    }
    
    // Définir les restrictions selon le type de handicap
    $restrictions = [
        'aveugle' => ['Design Graphique', 'Architecture', 'Topographie', 'Photographie', 'Arts Plastiques'],
        'malvoyant' => ['Design Graphique', 'Architecture', 'Topographie', 'Photographie'],
        'sourd' => ['Journalisme', 'Communication Digitale', 'Relations Publiques', 'Enseignement', 'Médecine Générale'],
        'malentendant' => ['Journalisme', 'Relations Publiques'],
        'mains' => ['Chirurgie', 'Pharmacie', 'Soins Infirmiers', 'Génie Civil', 'Architecture', 'Mécanique', 'Arts'],
        'mobilité_reduite' => ['Topographie', 'Génie Civil', 'Agronomie', 'Production Animale'],
        'voix' => ['Enseignement', 'Journalisme', 'Communication'],
        'audition' => ['Journalisme', 'Relations Publiques']
    ];
    
    $exclure = [];
    $type = strtolower($type_handicap);
    
    // Vérifier quel type de handicap correspond
    foreach ($restrictions as $key => $restricted_filieres) {
        if (strpos($type, $key) !== false || strpos($key, $type) !== false) {
            $exclure = array_merge($exclure, $restricted_filieres);
        }
    }
    
    // Supprimer les filières incompatibles
    $exclure = array_unique($exclure);
    return array_filter($filieres, function($filiere) use ($exclure) {
        return !in_array($filiere, $exclure);
    });
}

// =====================================================
// 7. FONCTION POUR FILTRER LES UNIVERSITÉS SELON LE HANDICAP
// =====================================================

function filtrerUniversitesParHandicap($universites, $type_handicap, $pdo) {
    if (empty($type_handicap)) {
        return $universites;
    }
    return $universites;
}

// =====================================================
// 8. FONCTION DE SAUVEGARDE DES RÉSULTATS (AVEC HANDICAP)
// =====================================================

function sauvegarderResultatsQuiz($user_id, $reponses, $pdo) {
    global $domaine_to_filiere;
    
    // Créer une session de quiz
    $stmt = $pdo->prepare("
        INSERT INTO quiz_sessions (utilisateur_id, date_debut, date_fin, statut)
        VALUES (?, NOW(), NOW(), 'termine')
    ");
    $stmt->execute([$user_id]);
    $session_id = $pdo->lastInsertId();
    
    // Calculer les scores
    $scores = calculerScores($reponses, $pdo);
    
    // Classer les domaines
    $classement = classerDomaines($scores);
    $top_domaines = array_keys(array_slice($classement, 0, 3, true));
    
    // Récupérer les filières recommandées
    $filieres_recommandees = [];
    foreach ($top_domaines as $domaine) {
        $filieres = getFilieresParDomaine($domaine, $pdo);
        foreach ($filieres as $filiere) {
            $filieres_recommandees[] = $filiere['nom'];
        }
    }
    $filieres_recommandees = array_slice($filieres_recommandees, 0, 5);
    
    // Récupérer le type de handicap de l'utilisateur
    $stmt = $pdo->prepare("SELECT type_handicap, situation_handicap FROM utilisateurs WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_handicap = $stmt->fetch();
    
    if ($user_handicap && $user_handicap['situation_handicap'] == 1) {
        $type_handicap = $user_handicap['type_handicap'] ?? '';
        $filieres_recommandees = filtrerFilieresParHandicap($filieres_recommandees, $type_handicap, $pdo);
        if (empty($filieres_recommandees)) {
            $filieres_recommandees = array_slice($filieres_recommandees, 0, 2);
        }
    }
    
    // Récupérer le pays choisi
    $pays = getPaysChoisi($reponses);
    
    // Construire le résultat
    $domaines_principaux = [];
    foreach ($top_domaines as $domaine) {
        $domaines_principaux[] = [
            'domaine' => $domaine,
            'nom' => $domaine_to_filiere[$domaine] ?? ucfirst($domaine),
            'score' => $scores[$domaine]
        ];
    }
    
    // Récupérer les universités
    $universites = getUniversitesParPays($pays, $pdo);
    
    if ($user_handicap && $user_handicap['situation_handicap'] == 1) {
        $universites = filtrerUniversitesParHandicap($universites, $type_handicap ?? '', $pdo);
    }
    
    // Insérer dans quiz_resultats
    $stmt = $pdo->prepare("
        INSERT INTO quiz_resultats (
            session_id, 
            utilisateur_id, 
            scores_domaines, 
            domaines_principaux, 
            filieres_proposees, 
            universites_proposees, 
            statut,
            date_creation
        )
        VALUES (?, ?, ?, ?, ?, ?, 'en_attente', NOW())
    ");
    
    $stmt->execute([
        $session_id,
        $user_id,
        json_encode($scores),
        json_encode($domaines_principaux),
        json_encode($filieres_recommandees),
        json_encode($universites)
    ]);
    
    // Sauvegarder les réponses détaillées
    $stmt = $pdo->prepare("
        INSERT INTO quiz_reponses (session_id, question_id, option_id)
        SELECT ?, qq.id, qo.id
        FROM quiz_questions qq
        JOIN quiz_options qo ON qo.question_id = qq.id
        WHERE qq.numero = ? AND qo.option_valeur = ?
    ");
    
    foreach ($reponses as $question_num => $reponse_valeur) {
        try {
            $stmt->execute([$session_id, $question_num, $reponse_valeur]);
        } catch (PDOException $e) {
            // Ignorer les erreurs
        }
    }
    
    return $session_id;
}

// =====================================================
// 9. FONCTION POUR RÉCUPÉRER LES UNIVERSITÉS PAR PAYS
// =====================================================

function getUniversitesParPays($pays, $pdo) {
    if ($pays === 'Bénin') {
        $stmt = $pdo->query("SELECT nom, ville, pays FROM universites WHERE pays = 'Bénin' LIMIT 5");
        return $stmt->fetchAll();
    } elseif ($pays === 'Afrique de l\'Ouest' || $pays === 'International') {
        $stmt = $pdo->query("SELECT nom, ville, pays FROM universites LIMIT 10");
        return $stmt->fetchAll();
    } else {
        $stmt = $pdo->prepare("SELECT nom, ville, pays FROM universites WHERE pays = ? LIMIT 5");
        $stmt->execute([$pays]);
        return $stmt->fetchAll();
    }
}

// =====================================================
// 10. FONCTION POUR RÉCUPÉRER LES RÉSULTATS
// =====================================================

function getResultatsQuiz($session_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT qr.*, qs.utilisateur_id, u.nom, u.prenom, u.email 
        FROM quiz_resultats qr
        JOIN quiz_sessions qs ON qr.session_id = qs.id
        JOIN utilisateurs u ON qr.utilisateur_id = u.id
        WHERE qr.session_id = ?
    ");
    $stmt->execute([$session_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $result['scores_domaines'] = json_decode($result['scores_domaines'], true);
        $result['domaines_principaux'] = json_decode($result['domaines_principaux'], true);
        $result['filieres_proposees'] = json_decode($result['filieres_proposees'], true);
        $result['universites_proposees'] = json_decode($result['universites_proposees'], true);
    }
    
    return $result;
}

// =====================================================
// 11. FONCTION POUR RÉCUPÉRER LE DERNIER QUIZ D'UN ÉLÈVE
// =====================================================

function getLastQuiz($user_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT qr.*, qs.utilisateur_id 
        FROM quiz_resultats qr
        JOIN quiz_sessions qs ON qr.session_id = qs.id
        WHERE qr.utilisateur_id = ?
        ORDER BY qr.date_creation DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $result['scores_domaines'] = json_decode($result['scores_domaines'], true);
        $result['domaines_principaux'] = json_decode($result['domaines_principaux'], true);
        $result['filieres_proposees'] = json_decode($result['filieres_proposees'], true);
        $result['universites_proposees'] = json_decode($result['universites_proposees'], true);
    }
    
    return $result;
}

// =====================================================
// 12. FONCTION POUR VALIDER UN RÉSULTAT (ADMIN)
// =====================================================

function validerResultatAdmin($session_id, $admin_id, $action, $commentaire = '', $pdo) {
    $statut = 'valide';
    
    switch ($action) {
        case 'valider':
            $statut = 'valide';
            break;
        case 'publier':
            $statut = 'publie';
            break;
        case 'rejeter':
            $statut = 'rejete';
            break;
        case 'modifier':
            $statut = 'modifie';
            break;
    }
    
    $stmt = $pdo->prepare("
        UPDATE quiz_resultats 
        SET statut = ?, 
            date_validation = NOW(), 
            admin_id = ?, 
            commentaire_admin = ?
        WHERE session_id = ?
    ");
    $stmt->execute([$statut, $admin_id, $commentaire, $session_id]);
    
    $stmt = $pdo->prepare("
        INSERT INTO validations_admin (resultat_id, admin_id, action, commentaire)
        VALUES ((SELECT id FROM quiz_resultats WHERE session_id = ?), ?, ?, ?)
    ");
    $stmt->execute([$session_id, $admin_id, $action, $commentaire]);
    
    return true;
}

// =====================================================
// 13. FONCTION POUR RÉCUPÉRER LE RÉSULTAT D'UN ÉLÈVE (PUBLIÉ)
// =====================================================

function getResultatEleve($user_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT qr.* 
        FROM quiz_resultats qr
        WHERE qr.utilisateur_id = ? 
        AND qr.statut = 'publie'
        ORDER BY qr.date_validation DESC
        LIMIT 1
    ");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $result['scores_domaines'] = json_decode($result['scores_domaines'], true);
        $result['domaines_principaux'] = json_decode($result['domaines_principaux'], true);
        $result['filieres_proposees'] = json_decode($result['filieres_proposees'], true);
        $result['universites_proposees'] = json_decode($result['universites_proposees'], true);
    }
    
    return $result;
}
?>