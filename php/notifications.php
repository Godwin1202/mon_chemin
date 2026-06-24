<?php
// php/notifications.php

require_once 'db.php';

function ajouterNotification($utilisateur_id, $titre, $message, $pdo) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (utilisateur_id, titre, message, date_creation)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$utilisateur_id, $titre, $message]);
    return $pdo->lastInsertId();
}

function getNotifications($utilisateur_id, $pdo, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT * FROM notifications 
        WHERE utilisateur_id = ? 
        ORDER BY date_creation DESC 
        LIMIT ?
    ");
    $stmt->execute([$utilisateur_id, $limit]);
    return $stmt->fetchAll();
}

function marquerNotificationLue($notification_id, $pdo) {
    $stmt = $pdo->prepare("UPDATE notifications SET lu = 1 WHERE id = ?");
    $stmt->execute([$notification_id]);
}

function getNbNotificationsNonLues($utilisateur_id, $pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM notifications 
        WHERE utilisateur_id = ? AND lu = 0
    ");
    $stmt->execute([$utilisateur_id]);
    return $stmt->fetchColumn();
}
?>