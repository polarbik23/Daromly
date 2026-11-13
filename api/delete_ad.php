<?php
require_once __DIR__ . '/../functions.php';
requireAuth();

$currentUser = currentUser();

$adId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$adId, $currentUser['id']]);
$ad = $stmt->fetch();

if ($ad) {
    if ($ad['image'] && file_exists(UPLOAD_DIR . $ad['image'])) {
        unlink(UPLOAD_DIR . $ad['image']);
    }
    $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
    $stmt->execute([$adId]);
    setFlash('success', 'Объявление удалено');
} else {
    setFlash('error', 'Объявление не найдено');
}

header('Location: ' . SITE_URL . '/cabinet.php?tab=myads');
exit;