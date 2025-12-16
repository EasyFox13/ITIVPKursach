<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Обработка формы создания резюме
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_resume'])) {
    $title = trim($_POST['title']);
    $template = $_POST['template'];
    
    if (empty($title)) {
        $error = 'Введите название резюме';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Создание резюме
            $stmt = $pdo->prepare("INSERT INTO resumes (user_id, title, template) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $title, $template]);
            $resume_id = $pdo->lastInsertId();
            
            // Добавление персональной информации
            $stmt = $pdo->prepare("INSERT INTO personal_info (resume_id, full_name) VALUES (?, ?)");
            $stmt->execute([$resume_id, 'Ваше имя']);
            
            $pdo->commit();
            
            $_SESSION['current_resume_id'] = $resume_id;
            redirect('edit_resume.php?id=' . $resume_id);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка при создании резюме: ' . $e->getMessage();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3>Создать новое резюме</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-4">
                            <label for="title" class="form-label">Название резюме</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   placeholder="Например: Резюме для позиции Frontend Developer" required>
                            <div class="form-text">Это название будет использоваться только в вашем личном кабинете</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Выберите шаблон</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="template-option">
                                        <input type="radio" name="template" value="classic" id="classic" checked>
                                        <label for="classic">
                                            <div class="template-preview-sm classic"></div>
                                            <h5>Классический</h5>
                                            <p>Классика</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="template-option">
                                        <input type="radio" name="template" value="modern" id="modern">
                                        <label for="modern">
                                            <div class="template-preview-sm modern"></div>
                                            <h5>Современный</h5>
                                            <p>Стильный минимализм</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="template-option">
                                        <input type="radio" name="template" value="creative" id="creative">
                                        <label for="creative">
                                            <div class="template-preview-sm creative"></div>
                                            <h5>Креативный</h5>
                                            <p>Для творческих профессий</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" name="create_resume" class="btn btn-primary btn-lg">
                                Создать резюме и начать редактирование
                            </button>
                            <a href="my_resumes.php" class="btn btn-outline-secondary">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-body">
                    <h5>Советы по созданию резюме:</h5>
                    <ul>
                        <li>Выберите шаблон, соответствующий вашей индустрии</li>
                        <li>Подготовьте информацию о вашем опыте работы и образовании</li>
                        <li>Используйте конкретные примеры достижений</li>
                        <li>Укажите ключевые навыки, соответствующие желаемой позиции</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>