<?php
// view_resume.php - Просмотр готового резюме
require_once 'config.php';

// Проверяем наличие ID резюме
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Резюме не найдено';
    redirect('index.php');
}

$resume_id = intval($_GET['id']);

// Обработка действий с резюме
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete_resume']) && isLoggedIn()) {
        // Проверяем принадлежность резюме
        $stmt = $pdo->prepare("SELECT user_id FROM resumes WHERE id = ?");
        $stmt->execute([$resume_id]);
        $resume_owner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resume_owner && $resume_owner['user_id'] == $_SESSION['user_id']) {
            try {
                // Удаляем связанные данные
                $tables = ['personal_info', 'experience', 'education', 'skills'];
                foreach ($tables as $table) {
                    $stmt = $pdo->prepare("DELETE FROM $table WHERE resume_id = ?");
                    $stmt->execute([$resume_id]);
                }
                
                // Удаляем само резюме
                $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
                $stmt->execute([$resume_id]);
                
                $_SESSION['success'] = 'Резюме успешно удалено';
                redirect('my_resumes.php');
                
            } catch (PDOException $e) {
                $_SESSION['error'] = 'Ошибка при удалении резюме: ' . $e->getMessage();
                redirect("view_resume.php?id=$resume_id");
            }
        }
    }
    
    if (isset($_POST['duplicate_resume']) && isLoggedIn()) {
        try {
            // Получаем данные исходного резюме
            $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
            $stmt->execute([$resume_id, $_SESSION['user_id']]);
            $original_resume = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($original_resume) {
                // Создаем новое резюме
                $stmt = $pdo->prepare("INSERT INTO resumes (user_id, title, template, created_at, updated_at) 
                                       VALUES (?, ?, ?, NOW(), NOW())");
                $new_title = "Копия: " . $original_resume['title'];
                $stmt->execute([$_SESSION['user_id'], $new_title, $original_resume['template']]);
                $new_resume_id = $pdo->lastInsertId();
                
                // Копируем персональную информацию
                $stmt = $pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
                $stmt->execute([$resume_id]);
                $personal_info = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($personal_info) {
                    $stmt = $pdo->prepare("INSERT INTO personal_info 
                                          (resume_id, full_name, email, phone, address, summary, website, linkedin)
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $new_resume_id,
                        $personal_info['full_name'],
                        $personal_info['email'],
                        $personal_info['phone'],
                        $personal_info['address'],
                        $personal_info['summary'],
                        $personal_info['website'],
                        $personal_info['linkedin']
                    ]);
                }
                
                $_SESSION['success'] = 'Резюме успешно продублировано';
                redirect("edit_resume.php?id=$new_resume_id");
            }
            
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Ошибка при дублировании резюме: ' . $e->getMessage();
            redirect("view_resume.php?id=$resume_id");
        }
    }
}

// Получаем информацию о резюме
try {
    // Если пользователь авторизован, проверяем принадлежность резюме
    $user_condition = isLoggedIn() ? '' : 'AND public_view = 1';
    $params = [$resume_id];
    
    // Получаем основную информацию о резюме
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? $user_condition");
    $stmt->execute($params);
    $resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resume) {
        $_SESSION['error'] = 'Резюме не найдено или у вас нет доступа к нему';
        redirect('index.php');
    }
    
    // Получаем персональную информацию
    $stmt = $pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
    $stmt->execute([$resume_id]);
    $personal_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Получаем опыт работы
    $stmt = $pdo->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY start_date DESC");
    $stmt->execute([$resume_id]);
    $experience = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем образование
    $stmt = $pdo->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY graduation_date DESC");
    $stmt->execute([$resume_id]);
    $education = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем навыки
    $stmt = $pdo->prepare("SELECT * FROM skills WHERE resume_id = ? ORDER BY proficiency DESC, skill_name ASC");
    $stmt->execute([$resume_id]);
    $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Получаем информацию о пользователе (владельце резюме)
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$resume['user_id']]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Ошибка базы данных: ' . $e->getMessage();
    redirect('index.php');
}

$pageTitle = "Резюме: " . htmlspecialchars($resume['title']);

// В зависимости от шаблона подключаем соответствующий файл
$template_file = $resume['template'] . '.php';
$template_path = 'templates/' . $template_file;

// Проверяем существование шаблона
if (!file_exists($template_path)) {
    $template_path = 'templates/classic.php';
}

