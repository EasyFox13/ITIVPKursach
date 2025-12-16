<?php
// login.php - Страница входа в систему

// Включаем конфигурацию
require_once 'config.php';

// Если пользователь уже авторизован, перенаправляем в кабинет
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Переменные для сообщений
$error = '';
$success = '';

// Обработка формы входа
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Валидация
    if (empty($username) || empty($password)) {
        $error = 'Введите имя пользователя и пароль';
    } else {
        try {
            // Ищем пользователя в базе данных
            $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Проверяем пароль
                if (password_verify($password, $user['password_hash'])) {
                    // Устанавливаем сессию
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Обновляем время последнего входа (если нужно)
                    // $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    // $stmt->execute([$user['id']]);
                    
                    // Перенаправляем на главную или на запрошенную страницу
                    if (isset($_SESSION['redirect_url'])) {
                        $redirect_url = $_SESSION['redirect_url'];
                        unset($_SESSION['redirect_url']);
                        redirect($redirect_url);
                    } else {
                        redirect('dashboard.php');
                    }
                } else {
                    $error = 'Неверный пароль';
                }
            } else {
                $error = 'Пользователь не найден';
            }
        } catch (PDOException $e) {
            $error = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход в систему - Генератор резюме</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Свои стили -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>assets/css/style.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 100%;
            padding: 20px;
        }
        
        .login-card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: none;
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #4a6fa5 0%, #166088 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        
        .login-body {
            padding: 2rem;
            background-color: white;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4a6fa5;
            box-shadow: 0 0 0 0.25rem rgba(74, 111, 165, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #4a6fa5 0%, #166088 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 111, 165, 0.3);
        }
        
        .login-footer {
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
            margin-top: 1.5rem;
            color: #666;
        }
        
        .login-footer a {
            color: #4a6fa5;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .logo {
            font-size: 2rem;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="login-container">
                    <div class="login-card">
                        <div class="login-header">
                            <div class="logo">
                                <i class="bi bi-file-earmark-person"></i>
                            </div>
                            <h2>Вход в систему</h2>
                            <p class="mb-0">Добро пожаловать в ResumeBuilder</p>
                        </div>
                        
                        <div class="login-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?php echo htmlspecialchars($error); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?php echo htmlspecialchars($success); ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm">
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person me-1"></i>Имя пользователя или Email
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="username" 
                                           name="username" 
                                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                                           required
                                           placeholder="Введите имя пользователя или email">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-key me-1"></i>Пароль
                                    </label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control" 
                                               id="password" 
                                               name="password" 
                                               required
                                               placeholder="Введите пароль">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Запомнить меня</label>
                                </div>
                                
                                <button type="submit" class="btn btn-login mb-3">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Войти
                                </button>
                                
                                <div class="text-center mb-3">
                                    <a href="forgot_password.php" class="text-decoration-none">
                                        <i class="bi bi-question-circle me-1"></i>Забыли пароль?
                                    </a>
                                </div>
                            </form>
                            
                            <div class="login-footer">
                                <p>Ещё нет аккаунта? <a href="register.php">Зарегистрируйтесь</a></p>
                                <p class="mb-0">
                                    <a href="<?php echo SITE_URL; ?>">
                                        <i class="bi bi-arrow-left me-1"></i>Вернуться на главную
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Дополнительная информация -->
                    <div class="text-center mt-3">
                        <p class="text-muted small">
                            <i class="bi bi-shield-check me-1"></i>
                            Ваши данные защищены
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Показать/скрыть пароль
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
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
        
        // Очистка ошибок при вводе
        document.querySelectorAll('#loginForm input').forEach(input => {
            input.addEventListener('input', function() {
                const alert = document.querySelector('.alert');
                if (alert) {
                    alert.remove();
                }
            });
        });
        
        // Фокус на поле ввода при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            const usernameInput = document.getElementById('username');
            if (usernameInput.value === '') {
                usernameInput.focus();
            }
        });
        
        // Валидация формы
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Пожалуйста, заполните все поля');
                return false;
            }
            
            // Можно добавить дополнительную валидацию
            if (username.length < 3) {
                e.preventDefault();
                alert('Имя пользователя должно содержать минимум 3 символа');
                return false;
            }
            
            return true;
        });
    </script>
</body>
</html>