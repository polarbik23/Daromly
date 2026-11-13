<?php
$pageTitle = 'Контакты';
require_once __DIR__ . '/includes/header.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = true;
    setFlash('success', 'Сообщение отправлено! Мы ответим в ближайшее время.');
}
?>
<section class="page">
    <h1 class="section-title">Контакты</h1>
    <p class="section-subtitle">Свяжитесь с нами любым удобным способом</p>

    <div class="contact-grid">
        <div class="contact-card">
            <div class="contact-card-icon">📧</div>
            <div class="contact-card-label">Email</div>
            <div class="contact-card-value">support@daska.ru</div>
        </div>
        <div class="contact-card">
            <div class="contact-card-icon">📞</div>
            <div class="contact-card-label">Телефон</div>
            <div class="contact-card-value">+7 (800) 123-45-67</div>
        </div>
        <div class="contact-card">
            <div class="contact-card-icon">💬</div>
            <div class="contact-card-label">Telegram</div>
            <div class="contact-card-value">@daska_support</div>
        </div>
        <div class="contact-card">
            <div class="contact-card-icon">🕐</div>
            <div class="contact-card-label">Время работы</div>
            <div class="contact-card-value">Пн–Пт: 9:00–18:00</div>
        </div>
    </div>

    <div class="form-container" style="margin-top:40px;">
        <h3 style="font-family:var(--font-heading);font-weight:700;font-size:1.3rem;margin-bottom:20px;text-align:center;">Написать нам</h3>
        <form method="POST">
            <div class="form-group">
                <label>Ваше имя</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Сообщение</label>
                <textarea name="message" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Отправить</button>
        </form>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>