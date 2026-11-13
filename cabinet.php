<?php
require_once __DIR__ . '/functions.php';
requireAuth();

$pageTitle = 'Личный кабинет';
require_once __DIR__ . '/includes/header.php';

$tab = $_GET['tab'] ?? 'myads';
$myAds = getUserAds($currentUser['id']);
$favAds = getUserFavorites($currentUser['id']);
?>

<section class="page">
    <h1 class="section-title">Личный кабинет</h1>

    <div class="cabinet-profile">
        <div class="cabinet-avatar"><?= mb_strtoupper(mb_substr($currentUser['name'], 0, 1)) ?></div>
        <div class="cabinet-info">
            <h3><?= e($currentUser['name']) ?></h3>
            <p><?= e($currentUser['email']) ?></p>
        </div>
        <div class="cabinet-stats">
            <div class="cabinet-stat">
                <div class="cabinet-stat-num"><?= count($myAds) ?></div>
                <div class="cabinet-stat-label">Объявления</div>
            </div>
            <div class="cabinet-stat">
                <div class="cabinet-stat-num"><?= count($favAds) ?></div>
                <div class="cabinet-stat-label">Избранное</div>
            </div>
        </div>
    </div>

    <div class="cabinet-tabs">
        <a href="?tab=myads" class="cabinet-tab <?= $tab === 'myads' ? 'active' : '' ?>">Мои объявления</a>
        <a href="?tab=favorites" class="cabinet-tab <?= $tab === 'favorites' ? 'active' : '' ?>">Избранное</a>
    </div>

    <?php if ($tab === 'myads'): ?>
        <?php if (empty($myAds)): ?>
            <p style="text-align:center;opacity:0.7;padding:40px;">У вас пока нет объявлений</p>
        <?php else: ?>
            <div class="ads-grid">
                <?php foreach ($myAds as $ad): ?>
                    <a href="<?= SITE_URL ?>/ad.php?id=<?= $ad['id'] ?>" class="ad-card">
                        <div class="ad-card-img"><?= $ad['category_icon'] ?></div>
                        <div class="ad-card-body">
                            <div class="ad-card-title"><?= e($ad['title']) ?></div>
                            <div class="ad-card-price <?= $ad['price'] === 0 ? 'free' : '' ?>">
                                <?= formatPrice($ad['price']) ?>
                            </div>
                            <div style="margin-top:10px;">
                                <a href="<?= SITE_URL ?>/api/delete_ad.php?id=<?= $ad['id'] ?>"
                                   class="btn btn-outline" style="padding:6px 14px;font-size:0.8rem;"
                                   onclick="return confirm('Удалить объявление?')">Удалить</a>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div style="text-align:center;margin-top:30px;">
            <a href="<?= SITE_URL ?>/post.php" class="btn btn-primary">+ Разместить новое объявление</a>
        </div>

    <?php elseif ($tab === 'favorites'): ?>
        <?php if (empty($favAds)): ?>
            <p style="text-align:center;opacity:0.7;padding:40px;">В избранном пока пусто</p>
        <?php else: ?>
            <div class="ads-grid">
                <?php foreach ($favAds as $ad): ?>
                    <a href="<?= SITE_URL ?>/ad.php?id=<?= $ad['id'] ?>" class="ad-card">
                        <div class="ad-card-img"><?= $ad['category_icon'] ?></div>
                        <div class="ad-card-body">
                            <div class="ad-card-title"><?= e($ad['title']) ?></div>
                            <div class="ad-card-price <?= $ad['price'] === 0 ? 'free' : '' ?>">
                                <?= formatPrice($ad['price']) ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>