<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$resume_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Проверка прав доступа
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->execute([$resume_id, $user_id]);
$resume = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resume) {
    $_SESSION['error'] = 'Резюме не найдено или у вас нет к нему доступа';
    redirect('my_resumes.php');
}

// Обработка сохранения ВСЕЙ информации (резюме + персональная)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_all_info'])) {
    // 1. Данные о резюме
    $title = trim($_POST['title']);
    $template = $_POST['template'];
    
    // 2. Персональная информация
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $summary = trim($_POST['summary'] ?? '');
    $website = trim($_POST['website'] ?? '');
    $linkedin = trim($_POST['linkedin'] ?? '');
    
    // Валидация
    $errors = [];
    if (empty($title)) {
        $errors[] = 'Название резюме не может быть пустым';
    }
    if (empty($full_name)) {
        $errors[] = 'Поле ФИО обязательно для заполнения';
    }
    if (empty($email)) {
        $errors[] = 'Поле Email обязательно для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email адрес';
    }
    
    if (!empty($errors)) {
        $_SESSION['error'] = implode('<br>', $errors);
    } else {
        try {
            // Начало транзакции для сохранения целостности данных
            $pdo->beginTransaction();
            
            // 1. Обновляем информацию о резюме
            $stmt = $pdo->prepare("UPDATE resumes SET title = ?, template = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$title, $template, $resume_id]);
            
            // Обновляем локальные данные для предпросмотра
            $resume['title'] = $title;
            $resume['template'] = $template;
            
            // 2. Обновляем или создаем персональную информацию
            $stmt = $pdo->prepare("SELECT id FROM personal_info WHERE resume_id = ?");
            $stmt->execute([$resume_id]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Обновляем существующую запись
                $sql = "UPDATE personal_info SET 
                        full_name = ?,
                        email = ?,
                        phone = ?,
                        address = ?,
                        summary = ?,
                        website = ?,
                        linkedin = ?
                        WHERE resume_id = ?";
                        
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $full_name,
                    $email,
                    $phone,
                    $address,
                    $summary,
                    $website,
                    $linkedin,
                    $resume_id
                ]);
            } else {
                // Создаем новую запись
                $sql = "INSERT INTO personal_info 
                        (resume_id, full_name, email, phone, address, summary, website, linkedin)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $resume_id,
                    $full_name,
                    $email,
                    $phone,
                    $address,
                    $summary,
                    $website,
                    $linkedin
                ]);
            }
            
            // Фиксируем транзакцию
            $pdo->commit();
            
            $_SESSION['success'] = 'Вся информация успешно сохранена';
            
            // Перезагружаем страницу для обновления данных
            redirect("edit_resume.php?id=$resume_id");
            
        } catch (PDOException $e) {
            // Откатываем транзакцию в случае ошибки
            $pdo->rollBack();
            $_SESSION['error'] = 'Ошибка базы данных: ' . $e->getMessage();
        }
    }
}

// Получение данных резюме (после возможных обновлений)
$stmt = $pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
$stmt->execute([$resume_id]);
$personal_info = $stmt->fetch(PDO::FETCH_ASSOC);

$experience = $pdo->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY start_date DESC");
$experience->execute([$resume_id]);
$experience = $experience->fetchAll(PDO::FETCH_ASSOC);

$education = $pdo->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY graduation_date DESC");
$education->execute([$resume_id]);
$education = $education->fetchAll(PDO::FETCH_ASSOC);

$skills = $pdo->prepare("SELECT * FROM skills WHERE resume_id = ?");
$skills->execute([$resume_id]);
$skills = $skills->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = "Редактирование: " . $resume['title'];
?>
<?php include 'includes/header.php'; ?>

