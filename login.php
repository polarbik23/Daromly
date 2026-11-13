<?php
$pageTitle = 'Авторизация';
require_once __DIR__ . '/includes/header.php';

if (isLoggedIn()) {
    header('Location: ' . SITE_URL . '/cabinet.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        setFlash('success', 'Вы успешно вошли! 🎉');
        header('Location: ' . SITE_URL . '/cabinet.php');
        exit;
    } else {
        $error = 'Неверный email или пароль';
    }
}
?>

<section class="page">
    <h1 class="section-title">Авторизация</h1>
    <p class="section-subtitle">Войдите в свой аккаунт</p>

    <div class="form-container">
        <?php if ($error): ?>
            <div style="background:rgba(184,92,46,0.3);padding:12px;border-radius:8px;margin-bottom:16px;text-align:center;">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Пароль</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-full">Войти</button>
            </div>
            <div class="form-link">
                Нет аккаунта? <a href="<?= SITE_URL ?>/register.php">Зарегистрироваться</a>
            </div>
        </form>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>