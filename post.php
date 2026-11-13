<?php
require_once __DIR__ . '/functions.php';
requireAuth();

$currentUser = currentUser();
$pageTitle = 'Разместить объявление';
$categories = getCategories();
$flash = getFlash();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности. Обновите страницу.';
    }

    $title = trim($_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (int)($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($title)) $errors[] = 'Укажите название';
    if (strlen($title) > 100) $errors[] = 'Название не должно превышать 100 символов';
    if (!$category_id) $errors[] = 'Выберите категорию';
    if (empty($description)) $errors[] = 'Добавьте описание';
    if (strlen($description) > 5000) $errors[] = 'Описание не должно превышать 5000 символов';
    if (empty($city)) $errors[] = 'Укажите город';

    // Обработка фото - сохраняем ТОЛЬКО имя файла
    $imageName = null;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['image']['size'] > MAX_FILE_SIZE) {
            $errors[] = 'Размер файла не должен превышать 5 МБ';
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                $errors[] = 'Недопустимый формат изображения';
            } else {
                // ВАЖНО: только имя файла, без пути!
                $imageName = uniqid('ad_') . '.' . $ext;
                $destination = UPLOAD_DIR . $imageName;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    $errors[] = 'Ошибка загрузки файла';
                    $imageName = null;
                }
            }
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO ads (user_id, category_id, title, description, price, city, phone, image, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $currentUser['id'], $category_id, $title, $description,
            $price, $city, $phone, $imageName
        ]);
        
        logActivity('ad_create', "Создано объявление: $title");
        setFlash('success', 'Объявление отправлено на модерацию!');
        header('Location: ' . SITE_URL . '/cabinet.php');
        exit;
    }
}

$csrfToken = generateCSRFToken();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page">
    <h1 class="section-title">Разместить объявление</h1>
    <p class="section-subtitle">Заполните форму и ваше объявление появится на сайте после модерации</p>

    <?php if (!empty($errors)): ?>
        <div style="max-width:600px;margin:0 auto 20px;background:rgba(184,92,46,0.3);padding:16px;border-radius:12px;">
            <?php foreach ($errors as $err): ?>
                <p style="color:var(--accent-cream);">⚠️ <?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            
            <div class="form-group">
                <label>Название объявления * (до 100 символов)</label>
                <input type="text" name="title" value="<?= e($_POST['title'] ?? '') ?>" maxlength="100" required>
            </div>

            <div class="form-group">
                <label>Категория *</label>
                <select name="category_id" required>
                    <option value="">Выберите категорию</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['icon']) ?> <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Цена (₽) — 0 если бесплатно</label>
                <input type="number" name="price" value="<?= e($_POST['price'] ?? '0') ?>" min="0">
            </div>

            <div class="form-group">
                <label>Описание * (до 5000 символов)</label>
                <textarea name="description" maxlength="5000" required><?= e($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Фотография (JPG, PNG, WebP, до 5 МБ)</label>
                <div class="upload-area" onclick="document.getElementById('adImage').click()">
                    <div style="font-size:2.5rem;">📷</div>
                    <div style="font-weight:300;opacity:0.7;">Нажмите, чтобы загрузить фото</div>
                </div>
                <input type="file" id="adImage" name="image" accept="image/*" style="display:none" onchange="previewImage(this)">
                <div id="photoPreview" style="margin-top:10px;"></div>
            </div>

            <div class="form-group">
                <label>Город *</label>
                <input type="text" name="city" value="<?= e($_POST['city'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label>Телефон для связи</label>
                <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" 
                       pattern="\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}" 
                       placeholder="+7 (___) ___-__-__">
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" required> Я согласен с <a href="<?= SITE_URL ?>/rules.php" target="_blank">правилами размещения</a>
                </label>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-full">Отправить на модерацию</button>
            </div>
        </form>
    </div>
</section>

<script>
function previewImage(input) {
    const preview = document.getElementById('photoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" style="max-height:150px;border-radius:8px;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>