<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

$currentUser = currentUser();
$pageTitle = 'Управление категориями';
$flash = getFlash();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $color = trim($_POST['color'] ?? '#3F6B5A');
        
        if ($name && $slug && $icon) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon, color) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $slug, $icon, $color]);
            logActivity('category_add', "Добавлена категория: $name");
            setFlash('success', 'Категория добавлена');
        } else {
            setFlash('error', 'Заполните все поля');
        }
    } elseif ($action === 'delete') {
        $catId = (int)($_POST['category_id'] ?? 0);
        if ($catId) {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$catId]);
            logActivity('category_delete', "Удалена категория ID $catId");
            setFlash('success', 'Категория удалена');
        }
    }
    
    header('Location: ' . SITE_URL . '/admin/categories.php');
    exit;
}

$categories = getCategories();
require_once __DIR__ . '/../includes/header.php';
?>

<section class="page">
    <h1 class="section-title">Управление категориями</h1>

    <div style="max-width:800px;margin:0 auto 40px;">
        <h3 style="margin-bottom:20px;">Добавить категорию</h3>
        <form method="POST" style="background:rgba(253,236,209,0.08);padding:24px;border-radius:12px;">
            <input type="hidden" name="action" value="add">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;margin-bottom:8px;">Название *</label>
                    <input type="text" name="name" required style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(253,236,209,0.3);background:rgba(253,236,209,0.1);color:var(--text-color);">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;">Slug (URL) *</label>
                    <input type="text" name="slug" required placeholder="например: transport" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(253,236,209,0.3);background:rgba(253,236,209,0.1);color:var(--text-color);">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;margin-bottom:15px;">
                <div>
                    <label style="display:block;margin-bottom:8px;">Иконка (эмодзи) *</label>
                    <input type="text" name="icon" required placeholder="🚗" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(253,236,209,0.3);background:rgba(253,236,209,0.1);color:var(--text-color);">
                </div>
                <div>
                    <label style="display:block;margin-bottom:8px;">Цвет</label>
                    <input type="color" name="color" value="#3F6B5A" style="width:100%;padding:5px;border-radius:8px;border:1px solid rgba(253,236,209,0.3);background:rgba(253,236,209,0.1);">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Добавить категорию</button>
        </form>
    </div>

    <div style="max-width:800px;margin:0 auto;">
        <h3 style="margin-bottom:20px;">Существующие категории</h3>
        <div style="display:grid;gap:15px;">
            <?php foreach ($categories as $cat): ?>
                <div style="background:rgba(253,236,209,0.08);padding:20px;border-radius:12px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:15px;">
                        <span style="font-size:2rem;"><?= $cat['icon'] ?></span>
                        <div>
                            <div style="font-weight:600;"><?= e($cat['name']) ?></div>
                            <div style="font-size:0.85rem;opacity:0.7;">Slug: <?= e($cat['slug']) ?></div>
                        </div>
                    </div>
                    <form method="POST" onsubmit="return confirm('Удалить категорию? Объявления в этой категории тоже будут удалены!');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="btn btn-outline" style="padding:8px 16px;font-size:0.85rem;color:#ff4444;">🗑 Удалить</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="text-align:center;margin-top:30px;">
        <a href="<?= SITE_URL ?>/admin/index.php" class="btn btn-outline">← Вернуться в админку</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>