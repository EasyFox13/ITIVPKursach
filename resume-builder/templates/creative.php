<?php
// creative.php - Креативный шаблон резюме (упрощенный)
$resume_id = $resume['id'];
$template = 'creative';

// Получаем данные резюме
$stmt = $pdo->prepare("SELECT * FROM personal_info WHERE resume_id = ?");
$stmt->execute([$resume_id]);
$personal = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем опыт работы
$stmt = $pdo->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY start_date DESC");
$stmt->execute([$resume_id]);
$experience = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем образование
$stmt = $pdo->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY graduation_date DESC");
$stmt->execute([$resume_id]);
$education = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получаем навыки
$stmt = $pdo->prepare("SELECT * FROM skills WHERE resume_id = ?");
$stmt->execute([$resume_id]);
$skills = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($resume['title']); ?> - Резюме</title>
    <style>
        /* Упрощенный креативный стиль */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .resume-container {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            position: relative;
        }
        
        /* Шапка с градиентом */
        .header {
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            padding: 40px 50px;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        
        /* Имя и должность */
        .name {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
            color: white;
        }
        
        .position {
            font-size: 20px;
            color: rgba(255,255,255,0.9);
            font-weight: 400;
            margin-bottom: 20px;
        }
        
        /* Контактная информация в шапке */
        .contact-header {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,0.2);
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            font-size: 15px;
        }
        
        .contact-item i {
            margin-right: 8px;
            color: #C8E6C9;
            width: 20px;
            text-align: center;
        }
        
        /* Основное содержание */
        .content {
            padding: 40px 50px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
        }
        
        /* Секции */
        .section {
            margin-bottom: 30px;
        }
        
        .section-title {
            font-size: 22px;
            font-weight: 600;
            color: #2E7D32;
            margin-bottom: 20px;
            padding-bottom: 10px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: #4CAF50;
            border-radius: 2px;
        }
        
        /* Опыт работы */
        .experience-item {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .experience-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        
        .exp-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        
        .exp-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .exp-date {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .exp-company {
            color: #666;
            font-weight: 500;
            margin-bottom: 8px;
            font-size: 15px;
        }
        
        /* Образование */
        .education-item {
            margin-bottom: 20px;
            padding-left: 20px;
            position: relative;
        }
        
        .education-item::before {
            content: '🎓';
            position: absolute;
            left: 0;
            top: 0;
        }
        
        .edu-degree {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        
        .edu-institution {
            color: #2E7D32;
            font-weight: 500;
            margin-bottom: 4px;
        }
        
        .edu-date {
            color: #666;
            font-size: 14px;
        }
        
        /* Навыки */
        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .skill-tag {
            background: #E8F5E9;
            color: #2E7D32;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            border: 1px solid #C8E6C9;
        }
        
        /* Правая колонка */
        .sidebar {
            background: #F5F5F5;
            padding: 25px;
            border-radius: 10px;
            border: 1px solid #E0E0E0;
        }
        
        .sidebar-section {
            margin-bottom: 25px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: 600;
            color: #2E7D32;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        
        .sidebar-title i {
            margin-right: 10px;
            color: #4CAF50;
        }
        
        /* Список в сайдбаре */
        .sidebar-list {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-list li {
            margin-bottom: 10px;
            padding-left: 5px;
            color: #333; /* Исправленный цвет текста */
        }
        
        /* Языки */
        .language-item {
            margin-bottom: 15px;
        }
        
        .language-name {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333; /* Исправленный цвет текста */
        }
        
        .language-level {
            display: flex;
            gap: 4px;
        }
        
        .language-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #E0E0E0;
        }
        
        .language-dot.filled {
            background: #4CAF50;
        }
        
        /* Достижения - исправленные цвета */
        .achievement-item {
            background: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #4CAF50;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            color: #333; /* Темный цвет текста */
        }
        
        .achievement-item i {
            margin-right: 8px;
        }
        
        .text-success {
            color: #2E7D32 !important;
        }
        
        .text-info {
            color: #2196F3 !important;
        }
        
        .text-warning {
            color: #FF9800 !important;
        }
        
        .text-muted {
            color: #666 !important;
        }
        
        /* Футер */
        .footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 13px;
            border-top: 1px solid #eee;
            background: #F9F9F9;
        }
        
        /* Печать */
        @media print {
            body {
                background: white;
            }
            
            .resume-container {
                box-shadow: none;
                margin: 0;
                max-width: 100%;
            }
            
            .no-print {
                display: none;
            }
        }
        
        /* Адаптивность */
        @media (max-width: 1200px) {
            .content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .sidebar {
                margin-top: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .header, .content {
                padding: 30px;
            }
            
            .name {
                font-size: 28px;
            }
            
            .position {
                font-size: 18px;
            }
            
            .contact-header {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="resume-container">
        <!-- Шапка -->
        <div class="header">
            <?php if ($personal && !empty($personal['full_name'])): ?>
                <h1 class="name"><?php echo htmlspecialchars($personal['full_name']); ?></h1>
                
            <?php endif; ?>
            
            <div class="contact-header">
                <?php if ($personal && !empty($personal['email'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($personal['email']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($personal && !empty($personal['phone'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($personal['phone']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($personal && !empty($personal['address'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($personal['address']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($personal && !empty($personal['website'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-globe"></i>
                        <span><?php echo htmlspecialchars($personal['website']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Основное содержание -->
        <div class="content">
            <!-- Левая колонка (основная) -->
            <div class="main-column">

            <?php if ($personal['summary']): ?>
    <?php 
    // Только первая строка summary идет как должность в шапке
    $summary_lines = explode("\n", $personal['summary']);
    $position = trim($summary_lines[0]);
    ?>
    <div class="position" style="color: #2E7D32; font-weight: 500;"><?php echo htmlspecialchars($position); ?></div>
<?php endif; ?>
                <!-- Опыт работы -->
                <?php if (!empty($experience)): ?>
                    <div class="section">
                        <h2 class="section-title">Опыт работы</h2>
                        <?php foreach ($experience as $exp): ?>
                            <div class="experience-item">
                                <div class="exp-header">
                                    <h3 class="exp-title"><?php echo htmlspecialchars($exp['position']); ?></h3>
                                    <div class="exp-date">
                                        <?php 
                                        $start_date = date('m.Y', strtotime($exp['start_date']));
                                        $end_date = $exp['end_date'] ? date('m.Y', strtotime($exp['end_date'])) : 'Наст. время';
                                        echo $start_date . ' - ' . $end_date;
                                        ?>
                                    </div>
                                </div>
                                <div class="exp-company">
                                    <?php echo htmlspecialchars($exp['company']); ?> • <?php echo htmlspecialchars($exp['location']); ?>
                                </div>
                                <?php if (!empty($exp['description'])): ?>
                                    <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Образование -->
                <?php if (!empty($education)): ?>
                    <div class="section">
                        <h2 class="section-title">Образование</h2>
                        <?php foreach ($education as $edu): ?>
                            <div class="education-item">
                                <h3 class="edu-degree"><?php echo htmlspecialchars($edu['degree']); ?></h3>
                                <div class="edu-institution"><?php echo htmlspecialchars($edu['institution']); ?></div>
                                <?php if (!empty($edu['location'])): ?>
                                    <div style="color: #666; font-size: 14px; margin-bottom: 4px;">
                                        <?php echo htmlspecialchars($edu['location']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="edu-date"><?php echo date('Y', strtotime($edu['graduation_date'])); ?></div>
                                <?php if (!empty($edu['description'])): ?>
                                    <p style="margin-top: 8px; font-size: 14px;"><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Правая колонка (сайдбар) -->
            <div class="sidebar">
                <!-- О себе -->
                <?php if ($personal && !empty($personal['summary'])): ?>
                    <div class="sidebar-section">
                        <h3 class="sidebar-title"><i class="fas fa-user"></i>О себе</h3>
                        <div style="color: #333; line-height: 1.6;">
                            <?php 
                            // Убираем первую строку (она уже в шапке как должность)
                            $summary_lines = explode("\n", $personal['summary']);
                            array_shift($summary_lines);
                            $summary = implode("\n", array_filter($summary_lines));
                            ?>
                            <p><?php echo nl2br(htmlspecialchars($summary)); ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Навыки -->
                <?php if (!empty($skills)): ?>
                    <div class="sidebar-section">
                        <h3 class="sidebar-title"><i class="fas fa-cogs"></i>Навыки</h3>
                        <div class="skills-list">
                            <?php foreach ($skills as $skill): ?>
                                <div class="skill-tag"><?php echo htmlspecialchars($skill['skill_name']); ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Языки -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-language"></i>Языки</h3>
                    <div class="language-item">
                        <span class="language-name">Русский (Родной)</span>
                        <div class="language-level">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="language-dot filled"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="language-item">
                        <span class="language-name">Английский (B2)</span>
                        <div class="language-level">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div class="language-dot filled"></div>
                            <?php endfor; ?>
                            <div class="language-dot"></div>
                        </div>
                    </div>
                    <div class="language-item">
                        <span class="language-name">Немецкий (A2)</span>
                        <div class="language-level">
                            <?php for ($i = 1; $i <= 2; $i++): ?>
                                <div class="language-dot filled"></div>
                            <?php endfor; ?>
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <div class="language-dot"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Достижения -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-trophy"></i>Достижения</h3>
                    <div class="achievement-item">
                        <i class="fas fa-award text-success"></i>
                        Лучший проект года 2023
                    </div>
                    <div class="achievement-item">
                        <i class="fas fa-certificate text-info"></i>
                        Сертификат профессионального уровня
                    </div>
                    <div class="achievement-item">
                        <i class="fas fa-users text-warning"></i>
                        Руководство командой из 5 человек
                    </div>
                </div>
                
                <!-- Контактная информация (дополнительная) -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title"><i class="fas fa-address-card"></i>Контакты</h3>
                    <ul class="sidebar-list">
                        <?php if ($personal && !empty($personal['email'])): ?>
                            <li><i class="fas fa-envelope text-muted me-2"></i><?php echo htmlspecialchars($personal['email']); ?></li>
                        <?php endif; ?>
                        
                        <?php if ($personal && !empty($personal['phone'])): ?>
                            <li><i class="fas fa-phone text-muted me-2"></i><?php echo htmlspecialchars($personal['phone']); ?></li>
                        <?php endif; ?>
                        
                        <?php if ($personal && !empty($personal['linkedin'])): ?>
                            <li><i class="fab fa-linkedin text-muted me-2"></i>LinkedIn профиль</li>
                        <?php endif; ?>
                        
                        <?php if ($personal && !empty($personal['website'])): ?>
                            <li><i class="fas fa-globe text-muted me-2"></i><?php echo htmlspecialchars($personal['website']); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Футер -->
        <div class="footer">
            <p>Резюме создано с помощью Конструктора Резюме • <?php echo date('d.m.Y'); ?></p>
        </div>
    </div>
</body>
</html>