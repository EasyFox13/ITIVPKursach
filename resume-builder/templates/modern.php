<?php
// modern.php - Современный шаблон резюме
$resume_id = $resume['id'];
$template = 'modern';

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
    <title><?php echo htmlspecialchars($resume['title']); ?> - Современное резюме</title>
    <style>
        /* Современный стиль резюме */
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
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        /* Левая панель */
        .sidebar {
            width: 45%;
            background: linear-gradient(135deg, #1a237e 0%, #283593 100%);
            color: white;
            padding: 40px 30px;
            min-height: 100%;
            position: absolute;
            left: 0;
            top: 0;
        }
        
        /* Правая панель */
        .main-content {
            width: 55%;
            padding: 40px 40px 40px 50px;
            margin-left: 45%;
        }
        
       
        
        /* Заголовки */
        .section-title {
            color: #1a237e;
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #448aff;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: #1a237e;
        }
        
        .sidebar-title {
            color: #bbdefb;
            font-size: 18px;
            font-weight: 600;
            margin: 30px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        /* Контактная информация */
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            color: #e3f2fd;
        }
        
        .contact-item i {
            width: 20px;
            margin-right: 12px;
            color: #448aff;
        }
        
        /* Навыки */
        .skill-item {
            margin-bottom: 15px;
        }
        
        .skill-name {
            display: block;
            margin-bottom: 5px;
            color: #e3f2fd;
        }
        
        .skill-bar {
            height: 6px;
            background: rgba(255,255,255,0.1);
            border-radius: 3px;
            overflow: hidden;
        }
        
        .skill-level {
            height: 100%;
            background: #448aff;
            border-radius: 3px;
        }
        
        /* Опыт работы и образование */
        .timeline-item {
            margin-bottom: 25px;
            position: relative;
            padding-left: 25px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            width: 12px;
            height: 12px;
            background: #448aff;
            border-radius: 50%;
        }
        
        .timeline-item::after {
            content: '';
            position: absolute;
            left: 5px;
            top: 17px;
            bottom: -25px;
            width: 2px;
            background: #e0e0e0;
        }
        
        .timeline-item:last-child::after {
            display: none;
        }
        
        .timeline-date {
            color: #448aff;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .timeline-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #1a237e;
        }
        
        .timeline-subtitle {
            color: #666;
            font-style: italic;
            margin-bottom: 8px;
        }
        
        /* Список */
        .list-item {
            position: relative;
            padding-left: 20px;
            margin-bottom: 8px;
        }
        
        .list-item::before {
            content: '▸';
            position: absolute;
            left: 0;
            color: #000000;
        }
        
        /* Имя и должность */
        .name {
            font-size: 32px;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        
        .position {
            font-size: 18px;
            color: #bbdefb;
            margin-bottom: 30px;
            font-weight: 400;
        }
        
        /* О себе */
        .summary {
            background: #f5f7ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #000000;
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
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="resume-container">
        <!-- Левая панель -->
        <div class="sidebar">        
            <!-- Имя и должность -->
            <?php if ($personal && !empty($personal['full_name'])): ?>
                <h1 class="name"><?php echo htmlspecialchars($personal['full_name']); ?></h1>
                
            <?php endif; ?>
            
            <!-- Контакты -->
            <h3 class="sidebar-title"><i class="fas fa-address-card me-2"></i>Контакты</h3>
            <div class="contact-info">
                <?php if ($personal && !empty($personal['phone'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($personal['phone']); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($personal && !empty($personal['email'])): ?>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($personal['email']); ?></span>
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
                
                <?php if ($personal && !empty($personal['linkedin'])): ?>
                    <div class="contact-item">
                        <i class="fab fa-linkedin"></i>
                        <span><?php echo htmlspecialchars($personal['linkedin']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Навыки -->
            <?php if (!empty($skills)): ?>
                <h3 class="sidebar-title"><i class="fas fa-cogs me-2"></i>Навыки</h3>
                <div class="skills">
                    <?php foreach ($skills as $skill): ?>
                        <div class="skill-item">
                            <span class="skill-name"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                            <div class="skill-bar">
                                <div class="skill-level" style="width: <?php echo min(100, $skill['proficiency']); ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Языки -->
            <h3 class="sidebar-title"><i class="fas fa-language me-2"></i>Языки</h3>
            <div class="languages">
                <div class="skill-item">
                    <span class="skill-name">Русский (Родной)</span>
                    <div class="skill-bar">
                        <div class="skill-level" style="width: 100%"></div>
                    </div>
                </div>
                <div class="skill-item">
                    <span class="skill-name">Английский (B2)</span>
                    <div class="skill-bar">
                        <div class="skill-level" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Правая панель -->
        <div class="main-content">
            <!-- О себе -->
<?php if ($personal && !empty($personal['summary'])): ?>
    <?php 
    // Разделяем текст на строки
    $summary_lines = explode("\n", $personal['summary']);
    ?>
    
    <div class="position" style="color: black;">
        <?php foreach ($summary_lines as $line): ?>
            <?php if (!empty(trim($line))): ?>
                <div><?php echo htmlspecialchars(trim($line)); ?></div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
            
            <!-- Опыт работы -->
            <?php if (!empty($experience)): ?>
                <h2 class="section-title"><i class="fas fa-briefcase me-2"></i>Опыт работы</h2>
                <div class="timeline">
                    <?php foreach ($experience as $exp): ?>
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php 
                                $start_date = date('m.Y', strtotime($exp['start_date']));
                                $end_date = $exp['end_date'] ? date('m.Y', strtotime($exp['end_date'])) : 'Наст. время';
                                echo $start_date . ' - ' . $end_date;
                                ?>
                            </div>
                            <h3 class="timeline-title"><?php echo htmlspecialchars($exp['position']); ?></h3>
                            <div class="timeline-subtitle">
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
                <h2 class="section-title"><i class="fas fa-graduation-cap me-2"></i>Образование</h2>
                <div class="timeline">
                    <?php foreach ($education as $edu): ?>
                        <div class="timeline-item">
                            <div class="timeline-date">
                                <?php echo date('Y', strtotime($edu['graduation_date'])); ?>
                            </div>
                            <h3 class="timeline-title"><?php echo htmlspecialchars($edu['degree']); ?></h3>
                            <div class="timeline-subtitle">
                                <?php echo htmlspecialchars($edu['institution']); ?> • <?php echo htmlspecialchars($edu['location']); ?>
                            </div>
                            <?php if (!empty($edu['description'])): ?>
                                <p><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Достижения -->
                            

            <?php if ($personal && !empty($personal['summary'])): ?>
                <h2 class="section-title"><i class="fas fa-trophy me-2"></i>Достижения</h2>
                <div class="achievements">
                    <div class="list-item">Успешная реализация 15+ проектов</div>
                    <div class="list-item">Повышение эффективности процессов на 30%</div>
                    <div class="list-item">Наставничество для 5 junior-разработчиков</div>
                </div>
            <?php endif; ?>
            
            <!-- Дата создания -->
            <div style="margin-top: 50px; padding-top: 20px; border-top: 1px solid #eee; color: #888; font-size: 12px; text-align: center;">
                Резюме создано <?php echo date('d.m.Y'); ?> с помощью Конструктора Резюме
            </div>
        </div>
    </div>
</body>
</html>