<?php
session_start();

// Настройки базы данных
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resume_builder');

// Настройки приложения
define('SITE_URL', 'http://localhost/resume-builder/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/resume-builder/uploads/');

// Автозагрузка классов
spl_autoload_register(function($class) {
    require_once 'classes/' . $class . '.php';
});

// Подключение к базе данных
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8");
} catch(PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}

// Функции для работы с пользователями
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}
?>