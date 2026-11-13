<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

$currentUser = currentUser();
$pageTitle = 'Административная панель';
$flash = getFlash();

// Статистика
$stats = [
    'ads_total' => $pdo->query("SELECT COUNT(*) FROM ads")->fetchColumn(),
    'ads_pending' => $pdo->query("SELECT COUNT(*) FROM ads WHERE status = 'pending'")->fetchColumn(),
    'ads_active' => $pdo->query("SELECT COUNT(*) FROM ads WHERE status = 'active'")->fetchColumn(),
    'users_total' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'users_active' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
];

// Последние объявления на модерации
$stmt = $pdo->query("SELECT a.*, u.name AS author_name, c.name AS category_name, c.icon AS category_icon 
    FROM ads a 
    JOIN users u ON a.user_id = u.id 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.status = 'pending' 
    ORDER BY a.created_at DESC 
    LIMIT 10");
$pendingAds = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="page">
    <h1 class="section-title">Административная панель</h1>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;max-width:1000px;margin:0 auto 40px;">
        <div style="background:var(--accent-green);padding:20px;border-radius:12px;text-align:center;">
            <div style="font-size:2rem;font-weight:800;"><?= $stats['ads_total'] ?></div>
            <div style="font-weight:300;">Всего объявлений</div>
        </div>
        <div style="background:var(--accent-orange);padding:20px;border-radius:12px;text-align:center;">
            <div style="font-size:2rem;font-weight:800;"><?= $stats['ads_pending'] ?></div>
            <div style="font-weight:300;">На модерации</div>
        </div>
        <div style="background:#5a7a9a;padding:20px;border-radius:12px;text-align:center;">
            <div style="font-size:2rem;font-weight:800;"><?= $stats['users_total'] ?></div>
            <div style="font-weight:300;">Всего пользователей</div>
        </div>
    </div>

    <h2 style="font-family:var(--font-heading);font-weight:700;margin-bottom:20px;">Объявления на модерации</h2>

    <?php if (empty($pendingAds)): ?>
        <p style="text-align:center;opacity:0.7;padding:40px;">Нет объявлений на модерации</p>
    <?php else: ?>
        <div class="ads-grid">
            <?php foreach ($pendingAds as $ad): 
                // Проверяем изображение
                $hasImage = !empty($ad['image']) 
                    && strpos($ad['image'], 'C:\\') === false 
                    && strpos($ad['image'], '/') === false 
                    && strpos($ad['image'], '\\') === false
                    && @file_exists(UPLOAD_DIR . $ad['image']);
            ?>
                <div class="ad-card" style="cursor:default;">
                    <div class="ad-card-img">
                        <?php if ($hasImage): ?>
                            <img src="<?= SITE_URL ?>/uploads/<?= e($ad['image']) ?>" alt="<?= e($ad['title']) ?>">
                        <?php else: ?>
                            <span style="font-size:4rem;"><?= $ad['category_icon'] ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="ad-card-body">
                        <div class="ad-card-title"><?= e($ad['title']) ?></div>
                        <div class="ad-card-price <?= $ad['price'] === 0 ? 'free' : '' ?>">
                            <?= formatPrice($ad['price']) ?>
                        </div>
                        <div class="ad-card-meta">
                            <span>Автор: <?= e($ad['author_name']) ?></span>
                            <span><?= timeAgo($ad['created_at']) ?></span>
                        </div>
                        <div style="margin-top:15px;display:flex;gap:10px;flex-wrap:wrap;">
                            <a href="<?= SITE_URL ?>/admin/moderate.php?action=approve&id=<?= $ad['id'] ?>" 
                               class="btn btn-secondary" style="padding:8px 16px;font-size:0.85rem;">✓ Одобрить</a>
                            <a href="<?= SITE_URL ?>/admin/moderate.php?action=reject&id=<?= $ad['id'] ?>" 
                               class="btn btn-outline" style="padding:8px 16px;font-size:0.85rem;">✕ Отклонить</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;margin-top:40px;">
        <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline">👥 Управление пользователями</a>
        <a href="<?= SITE_URL ?>/admin/categories.php" class="btn btn-outline">📂 Управление категориями</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>