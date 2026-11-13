<?php
$pageTitle = 'Все категории';
require_once __DIR__ . '/includes/header.php';

// Считаем количество объявлений в каждой категории
$stmt = $pdo->query("SELECT c.*, COUNT(a.id) as count
    FROM categories c
    LEFT JOIN ads a ON c.id = a.category_id AND a.status = 'active'
    GROUP BY c.id
    ORDER BY c.name");
$categoriesWithCount = $stmt->fetchAll();
?>

<section class="page">
    <h1 class="section-title">Все категории</h1>
    <p class="section-subtitle">Выберите интересующую вас категорию</p>

    <div class="categories-grid">
        <?php foreach ($categoriesWithCount as $cat): ?>
            <a href="<?= SITE_URL ?>/index.php?category=<?= e($cat['slug']) ?>"
               class="category-card"
               style="background: linear-gradient(135deg, <?= e($cat['color']) ?>, <?= e($cat['color']) ?>dd);">
                <div class="category-icon"><?= e($cat['icon']) ?></div>
                <div class="category-name"><?= e($cat['name']) ?></div>
                <div class="category-count"><?= $cat['count'] ?> объявлений</div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>