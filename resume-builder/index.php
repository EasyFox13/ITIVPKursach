<?php
require_once 'config.php';
$pageTitle = "Генератор резюме - Создайте профессиональное резюме за 5 минут";
?>
<?php include 'includes/header.php'; ?>

<div class="hero">
    <div class="container">
        <h1>Создайте профессиональное резюме за 5 минут</h1>
        <p class="lead">Используйте наш конструктор резюме, чтобы создать впечатляющее резюме, которое выделит вас среди других кандидатов.</p>
        
        <?php if (!isLoggedIn()): ?>
            <div class="cta-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">Начать</a>
                <a href="#features" class="btn btn-outline-primary btn-lg">Узнать больше</a>
            </div>
        <?php else: ?>
            <div class="cta-buttons">
                <a href="create_resume.php" class="btn btn-primary btn-lg">Создать новое резюме</a>
                <a href="my_resumes.php" class="btn btn-primary btn-lg">Мои резюме</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<section id="features" class="features">
    <div class="container">
        <h2 class="text-center mb-5">Почему выбирают наш сервис</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-card">
                    
                    <h3>Профессиональные шаблоны</h3>
                    <p>Выбирайте из множества современных шаблонов, одобренных HR-специалистами.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    
                    <h3>Быстрое создание</h3>
                    <p>Создайте полное резюме всего за 5 минут с помощью интуитивного конструктора.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    
                    <h3>Адаптивный дизайн</h3>
                    <p>Ваше резюме будет идеально выглядеть на любом устройстве и в любом формате.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="templates-preview">
    <div class="container">
        <h2 class="text-center mb-5">Популярные шаблоны</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="template-card">
                    <div class="template-preview classic"></div>
                    <h4>Классический</h4>
                    <p>Профессиональный и строгий дизайн</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="template-card">
                    <div class="template-preview modern"></div>
                    <h4>Современный</h4>
                    <p>Стильный и минималистичный дизайн</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="template-card">
                    <div class="template-preview creative"></div>
                    <h4>Креативный</h4>
                    <p>Яркий и запоминающийся дизайн</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>