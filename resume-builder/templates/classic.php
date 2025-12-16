<?php
// Этот файл подключается в edit_resume.php
// Здесь представлен упрощенный шаблон
?>
<div class="resume-template classic">
    <header class="resume-header text-center mb-4">
         <div class="text-center mb-4">
        <h1 class="resume-name"><?php echo isset($personal_info['full_name']) ? htmlspecialchars($personal_info['full_name']) : 'Ваше имя'; ?></h1>
        
        <div class="contact-info">
            <?php if (!empty($personal_info['email'])): ?>
                <span><i class="bi bi-envelope"></i> <?php echo htmlspecialchars($personal_info['email']); ?></span>
            <?php endif; ?>
            
            <?php if (!empty($personal_info['phone'])): ?>
                <span><i class="bi bi-phone"></i> <?php echo htmlspecialchars($personal_info['phone']); ?></span>
            <?php endif; ?>
            
            <?php if (!empty($personal_info['address'])): ?>
                <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($personal_info['address']); ?></span>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($personal_info['website']) || !empty($personal_info['linkedin'])): ?>
        <div class="links mt-2">
            <?php if (!empty($personal_info['website'])): ?>
                <a href="<?php echo htmlspecialchars($personal_info['website']); ?>" target="_blank" class="me-3">
                    <i class="bi bi-globe"></i> Портфолио
                </a>
            <?php endif; ?>
            
            <?php if (!empty($personal_info['linkedin'])): ?>
                <a href="<?php echo htmlspecialchars($personal_info['linkedin']); ?>" target="_blank">
                    <i class="bi bi-linkedin"></i> LinkedIn
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- О себе -->
    <?php if (!empty($personal_info['summary'])): ?>
    <div class="section mb-4">
        <h2 class="section-title">О себе</h2>
        <p><?php echo nl2br(htmlspecialchars($personal_info['summary'])); ?></p>
    </div>
    <?php endif; ?>

<style>
.resume-template.classic {
    font-family: 'Times New Roman', Times, serif;
    line-height: 1.6;
    color: #333;
}

.resume-template .section-title {
    border-bottom: 2px solid #4a6fa5;
    padding-bottom: 5px;
    margin-bottom: 15px;
    color: #4a6fa5;
}

.resume-template .contact-info {
    margin-top: 10px;
    font-size: 1.1rem;
}

.resume-template .experience-item,
.resume-template .education-item {
    margin-bottom: 20px;
}

.resume-template .skills-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.resume-template .skill-item {
    background: #f8f9fa;
    padding: 5px 15px;
    border-radius: 20px;
    border: 1px solid #dee2e6;
}
</style>