// Подключаем шаблон напрямую
ob_start();
include $template_path;
$resume_content = ob_get_clean();
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <!-- Заголовок и кнопки управления -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1><?php echo htmlspecialchars($resume['title']); ?></h1>
            <p class="text-muted mb-0">
                <i class="bi bi-person me-1"></i> Владелец: <?php echo htmlspecialchars($owner['username']); ?>
                <span class="mx-2">•</span>
                <i class="bi bi-calendar me-1"></i> Обновлено: <?php echo date('d.m.Y', strtotime($resume['updated_at'])); ?>
                <span class="mx-2">•</span>
                <i class="bi bi-layers me-1"></i> Шаблон: 
                <?php 
                switch($resume['template']) {
                    case 'classic': echo 'Классический'; break;
                    case 'modern': echo 'Современный'; break;
                    case 'creative': echo 'Креативный'; break;
                    default: echo htmlspecialchars($resume['template']);
                }
                ?>
            </p>
        </div>
        
        <div class="btn-toolbar">
            <div class="btn-group me-2">
                <?php if (isLoggedIn() && $resume['user_id'] == $_SESSION['user_id']): ?>
                    <a href="edit_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i> Редактировать
                    </a>
                <?php endif; ?>
                
                <button type="button" class="btn btn-outline-success" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Печать
                </button>
                
                <?php if (isLoggedIn() && $resume['user_id'] == $_SESSION['user_id']): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i> Действия
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <form method="POST" action="" style="display: inline;">
                                    <button type="submit" name="duplicate_resume" class="dropdown-item" 
                                            onclick="return confirm('Создать копию этого резюме?')">
                                        <i class="bi bi-files me-2"></i> Дублировать
                                    </button>
                                </form>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger" 
                                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash me-2"></i> Удалить
                                </button>
                            </li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Сообщения об ошибках/успехе -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <!-- Основное содержимое резюме (подключенный шаблон) -->
    <div class="resume-preview-container mb-4">
        <?php echo $resume_content; ?>
    </div>
    
    <!-- Информация о доступе -->
    <div class="card mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="bi bi-info-circle me-2"></i>Информация о резюме</h5>
                    <ul class="list-unstyled">
                        <li><strong>Статус:</strong> 
                            <span class="badge <?php echo $resume['public_view'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $resume['public_view'] ? 'Публичное' : 'Приватное'; ?>
                            </span>
                        </li>
                        <li><strong>Создано:</strong> <?php echo date('d.m.Y H:i', strtotime($resume['created_at'])); ?></li>
                        <li><strong>Обновлено:</strong> <?php echo date('d.m.Y H:i', strtotime($resume['updated_at'])); ?></li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h5><i class="bi bi-share me-2"></i>Поделиться</h5>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="share-url" 
                               value="<?php echo SITE_URL . 'view_resume.php?id=' . $resume_id; ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyShareUrl()">
                            <i class="bi bi-clipboard"></i>
                        </button>
                    </div>
                    <?php if ($resume['public_view']): ?>
                        <div class="btn-group">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . 'view_resume.php?id=' . $resume_id); ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-facebook"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . 'view_resume.php?id=' . $resume_id); ?>&text=<?php echo urlencode('Посмотрите мое резюме!'); ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-twitter"></i>
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(SITE_URL . 'view_resume.php?id=' . $resume_id); ?>" 
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-linkedin"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted"><small>Только вы можете просматривать это резюме</small></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить резюме "<strong><?php echo htmlspecialchars($resume['title']); ?></strong>"?</p>
                <p class="text-danger"><small>Это действие нельзя отменить.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form method="POST" action="" style="display: inline;">
                    <button type="submit" name="delete_resume" class="btn btn-danger">
                        Удалить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили для контейнера резюме */
    .resume-preview-container {
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    /* Стили для печати */
   /* Стили для печати */
@media print {
    /* Скрываем все элементы интерфейса (кнопки, меню, алерты и т.д.) */
    .btn-toolbar, .card, .modal, .alert, .dropdown-menu {
        display: none !important;
    }
    
    /* 1. По умолчанию скрываем ВСЁ содержимое страницы */
    body * {
        visibility: hidden;
    }
    
    /* Базовые настройки страницы */
    body {
        background-color: white !important;
        font-size: 12pt !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* 2. Делаем видимым ТОЛЬКО контейнер с резюме и всё внутри него */
    .resume-preview-container,
    .resume-preview-container * {
        visibility: visible !important;
    }
    
    /* 3. Позиционируем контейнер резюме в начале страницы без отступов */
    .resume-preview-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }
}
    
    /* Адаптивность для экранов */
    @media (max-width: 768px) {
        .resume-preview-container {
            padding: 15px;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 15px;
        }
        
        .btn-toolbar .btn-group {
            flex-wrap: wrap;
            gap: 5px;
        }
    }
</style>

<script>
    // Копирование ссылки для общего доступа
    function copyShareUrl() {
        const shareUrl = document.getElementById('share-url');
        shareUrl.select();
        shareUrl.setSelectionRange(0, 99999);
        
        navigator.clipboard.writeText(shareUrl.value)
            .then(() => {
                // Показываем временное уведомление
                const originalText = event.target.innerHTML;
                event.target.innerHTML = '<i class="bi bi-check"></i>';
                event.target.classList.remove('btn-outline-secondary');
                event.target.classList.add('btn-success');
                
                setTimeout(() => {
                    event.target.innerHTML = originalText;
                    event.target.classList.remove('btn-success');
                    event.target.classList.add('btn-outline-secondary');
                }, 2000);
            })
            .catch(err => {
                console.error('Ошибка копирования: ', err);
                alert('Не удалось скопировать ссылку');
            });
    }
    
    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        // Добавляем эффект появления
        const resumeContainer = document.querySelector('.resume-preview-container');
        if (resumeContainer) {
            resumeContainer.style.opacity = '0';
            resumeContainer.style.transition = 'opacity 0.5s ease';
            
            setTimeout(() => {
                resumeContainer.style.opacity = '1';
            }, 100);
        }
        
        // Автоматическое скрытие алертов через 5 секунд
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
    
    // Подготовка к печати
    window.onbeforeprint = function() {
        console.log('Подготовка к печати...');
    };
    
    window.onafterprint = function() {
        console.log('Печать завершена');
    };
</script>

<?php include 'includes/footer.php'; ?>