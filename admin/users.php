<?php
require_once __DIR__ . '/../functions.php';
requireAdmin();

$currentUser = currentUser();
$pageTitle = 'Управление пользователями';
$flash = getFlash();

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if (!$userId) {
        setFlash('error', 'Неверный ID пользователя');
        header('Location: ' . SITE_URL . '/admin/users.php');
        exit;
    }
    
    // Нельзя редактировать себя
    if ($userId === $currentUser['id']) {
        setFlash('error', 'Нельзя изменять свой аккаунт');
        header('Location: ' . SITE_URL . '/admin/users.php');
        exit;
    }
    
    if ($action === 'toggle_active') {
        // Блокировка/разблокировка
        $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            $newStatus = $user['is_active'] ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE users SET is_active = ? WHERE id = ?");
            $stmt->execute([$newStatus, $userId]);
            
            $actionText = $newStatus ? 'Разблокирован' : 'Заблокирован';
            logActivity('user_toggle_active', "Пользователь ID $userId $actionText");
            setFlash('success', "Пользователь $actionText");
        }
    } elseif ($action === 'change_role') {
        // Изменение роли
        $newRole = $_POST['role'] ?? 'user';
        if (!in_array($newRole, ['user', 'admin'])) {
            $newRole = 'user';
        }
        
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $userId]);
        
        logActivity('user_change_role', "Пользователь ID $userId получил роль $newRole");
        setFlash('success', 'Роль пользователя изменена');
    } elseif ($action === 'delete') {
        // Удаление пользователя
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        logActivity('user_delete', "Удалён пользователь ID $userId");
        setFlash('success', 'Пользователь удалён');
    }
    
    header('Location: ' . SITE_URL . '/admin/users.php');
    exit;
}

// Получаем всех пользователей
$stmt = $pdo->query("SELECT u.*, 
    (SELECT COUNT(*) FROM ads WHERE user_id = u.id) as ads_count,
    (SELECT COUNT(*) FROM favorites WHERE user_id = u.id) as favs_count
    FROM users u 
    ORDER BY u.created_at DESC");
$users = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<section class="page">
    <h1 class="section-title">Управление пользователями</h1>
    <p class="section-subtitle">Всего пользователей: <?= count($users) ?></p>

    <div style="max-width:1200px;margin:0 auto;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;background:rgba(253,236,209,0.05);border-radius:12px;overflow:hidden;">
                <thead>
                    <tr style="background:rgba(184,92,46,0.3);">
                        <th style="padding:15px;text-align:left;">ID</th>
                        <th style="padding:15px;text-align:left;">Имя</th>
                        <th style="padding:15px;text-align:left;">Email</th>
                        <th style="padding:15px;text-align:left;">Телефон</th>
                        <th style="padding:15px;text-align:left;">Роль</th>
                        <th style="padding:15px;text-align:left;">Статус</th>
                        <th style="padding:15px;text-align:left;">Объявления</th>
                        <th style="padding:15px;text-align:left;">Дата регистрации</th>
                        <th style="padding:15px;text-align:left;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr style="border-bottom:1px solid rgba(253,236,209,0.1);">
                            <td style="padding:15px;"><?= $user['id'] ?></td>
                            <td style="padding:15px;"><?= e($user['name']) ?></td>
                            <td style="padding:15px;"><?= e($user['email']) ?></td>
                            <td style="padding:15px;"><?= e($user['phone'] ?? '-') ?></td>
                            <td style="padding:15px;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="change_role">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role" onchange="this.form.submit()" style="padding:5px 10px;border-radius:5px;border:1px solid rgba(253,236,209,0.3);background:rgba(253,236,209,0.1);color:var(--text-color);">
                                        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Администратор</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding:15px;">
                                <span style="padding:4px 12px;border-radius:12px;font-size:0.85rem;<?= $user['is_active'] ? 'background:var(--accent-green);' : 'background:var(--accent-orange);' ?>">
                                    <?= $user['is_active'] ? 'Активен' : 'Заблокирован' ?>
                                </span>
                            </td>
                            <td style="padding:15px;"><?= $user['ads_count'] ?></td>
                            <td style="padding:15px;"><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                            <td style="padding:15px;">
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <?php if ($user['id'] !== $currentUser['id']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_active">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn <?= $user['is_active'] ? 'btn-outline' : 'btn-secondary' ?>" style="padding:6px 12px;font-size:0.8rem;">
                                                <?= $user['is_active'] ? '🔒 Заблокировать' : '🔓 Разблокировать' ?>
                                            </button>
                                        </form>
                                        
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Удалить пользователя и все его объявления?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-outline" style="padding:6px 12px;font-size:0.8rem;color:#ff4444;">
                                                🗑 Удалить
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="opacity:0.5;font-size:0.85rem;">Это вы</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div style="text-align:center;margin-top:30px;">
        <a href="<?= SITE_URL ?>/admin/index.php" class="btn btn-outline">← Вернуться в админку</a>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>