<?php
require_once __DIR__ . '/functions.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/cabinet.php');
    exit;
}

$pageTitle = 'Регистрация';
$currentUser = currentUser();
$flash = getFlash();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Ошибка безопасности';
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $agree = $_POST['agree'] ?? false;

    if (empty($name)) $errors[] = 'Укажите имя';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Некорректный email';
    
    $passwordErrors = validatePassword($password);
    if (!empty($passwordErrors)) {
        $errors = array_merge($errors, $passwordErrors);
    }
    
    if ($password !== $password2) $errors[] = 'Пароли не совпадают';
    if (!$agree) $errors[] = 'Необходимо согласие с правилами';

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Пользователь с таким email уже существует';
        }
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $hash]);
        $userId = $pdo->lastInsertId();

        logActivity('user_register', "Регистрация: $email");
        
        $_SESSION['user_id'] = $userId;
        setFlash('success', 'Регистрация успешна! Добро пожаловать!');
        header('Location: ' . SITE_URL . '/cabinet.php');
        exit;
    }
}

$csrfToken = generateCSRFToken();
require_once __DIR__ . '/includes/header.php';
?>

<section class="page">
    <h1 class="section-title">Регистрация</h1>
    <p class="section-subtitle">Создайте аккаунт для размещения объявлений</p>

    <?php if (!empty($errors)): ?>
        <div style="max-width:600px;margin:0 auto 20px;background:rgba(184,92,46,0.3);padding:16px;border-radius:12px;">
            <?php foreach ($errors as $err): ?>
                <p style="color:var(--accent-cream);">⚠️ <?= e($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            
            <div class="form-group">
                <label>Имя *</label>
                <input type="text" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Телефон</label>
                <input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" 
                       pattern="\+7\s?\(?\d{3}\)?\s?\d{3}-?\d{2}-?\d{2}">
            </div>
            <div class="form-group">
                <label>Пароль * (мин. 8 символов, заглавная, строчная, цифра, спецсимвол)</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Повторите пароль *</label>
                <input type="password" name="password2" required>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="agree" required> 
                    Я согласен с <a href="<?= SITE_URL ?>/rules.php" target="_blank">правилами</a> и 
                    <a href="<?= SITE_URL ?>/privacy.php" target="_blank">политикой конфиденциальности</a>
                </label>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-full">Зарегистрироваться</button>
            </div>
            <div class="form-link">
                Уже есть аккаунт? <a href="<?= SITE_URL ?>/login.php">Войти</a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>