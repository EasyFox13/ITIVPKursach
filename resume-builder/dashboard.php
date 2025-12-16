<?php
// dashboard.php - Панель управления
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Получаем статистику пользователя
try {
    // Общее количество резюме
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM resumes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $total_resumes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Резюме по шаблонам
    $stmt = $pdo->prepare("SELECT template, COUNT(*) as count FROM resumes WHERE user_id = ? GROUP BY template");
    $stmt->execute([$user_id]);
    $template_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Последние резюме
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY updated_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Резюме за последнюю неделю
    $stmt = $pdo->prepare("SELECT COUNT(*) as weekly FROM resumes WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute([$user_id]);
    $weekly_resumes = $stmt->fetch(PDO::FETCH_ASSOC)['weekly'];
    
    // Самое старое резюме
    $stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at ASC LIMIT 1");
    $stmt->execute([$user_id]);
    $oldest_resume = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Ошибка базы данных: " . $e->getMessage();
}

$pageTitle = "Панель управления";
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
        
        /* Стили для дашборда */
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .welcome-card::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -50%;
            width: 150px;
            height: 150px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        .stats-card {
            border-radius: 12px;
            padding: 1.5rem;
            color: white;
            transition: transform 0.3s ease;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .stats-card i {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 2.5rem;
            opacity: 0.3;
        }
        
        .stats-card h3 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stats-card p {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .recent-resume-card {
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            overflow: hidden;
        }
        
        .recent-resume-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: #007bff;
        }
        
        .badge-template {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
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
        
        .quick-actions .btn {
            border-radius: 10px;
            padding: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .quick-actions .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .activity-item {
            padding: 1rem;
            border-left: 3px solid transparent;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.3s ease;
        }
        
        .activity-item:hover {
            background-color: #f8f9fa;
            border-left-color: #007bff;
        }
        
        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
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
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Панель управления
                </a>
                <a class="nav-link" href="my_resumes.php">
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
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Приветствие -->
            <div class="welcome-card fade-in">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="display-6 fw-bold mb-3">Добро пожаловать, <?php echo htmlspecialchars($_SESSION['username']); ?>! 👋</h1>
                        <p class="lead mb-4 opacity-90">Управляйте своими резюме, создавайте новые и отслеживайте активность</p>
                        <a href="create_resume.php" class="btn btn-light btn-lg pulse">
                            <i class="bi bi-plus-circle me-2"></i> Создать новое резюме
                        </a>
                    </div>
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bi bi-file-earmark-text display-1 opacity-25"></i>
                    </div>
                </div>
            </div>
            
            <!-- Статистика -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3 fade-in" style="animation-delay: 0.1s;">
                    <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="bi bi-folder"></i>
                        <h3><?php echo $total_resumes; ?></h3>
                        <p>Всего резюме</p>
                        <small>в вашем профиле</small>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3 fade-in" style="animation-delay: 0.2s;">
                    <div class="stats-card" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                        <i class="bi bi-file-earmark-text"></i>
                        <?php 
                        $classic_count = 0;
                        foreach ($template_stats as $stat) {
                            if ($stat['template'] == 'classic') {
                                $classic_count = $stat['count'];
                            }
                        }
                        ?>
                        <h3><?php echo $classic_count; ?></h3>
                        <p>Классических</p>
                        <small>шаблон</small>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3 fade-in" style="animation-delay: 0.3s;">
                    <div class="stats-card" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                        <i class="bi bi-stars"></i>
                        <?php 
                        $modern_count = 0;
                        foreach ($template_stats as $stat) {
                            if ($stat['template'] == 'modern') {
                                $modern_count = $stat['count'];
                            }
                        }
                        ?>
                        <h3><?php echo $modern_count; ?></h3>
                        <p>Современных</p>
                        <small>шаблон</small>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3 fade-in" style="animation-delay: 0.4s;">
                    <div class="stats-card" style="background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);">
                        <i class="bi bi-lightning"></i>
                        <h3><?php echo $weekly_resumes; ?></h3>
                        <p>За неделю</p>
                        <small>новых резюме</small>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Быстрые действия -->
                <div class="col-lg-4 mb-4 fade-in" style="animation-delay: 0.5s;">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="fw-bold mb-3"><i class="bi bi-lightning text-warning me-2"></i> Быстрые действия</h5>
                        </div>
                        <div class="card-body pt-0">
                            <div class="quick-actions d-grid gap-2">
                                <a href="create_resume.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i> Создать резюме
                                </a>
                                <a href="templates.php" class="btn btn-outline-primary">
                                    <i class="bi bi-layout-wtf me-2"></i> Посмотреть шаблоны
                                </a>
                                <a href="my_resumes.php" class="btn btn-outline-success">
                                    <i class="bi bi-folder me-2"></i> Все мои резюме
                                </a>
                                <?php if ($oldest_resume): ?>
                                <a href="edit_resume.php?id=<?php echo $oldest_resume['id']; ?>" class="btn btn-outline-info">
                                    <i class="bi bi-pencil me-2"></i> Обновить старое резюме
                                </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mt-4 pt-3 border-top">
                                <h6 class="fw-bold mb-3"><i class="bi bi-graph-up text-success me-2"></i> Советы</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Регулярно обновляйте резюме</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Используйте разные шаблоны</li>
                                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i> Заполняйте все разделы</li>
                                    <li><i class="bi bi-check-circle text-success me-2"></i> Сохраняйте несколько версий</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Последние резюме -->
                <div class="col-lg-8 mb-4 fade-in" style="animation-delay: 0.6s;">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="fw-bold mb-3"><i class="bi bi-clock-history text-primary me-2"></i> Недавние резюме</h5>
                                <a href="my_resumes.php" class="btn btn-sm btn-outline-primary">Все резюме</a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <?php if (empty($recent_resumes)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-folder-x display-1 text-muted mb-3"></i>
                                    <h5 class="text-muted">У вас пока нет резюме</h5>
                                    <p class="text-muted mb-4">Создайте свое первое резюме</p>
                                    <a href="create_resume.php" class="btn btn-primary">
                                        <i class="bi bi-plus-circle me-2"></i> Создать резюме
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Название</th>
                                                <th>Шаблон</th>
                                                <th>Обновлено</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_resumes as $resume): ?>
                                                <tr class="activity-item">
                                                    <td>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($resume['title']); ?></div>
                                                    </td>
                                                    <td>
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
                                                    </td>
                                                    <td>
                                                        <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($resume['updated_at'])); ?></small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <a href="edit_resume.php?id=<?php echo $resume['id']; ?>" 
                                                               class="btn btn-outline-primary btn-sm"
                                                               title="Редактировать">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="view_resume.php?id=<?php echo $resume['id']; ?>" 
                                                               class="btn btn-outline-success btn-sm"
                                                               title="Просмотр">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Прогресс -->
            <div class="row fade-in" style="animation-delay: 0.7s;">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0">
                            <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart text-info me-2"></i> Ваш прогресс</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 text-center mb-3">
                                    <div class="display-6 fw-bold text-primary"><?php echo $total_resumes; ?></div>
                                    <p class="text-muted">созданных резюме</p>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="display-6 fw-bold text-success">
                                        <?php 
                                        $unique_templates = count($template_stats);
                                        echo $unique_templates;
                                        ?>
                                    </div>
                                    <p class="text-muted">разных шаблонов</p>
                                </div>
                                <div class="col-md-4 text-center mb-3">
                                    <div class="display-6 fw-bold text-warning">
                                        <?php 
                                        $active_days = $total_resumes > 0 ? '7+' : '0';
                                        echo $active_days;
                                        ?>
                                    </div>
                                    <p class="text-muted">активных дней</p>
                                </div>
                            </div>
                            
                            <div class="progress mb-3" style="height: 10px;">
                                <?php 
                                $progress = min(100, $total_resumes * 20); // 5 резюме = 100%
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $progress; ?>%" 
                                     aria-valuenow="<?php echo $progress; ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">
                                    <?php if ($total_resumes < 5): ?>
                                        Создайте еще <?php echo 5 - $total_resumes; ?> резюме для полного профиля
                                    <?php else: ?>
                                        Отлично! У вас полный профиль с разнообразными резюме
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
    
    // Анимация прогресс-бара
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const width = progressBar.style.width;
        progressBar.style.width = '0';
        setTimeout(() => {
            progressBar.style.transition = 'width 1.5s ease-in-out';
            progressBar.style.width = width;
        }, 500);
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>