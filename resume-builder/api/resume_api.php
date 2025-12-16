<?php
require_once '../config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Получение данных из запроса
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Получение резюме пользователя
        if (isset($_GET['id'])) {
            $resume_id = intval($_GET['id']);
            $stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
            $stmt->execute([$resume_id, $user_id]);
            $resume = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resume) {
                echo json_encode($resume);
            } else {
                echo json_encode(['error' => 'Резюме не найдено']);
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY updated_at DESC");
            $stmt->execute([$user_id]);
            $resumes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($resumes);
        }
        break;
        
    case 'POST':
        // Создание нового резюме
        if (isset($input['duplicate_id'])) {
    // Дублирование существующего резюме
    $duplicate_id = intval($input['duplicate_id']);
    
    // Проверка прав доступа
    $checkStmt = $pdo->prepare("SELECT user_id FROM resumes WHERE id = ?");
    $checkStmt->execute([$duplicate_id]);
    $original = $checkStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($original && $original['user_id'] == $user_id) {
        // Здесь код для дублирования резюме...
        // (Нужно скопировать все связанные таблицы)
        echo json_encode(['success' => true, 'resume_id' => $new_resume_id]);
    } else {
        echo json_encode(['error' => 'Доступ запрещен']);
    }
    exit;
}

        if (isset($input['title'])) {
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("INSERT INTO resumes (user_id, title, template) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $input['title'], $input['template'] ?? 'classic']);
                $resume_id = $pdo->lastInsertId();
                
                // Создание пустой персональной информации
                $stmt = $pdo->prepare("INSERT INTO personal_info (resume_id, full_name) VALUES (?, ?)");
                $stmt->execute([$resume_id, 'Новое резюме']);
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'resume_id' => $resume_id]);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['error' => $e->getMessage()]);
            }
        }
        break;
        
    case 'PUT':
        // Обновление резюме
        parse_str(file_get_contents("php://input"), $put_vars);
        if (isset($input['public_view'])) {
    $updates[] = 'public_view = ?';
    $params[] = $input['public_view'] ? 1 : 0;
}

        if (isset($put_vars['id'])) {
            $resume_id = intval($put_vars['id']);
            
            // Проверка прав доступа
            $stmt = $pdo->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
            $stmt->execute([$resume_id, $user_id]);
            
            if ($stmt->fetch()) {
                $updates = [];
                $params = [];
                
                if (isset($put_vars['title'])) {
                    $updates[] = 'title = ?';
                    $params[] = $put_vars['title'];
                }
                
                if (isset($put_vars['template'])) {
                    $updates[] = 'template = ?';
                    $params[] = $put_vars['template'];
                }
                
                if (!empty($updates)) {
                    $params[] = $resume_id;
                    $sql = "UPDATE resumes SET " . implode(', ', $updates) . " WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    
                    echo json_encode(['success' => true]);
                }
            } else {
                echo json_encode(['error' => 'Доступ запрещен']);
            }
        }
        break;
        
    case 'DELETE':
        // Удаление резюме
        if (isset($_GET['id'])) {
            $resume_id = intval($_GET['id']);
            
            // Проверка прав доступа
            $stmt = $pdo->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
            $stmt->execute([$resume_id, $user_id]);
            
            if ($stmt->fetch()) {
                $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
                $stmt->execute([$resume_id]);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['error' => 'Доступ запрещен']);
            }
        }
        break;
        
    default:
        echo json_encode(['error' => 'Метод не поддерживается']);
}
?>