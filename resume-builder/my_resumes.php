<?php
// my_resumes.php - Мои резюме
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Обработка удаления резюме
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Проверяем, принадлежит ли резюме текущему пользователю
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    $resume_to_delete = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resume_to_delete) {
        try {
            // Начинаем транзакцию
            $pdo->beginTransaction();
            
            // Удаляем связанные данные
            $tables = ['personal_info', 'experience', 'education', 'skills'];
            foreach ($tables as $table) {
                $stmt = $pdo->prepare("DELETE FROM $table WHERE resume_id = ?");
                $stmt->execute([$delete_id]);
            }
            
            // Удаляем само резюме
            $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ? AND user_id = ?");
            $stmt->execute([$delete_id, $user_id]);
            
            $pdo->commit();
            
            $_SESSION['success'] = 'Резюме "' . htmlspecialchars($resume_to_delete['title']) . '" успешно удалено';
            redirect('my_resumes.php');
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Ошибка при удалении резюме: ' . $e->getMessage();
            redirect('my_resumes.php');
        }
    } else {
        $_SESSION['error'] = 'Резюме не найдено или у вас нет прав для его удаления';
        redirect('my_resumes.php');
    }
}

// Получаем все резюме пользователя
try {
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY updated_at DESC");
    $stmt->execute([$user_id]);
    $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Подсчет по шаблонам
    $template_stats = [];
    foreach ($resumes as $resume) {
        $template = $resume['template'];
        if (!isset($template_stats[$template])) {
            $template_stats[$template] = 0;
        }
        $template_stats[$template]++;
    }
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}

