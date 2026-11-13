<?php
require_once __DIR__ . '/config.php';

// ===== АВТОРИЗАЦИЯ =====
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireAuth(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Необходима авторизация');
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    $user = currentUser();
    if (!$user || $user['role'] !== 'admin') {
        setFlash('error', 'Доступ запрещён');
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

// ===== БЕЗОПАСНОСТЬ =====
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function validatePassword(string $password): array {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = 'Пароль должен содержать не менее 8 символов';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Пароль должен содержать заглавную букву';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Пароль должен содержать строчную букву';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Пароль должен содержать цифру';
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $errors[] = 'Пароль должен содержать специальный символ';
    }
    return $errors;
}

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ===== УТИЛИТЫ =====
function formatPrice(int $price): string {
    if ($price === 0) return 'Бесплатно';
    return number_format($price, 0, '.', ' ') . ' ₽';
}

function timeAgo(string $datetime): string {
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'только что';
    if ($diff < 3600) return floor($diff / 60) . ' мин. назад';
    if ($diff < 86400) return floor($diff / 3600) . ' ч. назад';
    if ($diff < 2592000) return floor($diff / 86400) . ' дн. назад';
    return date('d.m.Y', $time);
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function logActivity(string $action, string $description = ''): void {
    global $pdo;
    $userId = $_SESSION['user_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $stmt = $pdo->prepare("INSERT INTO activity_log (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $action, $description, $ip]);
}

// ===== ДАННЫЕ =====
function getCategories(): array {
    global $pdo;
    return $pdo->query("SELECT * FROM categories ORDER BY sort_order, name")->fetchAll();
}

function getAds(array $filters = []): array {
    global $pdo;
    $sql = "SELECT a.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon,
            u.name AS author_name
            FROM ads a
            JOIN categories c ON a.category_id = c.id
            JOIN users u ON a.user_id = u.id
            WHERE a.status = 'active'";
    $params = [];

    if (!empty($filters['category'])) {
        $sql .= " AND c.slug = ?";
        $params[] = $filters['category'];
    }
    if (!empty($filters['search'])) {
        $sql .= " AND (a.title LIKE ? OR a.description LIKE ? OR a.city LIKE ?)";
        $like = '%' . $filters['search'] . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if (!empty($filters['free'])) {
        $sql .= " AND a.price = 0";
    }

    $sql .= " ORDER BY a.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAd(int $id): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name AS category_name, c.slug AS category_slug,
            u.name AS author_name, u.email AS author_email
            FROM ads a
            JOIN categories c ON a.category_id = c.id
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ?");
    $stmt->execute([$id]);
    $ad = $stmt->fetch();
    if ($ad) {
        // Увеличиваем счётчик просмотров
        $pdo->prepare("UPDATE ads SET views = views + 1 WHERE id = ?")->execute([$id]);
    }
    return $ad ?: null;
}

function getUserAds(int $userId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name AS category_name, c.icon AS category_icon
            FROM ads a
            JOIN categories c ON a.category_id = c.id
            WHERE a.user_id = ?
            ORDER BY a.created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getUserFavorites(int $userId): array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT a.*, c.name AS category_name, c.icon AS category_icon
            FROM ads a
            JOIN categories c ON a.category_id = c.id
            JOIN favorites f ON a.id = f.ad_id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function isFavorite(int $userId, int $adId): bool {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND ad_id = ?");
    $stmt->execute([$userId, $adId]);
    return (bool) $stmt->fetch();
}

function getPage(string $slug): ?array {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM pages WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}