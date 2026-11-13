<?php
require_once __DIR__ . '/functions.php';

$pageTitle = 'Свежие объявления';
$currentUser = currentUser();
$categories = getCategories();
$flash = getFlash();

$filters = [];
if (!empty($_GET['category'])) $filters['category'] = $_GET['category'];
if (!empty($_GET['search'])) $filters['search'] = trim($_GET['search']);
if (!empty($_GET['free'])) $filters['free'] = true;

$ads = getAds($filters);

require_once __DIR__ . '/includes/header.php';
?>

<section class="page">
    <h1 class="section-title">Свежие объявления</h1>
    <p class="section-subtitle">Найдите то, что нужно, или продайте ненужное</p>

    <form class="search-bar" method="GET" action="">
        <input type="text" name="search" placeholder="Поиск объявлений..." value="<?= e($_GET['search'] ?? '') ?>">
        <button type="submit">🔍 Найти</button>
    </form>

    <div class="filter-tags">
        <a href="<?= SITE_URL ?>/index.php" class="filter-tag <?= empty($_GET['category']) && empty($_GET['free']) ? 'active' : '' ?>">Все</a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= SITE_URL ?>/index.php?category=<?= e($cat['slug']) ?>"
               class="filter-tag <?= ($_GET['category'] ?? '') === $cat['slug'] ? 'active' : '' ?>">
                <?= e($cat['name']) ?>
            </a>
        <?php endforeach; ?>
        <a href="<?= SITE_URL ?>/index.php?free=1" class="filter-tag <?= !empty($_GET['free']) ? 'active' : '' ?>">Бесплатно</a>
    </div>

    <?php if (empty($ads)): ?>
        <p style="text-align:center; opacity:0.7; padding: 40px;">Объявления не найдены</p>
    <?php else: ?>
        <div class="ads-grid">
            <?php foreach ($ads as $ad):
                $isFav = $currentUser && isFavorite($currentUser['id'], $ad['id']);
                
                // Проверяем изображение
                $hasImage = !empty($ad['image']) 
                    && strpos($ad['image'], 'C:\\') === false 
                    && strpos($ad['image'], '/') === false 
                    && strpos($ad['image'], '\\') === false
                    && @file_exists(UPLOAD_DIR . $ad['image']);
            ?>
                <a href="<?= SITE_URL ?>/ad.php?id=<?= $ad['id'] ?>" class="ad-card">
                    <?php if ($ad['price'] === 0): ?>
                        <div class="ad-card-badge">Даром</div>
                    <?php endif; ?>
                    <button class="ad-card-fav <?= $isFav ? 'liked' : '' ?>"
                            onclick="toggleFav(event, <?= $ad['id'] ?>)"
                            data-fav="<?= $isFav ? '1' : '0' ?>">
                        <?= $isFav ? '❤️' : '♡' ?>
                    </button>
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
                            <span>📍 <?= e($ad['city']) ?></span>
                            <span><?= timeAgo($ad['created_at']) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<script>
function toggleFav(event, adId) {
    event.preventDefault();
    event.stopPropagation();
    fetch('<?= SITE_URL ?>/api/favorite.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ad_id: adId})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка');
        }
    });
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>