<?php
if (!function_exists('isLoggedIn')) {
    require_once __DIR__ . '/../functions.php';
}

$currentUser = currentUser();
$categories = getCategories();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Доска Объявлений') ?></title>
    <meta name="description" content="Бесплатная доска объявлений - размещение и поиск объявлений">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/style.css">
</head>
<body>
<div class="corkboard">

<header class="site-header">
    <a href="<?= SITE_URL ?>/index.php" class="logo">📌 Доска<span>Объявлений</span></a>
    <nav>
        <a href="<?= SITE_URL ?>/index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">Главная</a>
        <a href="<?= SITE_URL ?>/categories.php" class="<?= basename($_SERVER['PHP_SELF']) === 'categories.php' ? 'active' : '' ?>">Категории</a>
        <a href="<?= SITE_URL ?>/post.php" class="<?= basename($_SERVER['PHP_SELF']) === 'post.php' ? 'active' : '' ?>">Разместить</a>
        <a href="<?= SITE_URL ?>/rules.php" class="<?= basename($_SERVER['PHP_SELF']) === 'rules.php' ? 'active' : '' ?>">Правила</a>
        <a href="<?= SITE_URL ?>/help.php" class="<?= basename($_SERVER['PHP_SELF']) === 'help.php' ? 'active' : '' ?>">Помощь</a>
        <a href="<?= SITE_URL ?>/contacts.php" class="<?= basename($_SERVER['PHP_SELF']) === 'contacts.php' ? 'active' : '' ?>">Контакты</a>
        <div class="nav-auth">
            <?php if ($currentUser): ?>
                <?php if ($currentUser['role'] === 'admin'): ?>
                    <a href="<?= SITE_URL ?>/admin/index.php">Админка</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>/cabinet.php">Кабинет</a>
                <a href="<?= SITE_URL ?>/logout.php">Выйти</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php" class="<?= basename($_SERVER['PHP_SELF']) === 'login.php' ? 'active' : '' ?>">Войти</a>
                <a href="<?= SITE_URL ?>/register.php" class="<?= basename($_SERVER['PHP_SELF']) === 'register.php' ? 'active' : '' ?>">Регистрация</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php if ($flash): ?>
    <div class="notification <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>