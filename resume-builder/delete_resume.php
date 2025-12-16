<?php
// delete_resume.php - Простой обработчик удаления резюме
require_once 'config.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Требуется авторизация';
    redirect('login.php');
}

// Получаем ID резюме
$resume_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

if ($resume_id <= 0) {
    $_SESSION['error'] = 'Неверный ID резюме';
    redirect('dashboard.php');
}

try {
    // Проверяем, существует ли резюме и принадлежит ли пользователю
    $stmt = $pdo->prepare("SELECT id, title FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $user_id]);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        $_SESSION['error'] = 'Резюме не найдено или у вас нет прав на его удаление';
        redirect('dashboard.php');
    }
    
    // Удаляем резюме
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
    $stmt->execute([$resume_id]);
    
    // Проверяем, было ли удалено
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Резюме "' . htmlspecialchars($resume['title']) . '" успешно удалено';
    } else {
        $_SESSION['error'] = 'Не удалось удалить резюме';
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Ошибка базы данных: ' . $e->getMessage();
}

// Возвращаем обратно на dashboard или my_resumes
$redirect = isset($_GET['from']) ? $_GET['from'] : 'dashboard.php';
redirect($redirect);
?>