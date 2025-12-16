<?php
// api/get_preview.php - Получение HTML предпросмотра резюме
require_once '../config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID резюме не указан');
}

$resume_id = intval($_GET['id']);

try {
    // Получаем данные резюме
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ?");
    $stmt->execute([$resume_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        die('Резюме не найдено');
    }
    
    // Получаем персональную информацию
    $stmt = $pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
    $stmt->execute([$resume_id]);
    $personal_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Получаем остальные данные
    $stmt = $pdo->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY start_date DESC");
    $stmt->execute([$resume_id]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY graduation_date DESC");
    $stmt->execute([$resume_id]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE resume_id = ? ORDER BY proficiency DESC");
    $stmt->execute([$resume_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Подключаем шаблон
    ob_start();
    include '../templates/' . $resume['template'] . '.php';
    $html = ob_get_clean();
    
    echo $html;
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Ошибка загрузки данных: ' . $e->getMessage() . '</div>';
}
?>