$pageTitle = "Мои резюме";
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Конструктор резюме</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        /* Стили для бокового меню */
        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            min-height: 100vh;
            position: sticky;
            top: 0;
            padding: 0;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background-color: rgba(0,0,0,0.2);
        }
        
        .user-info {
            text-align: center;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .user-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: white;
        }
        
        .user-email {
            font-size: 0.875rem;
            color: #bdc3c7;
        }
        
        .nav-link {
            color: #bdc3c7;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        
        .nav-link.active {
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            font-size: 1.1rem;
        }
        
        .logout-link {
            color: #e74c3c !important;
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
        }
        
        .logout-link:hover {
            background-color: rgba(231, 76, 60, 0.1) !important;
        }
        
        /* Стили для карточек резюме */
        .resume-card {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .resume-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        
        .resume-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 1.25rem;
        }
        
        .badge-template {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
        }
        
        .badge-classic {
            background-color: #6c757d;
            color: white;
        }
        
        .badge-modern {
            background-color: #007bff;
            color: white;
        }
        
        .badge-creative {
            background-color: #28a745;
            color: white;
        }
        
        /* Стили для статистики */
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stats-card p {
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Боковая панель -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="sidebar-header">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <h5 class="user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></h5>
                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                </div>
            </div>
            
            <nav class="nav flex-column pt-3">
                <a class="nav-link" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Панель управления
                </a>
                <a class="nav-link active" href="my_resumes.php">
                    <i class="bi bi-folder"></i> Мои резюме
                </a>
                <a class="nav-link" href="create_resume.php">
                    <i class="bi bi-plus-circle"></i> Создать резюме
                </a>
                <a class="nav-link" href="templates.php">
                    <i class="bi bi-layout-wtf"></i> Шаблоны
                </a>
                <a class="nav-link" href="profile.php">
                    <i class="bi bi-person"></i> Профиль
                </a>
                <a class="nav-link logout-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Выйти
                </a>
            </nav>
        </div>
        
        <!-- Основной контент -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-4 pb-3 mb-4 border-bottom">
                <h1 class="h2 fw-bold text-primary">
                    <i class="bi bi-folder me-2"></i>Мои резюме
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create_resume.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-plus-circle me-2"></i> Новое резюме
                    </a>
                </div>
            </div>
            
            <!-- Сообщения об ошибках/успехе -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <!-- Статистика -->
            <div class="row mb-5 fade-in">
                <div class="col-md-4 mb-3">
                    <div class="stats-card">
                        <h3><?php echo count($resumes); ?></h3>
                        <p>Всего резюме</p>
                        <i class="bi bi-folder display-6 opacity-50"></i>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                        <h3><?php echo isset($template_stats['classic']) ? $template_stats['classic'] : 0; ?></h3>
                        <p>Классических</p>
                        <i class="bi bi-file-earmark-text display-6 opacity-50"></i>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stats-card" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                        <h3><?php echo isset($template_stats['modern']) ? $template_stats['modern'] : 0; ?></h3>
                        <p>Современных</p>
                        <i class="bi bi-stars display-6 opacity-50"></i>
                    </div>
                </div>
            </div>
            
            <!-- Список резюме -->
            <?php if (empty($resumes)): ?>
                <div class="text-center py-5 fade-in">
                    <div class="empty-state">
                        <i class="bi bi-folder-x display-1 text-muted mb-4"></i>
                        <h2 class="mb-3">У вас пока нет резюме</h2>
                        <p class="lead text-muted mb-4">Создайте свое первое профессиональное резюме всего за 5 минут</p>
                        <a href="create_resume.php" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-plus-circle me-2"></i> Создать резюме
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row fade-in">
                    <?php foreach ($resumes as $index => $resume): ?>
                        <div class="col-md-6 col-lg-4 mb-4" style="animation-delay: <?php echo $index * 0.1; ?>s;">
                            <div class="card resume-card h-100">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-truncate" title="<?php echo htmlspecialchars($resume['title']); ?>">
                                            <?php echo htmlspecialchars($resume['title']); ?>
                                        </h6>
                                        <?php 
                                        $badge_class = '';
                                        switch($resume['template']) {
                                            case 'classic': $badge_class = 'badge-classic'; break;
                                            case 'modern': $badge_class = 'badge-modern'; break;
                                            case 'creative': $badge_class = 'badge-creative'; break;
                                        }
                                        ?>
                                        <span class="badge badge-template <?php echo $badge_class; ?>">
                                            <?php 
                                            switch($resume['template']) {
                                                case 'classic': echo 'Классический'; break;
                                                case 'modern': echo 'Современный'; break;
                                                case 'creative': echo 'Креативный'; break;
                                                default: echo htmlspecialchars($resume['template']);
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bi bi-calendar text-primary"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Создано</small>
                                            <span class="fw-semibold"><?php echo date('d.m.Y', strtotime($resume['created_at'])); ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light rounded-circle p-2 me-3">
                                            <i class="bi bi-arrow-clockwise text-success"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Обновлено</small>
                                            <span class="fw-semibold"><?php echo date('d.m.Y H:i', strtotime($resume['updated_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-white border-top-0 pt-0">
                                    <div class="btn-group w-100" role="group">
                                        <a href="edit_resume.php?id=<?php echo $resume['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm flex-fill"
                                           title="Редактировать">
                                            <i class="bi bi-pencil me-1"></i> Редактировать
                                        </a>
                                        <a href="view_resume.php?id=<?php echo $resume['id']; ?>" 
                                           class="btn btn-outline-success btn-sm flex-fill"
                                           title="Просмотр">
                                            <i class="bi bi-eye me-1"></i> Просмотр
                                        </a>
                                        <a href="my_resumes.php?delete=<?php echo $resume['id']; ?>" 
                                           class="btn btn-outline-danger btn-sm flex-fill"
                                           title="Удалить"
                                           onclick="return confirm('Вы действительно хотите удалить резюме &quot;<?php echo addslashes($resume['title']); ?>&quot;?')">
                                            <i class="bi bi-trash me-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<script>
// Автоматическое скрытие алертов через 5 секунд
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
    
    // Подсветка активной ссылки в меню
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>