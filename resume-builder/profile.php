<?php
// profile.php - Профиль пользователя
require_once 'config.php';

// Проверяем авторизацию
if (!isLoggedIn()) {
    $_SESSION['error'] = 'Для доступа к профилю необходимо авторизоваться';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$pageTitle = 'Мой профиль';

// Получаем данные пользователя
try {
    $stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['error'] = 'Пользователь не найден';
        redirect('index.php');
    }
    
    // Получаем количество резюме пользователя
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM resumes WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $resume_count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Получаем последние 3 резюме
    $stmt = $pdo->prepare("SELECT id, title, template, updated_at FROM resumes 
                          WHERE user_id = ? ORDER BY updated_at DESC LIMIT 3");
    $stmt->execute([$user_id]);
    $recent_resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = 'Ошибка базы данных: ' . $e->getMessage();
    redirect('index.php');
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <!-- Левая колонка: информация профиля -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Аватар пользователя (можно заменить на реальный) -->
                    <div class="mb-3">
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center" 
                             style="width: 120px; height: 120px; font-size: 48px; color: white;">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    </div>
                    
                    <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                    <p class="text-muted mb-3">
                        <i class="bi bi-envelope me-1"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                    
                    <div class="d-grid gap-2">
                        <a href="edit_profile.php" class="btn btn-primary">
                            <i class="bi bi-pencil-square me-1"></i> Редактировать профиль
                        </a>
                        <a href="change_password.php" class="btn btn-outline-secondary">
                            <i class="bi bi-key me-1"></i> Сменить пароль
                        </a>
                    </div>
                    
                    <hr class="my-3">
                    
                    <div class="row text-muted small">
                        <div class="col-6">
                            <div class="fw-bold"><?php echo $resume_count; ?></div>
                            <div>Резюме</div>
                        </div>
                        <div class="col-6">
                            <div class="fw-bold">
                                <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                            </div>
                            <div>На сайте с</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Правая колонка: активность и резюме -->
        <div class="col-md-8">
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
            
            <!-- Блок с кнопками быстрого доступа -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-lightning me-2"></i>Быстрые действия</h5>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <a href="create_resume.php" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle me-1"></i> Новое резюме
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="my_resumes.php" class="btn btn-outline-primary w-100">
                                <i class="bi bi-folder me-1"></i> Все резюме
                            </a>
                        </div>
                        <div class="col-md-4">
                            <a href="templates.php" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-layers me-1"></i> Шаблоны
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Последние резюме -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text me-2"></i>Последние резюме</h5>
                    <a href="my_resumes.php" class="btn btn-sm btn-outline-primary">
                        Показать все
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($recent_resumes): ?>
                        <div class="list-group">
                            <?php foreach ($recent_resumes as $resume): ?>
                                <a href="view_resume.php?id=<?php echo $resume['id']; ?>" 
                                   class="list-group-item list-group-item-action">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($resume['title']); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('d.m.Y', strtotime($resume['updated_at'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-1 small text-muted">
                                        <i class="bi bi-layers me-1"></i>
                                        <?php 
                                        switch($resume['template']) {
                                            case 'classic': echo 'Классический'; break;
                                            case 'modern': echo 'Современный'; break;
                                            case 'creative': echo 'Креативный'; break;
                                            default: echo htmlspecialchars($resume['template']);
                                        }
                                        ?>
                                    </p>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-x fs-1 text-muted mb-3 d-block"></i>
                            <p class="text-muted">У вас еще нет резюме</p>
                            <a href="create_resume.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i> Создать первое резюме
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Статистика (простая) -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-bar-chart me-2"></i>Активность
                            </h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fs-4 fw-bold"><?php echo $resume_count; ?></div>
                                    <div class="text-muted small">Всего резюме</div>
                                </div>
                                <div class="text-primary">
                                    <i class="bi bi-files fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-calendar-check me-2"></i>Последняя активность
                            </h6>
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fs-4 fw-bold">
                                        <?php if ($recent_resumes): ?>
                                            <?php echo date('d.m.Y', strtotime($recent_resumes[0]['updated_at'])); ?>
                                        <?php else: ?>
                                            Нет
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted small">Обновление резюме</div>
                                </div>
                                <div class="text-success">
                                    <i class="bi bi-clock-history fs-1"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили для аватара */
    .rounded-circle {
        border-radius: 50% !important;
        background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
    }
    
    /* Адаптивность */
    @media (max-width: 768px) {
        .rounded-circle {
            width: 80px !important;
            height: 80px !important;
            font-size: 32px !important;
        }
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
        
        // Простая анимация карточек
        const cards = document.querySelectorAll('.card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>