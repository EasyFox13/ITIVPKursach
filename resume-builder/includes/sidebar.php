
<?php
// includes/sidebar.php - Боковая панель навигации
if (!isset($user_id)) {
    $user_id = $_SESSION['user_id'] ?? 0;
}

if (!isset($username)) {
    $username = $_SESSION['username'] ?? 'Пользователь';
}
?>
<div class="col-md-3 col-lg-2 sidebar d-none d-md-block">
    <div class="sidebar-sticky">
        <div class="sidebar-header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-details">
                    <h6><?php echo htmlspecialchars($username); ?></h6>
                    <small><?php echo $_SESSION['email'] ?? ''; ?></small>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Панель управления
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_resumes.php' ? 'active' : ''; ?>" 
                   href="my_resumes.php">
                    <i class="bi bi-files me-2"></i> Мои резюме
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'create_resume.php' ? 'active' : ''; ?>" 
                   href="create_resume.php">
                    <i class="bi bi-plus-circle me-2"></i> Создать резюме
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'templates.php' ? 'active' : ''; ?>" 
                   href="templates.php">
                    <i class="bi bi-layers me-2"></i> Шаблоны
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>" 
                   href="profile.php">
                    <i class="bi bi-person me-2"></i> Профиль
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Выйти
                </a>
            </li>
        </ul>
    </div>
</div>