<?php
require_once __DIR__ . '/functions.php';

$currentUser = currentUser();
$categories = getCategories();
$flash = getFlash();

if (empty($_GET['id'])) {
    header('Location: ' . SITE_URL . '/index.php');
    exit;
}

$ad = getAd((int)$_GET['id']);
if (!$ad) {
    echo '<section class="page"><p style="text-align:center;padding:60px;">Объявление не найдено</p></section>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$isFav = $currentUser && isFavorite($currentUser['id'], $ad['id']);
$pageTitle = $ad['title'];

// Проверяем изображение
$hasImage = !empty($ad['image']) 
    && strpos($ad['image'], 'C:\\') === false 
    && strpos($ad['image'], '/') === false 
    && strpos($ad['image'], '\\') === false
    && @file_exists(UPLOAD_DIR . $ad['image']);

require_once __DIR__ . '/includes/header.php';
?>

<section class="page">
    <div class="ad-detail">
        <div class="ad-detail-img">
            <?php if ($hasImage): ?>
                <img src="<?= SITE_URL ?>/uploads/<?= e($ad['image']) ?>" alt="<?= e($ad['title']) ?>">
            <?php else: ?>
                <span style="font-size:6rem;"><?= $ad['category_icon'] ?></span>
            <?php endif; ?>
        </div>
        <div class="ad-detail-info">
            <h2><?= e($ad['title']) ?></h2>
            <div class="ad-detail-price <?= $ad['price'] === 0 ? 'free' : '' ?>">
                <?= formatPrice($ad['price']) ?>
            </div>
            <div class="ad-detail-desc"><?= nl2br(e($ad['description'])) ?></div>
            <div class="ad-detail-meta">
                <p><strong>Категория:</strong> <?= e($ad['category_name']) ?></p>
                <p><strong>Город:</strong> <?= e($ad['city']) ?></p>
                <p><strong>Автор:</strong> <?= e($ad['author_name']) ?></p>
                <?php if (!empty($ad['phone'])): ?>
                    <p><strong>Телефон:</strong> <?= e($ad['phone']) ?></p>
                <?php endif; ?>
                <p><strong>Опубликовано:</strong> <?= timeAgo($ad['created_at']) ?></p>
            </div>
            <?php if ($currentUser): ?>
                <button class="btn <?= $isFav ? 'btn-secondary' : 'btn-primary' ?>"
                        onclick="toggleFav(event, <?= $ad['id'] ?>)">
                    <?= $isFav ? '❤️ В избранном' : '♡ В избранное' ?>
                </button>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="btn btn-primary">Войдите, чтобы добавить в избранное</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
function toggleFav(event, adId) {
    event.preventDefault();
    fetch('<?= SITE_URL ?>/api/favorite.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ad_id: adId})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message);
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>