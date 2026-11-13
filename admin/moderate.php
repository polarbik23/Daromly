<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

$currentUser = currentUser();
$action = $_GET['action'] ?? '';
$adId = (int)($_GET['id'] ?? 0);

if (!$adId) {
    setFlash('error', 'Неверный ID объявления');
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ?");
$stmt->execute([$adId]);
$ad = $stmt->fetch();

if (!$ad) {
    setFlash('error', 'Объявление не найдено');
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

if ($action === 'approve') {
    $stmt = $pdo->prepare("UPDATE ads SET status = 'active' WHERE id = ?");
    $stmt->execute([$adId]);
    logActivity('ad_approve', "Одобрено объявление: {$ad['title']}");
    setFlash('success', 'Объявление одобрено');
} elseif ($action === 'reject') {
    $reason = $_POST['reason'] ?? 'Не соответствует правилам';
    $stmt = $pdo->prepare("UPDATE ads SET status = 'rejected', reject_reason = ? WHERE id = ?");
    $stmt->execute([$reason, $adId]);
    logActivity('ad_reject', "Отклонено объявление: {$ad['title']}. Причина: $reason");
    setFlash('success', 'Объявление отклонено');
}

header('Location: ' . SITE_URL . '/admin/index.php');
exit;