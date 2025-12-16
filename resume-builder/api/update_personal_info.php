<?php
// api/update_personal_info.php
require_once '../config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Получаем данные из формы
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['resume_id'])) {
        echo json_encode(['error' => 'ID резюме не указан']);
        exit;
    }
    
    $resume_id = intval($input['resume_id']);
    
    // Проверяем права доступа к резюме
    $stmt = $pdo->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $user_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Доступ запрещен']);
        exit;
    }
    
    try {
        // Проверяем, есть ли уже запись в personal_info
        $stmt = $pdo->prepare("SELECT id FROM personal_info WHERE resume_id = ?");
        $stmt->execute([$resume_id]);
        $exists = $stmt->fetch();
        
        if ($exists) {
            // Обновляем существующую запись
            $sql = "UPDATE personal_info SET 
                    full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    address = :address,
                    summary = :summary,
                    website = :website,
                    linkedin = :linkedin,
                    updated_at = NOW()
                    WHERE resume_id = :resume_id";
                    
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':full_name' => $input['full_name'] ?? '',
                ':email' => $input['email'] ?? '',
                ':phone' => $input['phone'] ?? '',
                ':address' => $input['address'] ?? '',
                ':summary' => $input['summary'] ?? '',
                ':website' => $input['website'] ?? '',
                ':linkedin' => $input['linkedin'] ?? '',
                ':resume_id' => $resume_id
            ]);
            
            $action = 'updated';
        } else {
            // Создаем новую запись
            $sql = "INSERT INTO personal_info 
                    (resume_id, full_name, email, phone, address, summary, website, linkedin, created_at, updated_at)
                    VALUES (:resume_id, :full_name, :email, :phone, :address, :summary, :website, :linkedin, NOW(), NOW())";
                    
            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ':resume_id' => $resume_id,
                ':full_name' => $input['full_name'] ?? '',
                ':email' => $input['email'] ?? '',
                ':phone' => $input['phone'] ?? '',
                ':address' => $input['address'] ?? '',
                ':summary' => $input['summary'] ?? '',
                ':website' => $input['website'] ?? '',
                ':linkedin' => $input['linkedin'] ?? ''
            ]);
            
            $action = 'created';
        }
        
        // Обновляем время изменения резюме
        $stmt = $pdo->prepare("UPDATE resumes SET updated_at = NOW() WHERE id = ?");
        $stmt->execute([$resume_id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Персональная информация успешно сохранена',
            'action' => $action
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
    }
    
} else {
    echo json_encode(['error' => 'Метод не поддерживается']);
}
?>