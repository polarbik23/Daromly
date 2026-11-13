<?php
require_once __DIR__ . '/functions.php';

echo "<h2>🔧 Исправление изображений в базе данных</h2>";
echo "<hr>";

// 1. Смотрим, что сейчас в базе
$stmt = $pdo->query("SELECT id, title, image FROM ads WHERE image IS NOT NULL AND image != '' LIMIT 20");
$rows = $stmt->fetchAll();

echo "<h3>📋 Текущие записи в поле image (первые 20):</h3>";
echo "<table border='1' cellpadding='8' style='border-collapse:collapse;'>";
echo "<tr><th>ID</th><th>Название</th><th>Значение image</th><th>Действие</th></tr>";

$fixed = 0;
foreach ($rows as $row) {
    $image = $row['image'];
    $isBad = (strpos($image, 'C:\\') !== false) 
          || (strpos($image, '/') !== false && strpos($image, 'uploads/') === 0)
          || (strpos($image, '\\') !== false);
    
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>" . htmlspecialchars($row['title']) . "</td>";
    echo "<td style='color:" . ($isBad ? 'red' : 'green') . ";'>" . htmlspecialchars($image) . "</td>";
    
    if ($isBad) {
        // Исправляем: берём только имя файла из пути
        $fileName = basename(str_replace('\\', '/', $image));
        
        // Проверяем, существует ли файл
        if (file_exists(UPLOAD_DIR . $fileName)) {
            $pdo->prepare("UPDATE ads SET image = ? WHERE id = ?")->execute([$fileName, $row['id']]);
            echo "<td style='color:green;'>✅ Исправлено → $fileName</td>";
            $fixed++;
        } else {
            // Файла нет — очищаем поле
            $pdo->prepare("UPDATE ads SET image = NULL WHERE id = ?")->execute([$row['id']]);
            echo "<td style='color:orange;'>⚠️ Файл не найден, очищено</td>";
            $fixed++;
        }
    } else {
        echo "<td style='color:green;'>✓ OK</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>📊 Результат:</h3>";
echo "<p>Исправлено записей: <b>$fixed</b></p>";

// Проверяем константы
echo "<hr>";
echo "<h3>⚙️ Проверка настроек:</h3>";
echo "<p>UPLOAD_DIR: <code>" . UPLOAD_DIR . "</code></p>";
echo "<p>Папка существует: " . (is_dir(UPLOAD_DIR) ? '✅ Да' : '❌ Нет') . "</p>";
echo "<p>Папка доступна для записи: " . (is_writable(UPLOAD_DIR) ? '✅ Да' : '❌ Нет') . "</p>";

// Проверяем файлы в uploads
echo "<h3>📁 Файлы в папке uploads:</h3>";
$files = glob(UPLOAD_DIR . '*');
if ($files) {
    echo "<ul>";
    foreach ($files as $file) {
        echo "<li>" . basename($file) . " (" . filesize($file) . " байт)</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Папка uploads пустая</p>";
}

echo "<hr>";
echo "<p><a href='index.php'>← На главную</a> | <a href='fix_images.php'>🔄 Повторить проверку</a></p>";
echo "<p style='color:red;'><b>⚠️ УДАЛИТЕ ЭТОТ ФАЙЛ ПОСЛЕ ИСПРАВЛЕНИЯ!</b></p>";