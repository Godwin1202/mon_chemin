<?php
// php/generate_pdf.php

require_once 'db.php';

function genererPDF($resultat_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT qr.*, u.nom, u.prenom, u.email, u.telephone, u.parent_email, u.parent_whatsapp
        FROM quiz_resultats qr
        JOIN utilisateurs u ON qr.utilisateur_id = u.id
        WHERE qr.id = ?
    ");
    $stmt->execute([$resultat_id]);
    $data = $stmt->fetch();
    
    if (!$data) {
        return false;
    }
    
    $dossier = '../assets/pdf/';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0777, true);
    }
    
    $nom_fichier = 'orientation_' . $data['utilisateur_id'] . '_' . date('Ymd_His') . '.txt';
    $chemin_complet = $dossier . $nom_fichier;
    
    $contenu = "=== MON CHEMIN ===\n";
    $contenu .= "Bilan d'orientation\n\n";
    $contenu .= "--- Informations de l'eleve ---\n";
    $contenu .= "Nom: " . $data['prenom'] . ' ' . $data['nom'] . "\n";
    $contenu .= "Email: " . $data['email'] . "\n";
    $contenu .= "Telephone: " . ($data['telephone'] ?? 'Non renseigne') . "\n\n";
    
    $contenu .= "--- Scores par domaine ---\n";
    $scores = json_decode($data['scores_domaines'], true);
    if ($scores) {
        foreach ($scores as $domaine => $score) {
            $contenu .= ucfirst($domaine) . ': ' . $score . " pts\n";
        }
    }
    $contenu .= "\n";
    
    $filieres = json_decode($data['filieres_proposees'], true);
    if ($filieres) {
        $contenu .= "--- Filières recommandées ---\n";
        foreach ($filieres as $filiere) {
            $contenu .= "- " . $filiere . "\n";
        }
        $contenu .= "\n";
    }
    
    if ($data['commentaire_admin']) {
        $contenu .= "--- Commentaire ---\n";
        $contenu .= $data['commentaire_admin'] . "\n";
    }
    
    $contenu .= "\nDocument genere le " . date('d/m/Y') . " - Mon Chemin";
    
    file_put_contents($chemin_complet, $contenu);
    
    $stmt = $pdo->prepare("
        INSERT INTO documents_pdf (resultat_id, chemin_fichier, date_creation)
        VALUES (?, ?, NOW())
    ");
    $stmt->execute([$resultat_id, $chemin_complet]);
    
    return $chemin_complet;
}
?>