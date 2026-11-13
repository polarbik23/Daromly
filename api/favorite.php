<?php
require_once __DIR__ . '/../functions.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit;
}

$currentUser = currentUser();

$input = json_decode(file_get_contents('php://input'), true);
$adId = (int)($input['ad_id'] ?? 0);

if (!$adId) {
    echo json_encode(['success' => false, 'message' => 'Неверный ID']);
    exit;
}

$userId = $currentUser['id'];

$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND ad_id = ?");
$stmt->execute([$userId, $adId]);

if ($stmt->fetch()) {
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND ad_id = ?");
    $stmt->execute([$userId, $adId]);
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    $stmt = $pdo->prepare("INSERT INTO favorites (user_id, ad_id) VALUES (?, ?)");
    $stmt->execute([$userId, $adId]);
    echo json_encode(['success' => true, 'action' => 'added']);
}