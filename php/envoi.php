<?php
// php/envoi.php

require_once 'db.php';

function envoyerEmailParent($utilisateur_id, $pdf_path, $pdo) {
    $stmt = $pdo->prepare("
        SELECT parent_email, prenom, nom FROM utilisateurs WHERE id = ?
    ");
    $stmt->execute([$utilisateur_id]);
    $user = $stmt->fetch();
    
    if (!$user || empty($user['parent_email'])) {
        return false;
    }
    
    $parent_email = $user['parent_email'];
    $nom_eleve = $user['prenom'] . ' ' . $user['nom'];
    
    $sujet = 'Mon Chemin - Résultat d\'orientation de ' . $nom_eleve;
    $message = "Bonjour,\n\nLe bilan d'orientation de votre enfant {$nom_eleve} est disponible.\n\n";
    $message .= "Connectez-vous à l'espace Mon Chemin pour consulter les résultats.\n\n";
    $message .= "Cordialement,\nL'équipe Mon Chemin";
    
    $headers = "From: Mon Chemin <no-reply@monchemin.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=utf-8\r\n";
    
    return mail($parent_email, $sujet, $message, $headers);
}

function envoyerWhatsAppParent($utilisateur_id, $pdf_path, $pdo) {
    $stmt = $pdo->prepare("
        SELECT parent_whatsapp, prenom, nom FROM utilisateurs WHERE id = ?
    ");
    $stmt->execute([$utilisateur_id]);
    $user = $stmt->fetch();
    
    if (!$user || empty($user['parent_whatsapp'])) {
        return false;
    }
    
    $whatsapp = $user['parent_whatsapp'];
    $nom_eleve = $user['prenom'] . ' ' . $user['nom'];
    
    $whatsapp = preg_replace('/[^0-9]/', '', $whatsapp);
    if (substr($whatsapp, 0, 1) !== '0' && strlen($whatsapp) < 10) {
        $whatsapp = '229' . $whatsapp;
    }
    
    $message = "Bonjour, le bilan d'orientation de votre enfant {$nom_eleve} est disponible sur Mon Chemin. Connectez-vous pour consulter les résultats.";
    
    return "https://wa.me/{$whatsapp}?text=" . urlencode($message);
}
?>