<div class="container-fluid mt-3">
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
    
    <div class="row">
        <!-- Панель редактирования -->
        <div class="col-md-4">
            <!-- ЕДИНАЯ ФОРМА для сохранения ВСЕЙ информации -->
            <form method="POST" action="" id="resumeForm">
                <input type="hidden" name="save_all_info" value="1">
                
                <!-- Информация о резюме -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Информация о резюме</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Название</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($resume['title']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="template" class="form-label">Шаблон</label>
                            <select class="form-select" id="template" name="template">
                                <option value="classic" <?php echo $resume['template'] == 'classic' ? 'selected' : ''; ?>>Классический</option>
                                <option value="modern" <?php echo $resume['template'] == 'modern' ? 'selected' : ''; ?>>Современный</option>
                                <option value="creative" <?php echo $resume['template'] == 'creative' ? 'selected' : ''; ?>>Креативный</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Персональная информация -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Персональная информация</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">ФИО *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo isset($personal_info['full_name']) ? htmlspecialchars($personal_info['full_name']) : ''; ?>"
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($personal_info['email']) ? htmlspecialchars($personal_info['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Телефон</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($personal_info['phone']) ? htmlspecialchars($personal_info['phone']) : ''; ?>"
                                       placeholder="+7 (999) 123-45-67">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Адрес</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo isset($personal_info['address']) ? htmlspecialchars($personal_info['address']) : ''; ?>"
                                       placeholder="г. Москва, ул. Примерная, д. 1">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="summary" class="form-label">О себе / Цель</label>
                            <textarea class="form-control" id="summary" name="summary" rows="4"><?php echo isset($personal_info['summary']) ? htmlspecialchars($personal_info['summary']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="website" class="form-label">Веб-сайт / Портфолио</label>
                                <input type="url" class="form-control" id="website" name="website" 
                                       value="<?php echo isset($personal_info['website']) ? htmlspecialchars($personal_info['website']) : ''; ?>"
                                       placeholder="https://ваш-сайт.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="linkedin" class="form-label">LinkedIn профиль</label>
                                <input type="url" class="form-control" id="linkedin" name="linkedin" 
                                       value="<?php echo isset($personal_info['linkedin']) ? htmlspecialchars($personal_info['linkedin']) : ''; ?>"
                                       placeholder="https://linkedin.com/in/ваше-имя">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Сохранить информацию
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="fillSampleData()">
                                <i class="bi bi-magic me-2"></i> Заполнить тестовыми данными
                            </button>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Дополнительные разделы -->
            <div class="card mb-3">
                <div class="card-header">
                    <h5>Дополнительные разделы</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" onclick="alert('Функция добавления опыта работы будет реализована позже')">
                            <i class="bi bi-briefcase me-2"></i> Добавить опыт работы
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="alert('Функция добавления образования будет реализована позже')">
                            <i class="bi bi-mortarboard me-2"></i> Добавить образование
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="alert('Функция добавления навыков будет реализована позже')">
                            <i class="bi bi-tools me-2"></i> Добавить навыки
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Навигация -->
            <div class="card">
                <div class="card-header">
                    <h5>Быстрая навигация</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="view_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-outline-primary">
                            <i class="bi bi-eye me-2"></i> Просмотр резюме
                        </a>
                        <a href="my_resumes.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i> Вернуться к списку
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary">
                            <i class="bi bi-speedometer2 me-2"></i> В панель управления
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Предпросмотр резюме -->
        <div class="col-md-8">
            <div class="sticky-top" style="top: 20px;">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Предпросмотр резюме</h5>
                        <div>
                            <a href="view_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-success btn-sm">
                                <i class="bi bi-eye me-1"></i> Полный просмотр
                            </a>
                            <a href="view_resume.php?id=<?php echo $resume_id; ?>&print=1" class="btn btn-outline-secondary btn-sm" target="_blank">
                                <i class="bi bi-printer me-1"></i> Печать
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="resume-preview">
                            <?php include 'templates/' . $resume['template'] . '.php'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .resume-preview {
        background-color: white;
        padding: 30px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        min-height: 800px;
        border-radius: 5px;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>

<script>
// Простая функция для заполнения тестовыми данными
function fillSampleData() {
    if (confirm('Заполнить форму тестовыми данными? Текущие данные будут заменены.')) {
        const sampleData = {
            title: 'Моё профессиональное резюме',
            full_name: 'Иванов Иван Иванович',
            email: 'ivanov@example.com',
            phone: '+7 (999) 123-45-67',
            address: 'г. Москва, ул. Ленина, д. 1, кв. 25',
            summary: 'Опытный full-stack разработчик с 5+ годами опыта. Специализируюсь на создании современных веб-приложений с использованием PHP, JavaScript и современных фреймворков. Имею опыт работы в agile-командах и руководства небольшими проектами.',
            website: 'https://portfolio.ivanov.com',
            linkedin: 'https://linkedin.com/in/ivanivanov'
        };
        
        // Заполняем поля формы
        document.getElementById('title').value = sampleData.title;
        document.getElementById('full_name').value = sampleData.full_name;
        document.getElementById('email').value = sampleData.email;
        document.getElementById('phone').value = sampleData.phone;
        document.getElementById('address').value = sampleData.address;
        document.getElementById('summary').value = sampleData.summary;
        document.getElementById('website').value = sampleData.website;
        document.getElementById('linkedin').value = sampleData.linkedin;
        
        alert('Форма заполнена тестовыми данными. Нажмите "Сохранить информацию" для сохранения.');
    }
}

// Автоматическое обновление предпросмотра при изменении шаблона
document.getElementById('template').addEventListener('change', function() {
    // Обновляем предпросмотр через AJAX или перезагрузку страницы
    alert('Шаблон изменен. Нажмите "Сохранить информацию" для применения изменений.');
});

// При загрузке страницы - фокус на первом поле
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли сохраненные данные
    const hasPersonalData = document.getElementById('full_name').value.trim() !== '';
    
    if (!hasPersonalData) {
        // Если данных нет, фокус на поле ФИО
        document.getElementById('full_name').focus();
    }
    
    // Обработчик отправки формы (опционально - можно добавить подтверждение)
    document.getElementById('resumeForm').addEventListener('submit', function(e) {
        // Можно добавить дополнительную валидацию здесь
        console.log('Форма отправляется...');
    });
});
</script>

<?php include 'includes/footer.php'; ?>