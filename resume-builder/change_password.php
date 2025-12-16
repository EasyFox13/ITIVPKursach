<?php
// change_password.php - Смена пароля пользователя
require_once 'config.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Для смены пароля необходимо авторизоваться';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$pageTitle = 'Смена пароля';

// Обработка формы смены пароля
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Проверка введенных данных
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Все поля обязательны для заполнения';
    } elseif ($new_password !== $confirm_password) {
        $_SESSION['error'] = 'Новый пароль и подтверждение не совпадают';
    } elseif (strlen($new_password) < 6) {
        $_SESSION['error'] = 'Новый пароль должен содержать минимум 6 символов';
    } else {
        try {
            // Получаем текущий хеш пароля из БД (поле password_hash)
            $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($current_password, $user['password_hash'])) {
                // Хешируем новый пароль
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Обновляем пароль в БД (поле password_hash)
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                $_SESSION['success'] = 'Пароль успешно изменен!';
                
                // Можно добавить логирование смены пароля
                $log_message = "Пользователь ID $user_id сменил пароль";
                error_log(date('Y-m-d H:i:s') . " - $log_message\n", 3, "logs/security.log");
                
                // Редирект на профиль с сообщением об успехе
                redirect('profile.php');
            } else {
                $_SESSION['error'] = 'Текущий пароль неверный';
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="bi bi-key me-2"></i>Смена пароля
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Сообщения об ошибках/успехе -->
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <!-- Текущий пароль -->
                        <div class="mb-3">
                            <label for="current_password" class="form-label">
                                <i class="bi bi-lock me-1"></i>Текущий пароль
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required 
                                       placeholder="Введите текущий пароль">
                                <button type="button" class="btn btn-outline-secondary toggle-password" 
                                        data-target="current_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Новый пароль -->
                        <div class="mb-3">
                            <label for="new_password" class="form-label">
                                <i class="bi bi-key me-1"></i>Новый пароль
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required 
                                       placeholder="Минимум 6 символов">
                                <button type="button" class="btn btn-outline-secondary toggle-password" 
                                        data-target="new_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                Пароль должен содержать минимум 6 символов
                            </div>
                        </div>
                        
                        <!-- Подтверждение пароля -->
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">
                                <i class="bi bi-key-fill me-1"></i>Подтверждение пароля
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required 
                                       placeholder="Повторите новый пароль">
                                <button type="button" class="btn btn-outline-secondary toggle-password" 
                                        data-target="confirm_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="d-flex justify-content-between">
                            <a href="profile.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-1"></i>Назад к профилю
                            </a>
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Сменить пароль
                            </button>
                        </div>
                    </form>
                    
                    <!-- Подсказки по безопасности -->
                    <div class="mt-4 pt-3 border-top">
                        <h6><i class="bi bi-shield-check me-2"></i>Советы по безопасности:</h6>
                        <ul class="small text-muted mb-0">
                            <li>Используйте уникальный пароль для этого сайта</li>
                            <li>Сочетайте буквы, цифры и специальные символы</li>
                            <li>Не используйте личную информацию в пароле</li>
                            <li>Рекомендуется менять пароль каждые 3-6 месяцев</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили для страницы смены пароля */
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 2px solid #e9ecef;
        padding: 1.25rem 1.5rem;
    }
    
    .toggle-password:hover {
        background-color: #e9ecef;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Автоматическое скрытие алертов
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Переключение видимости пароля
        document.querySelectorAll('.toggle-password').forEach(function(button) {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
        
        // Проверка совпадения паролей в реальном времени
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function checkPasswords() {
            if (newPassword.value && confirmPassword.value) {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.style.borderColor = '#dc3545';
                } else {
                    confirmPassword.style.borderColor = '#198754';
                }
            } else {
                confirmPassword.style.borderColor = '';
            }
        }
        
        newPassword.addEventListener('input', checkPasswords);
        confirmPassword.addEventListener('input', checkPasswords);
        
        // Фокус на первом поле при загрузке
        document.getElementById('current_password').focus();
    });
</script>

<?php include 'includes/footer.php'; ?>