<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Check if user is logged in for admin panel access
$is_browser_request = false;
if (isset($_SERVER['HTTP_USER_AGENT'])) {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $is_browser_request = (strpos($user_agent, 'Mozilla') !== false || 
                          strpos($user_agent, 'Chrome') !== false || 
                          strpos($user_agent, 'Safari') !== false || 
                          strpos($user_agent, 'Firefox') !== false);
}

// For browser requests (admin panel), require login
if ($is_browser_request && !isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Authentication required. Please login first."]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Only require API key for non-browser requests (external API calls)
$require_api_key = !$is_browser_request;

// For POST requests, allow admin panel form submissions without API key
if ($method === 'POST' && $is_browser_request) {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/x-www-form-urlencoded') !== false || 
        strpos($contentType, 'multipart/form-data') !== false) {
        $require_api_key = false;
    }
}

if ($require_api_key) {
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $system_api_key = $headers['Api-Key'] ?? ($headers['api-key'] ?? '');
    if (!$system_api_key) {
        echo json_encode(["error" => "Missing Api-Key header."]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ?");
    $stmt->execute([$system_api_key]);
    if ($stmt->rowCount() === 0) {
        echo json_encode(["error" => "Invalid Api-Key."]);
        exit;
    }
}

switch($method) {
    case 'GET':
        if (isset($_GET['category'])) {
            $category = $_GET['category'];
            $stmt = $pdo->prepare("SELECT * FROM questions WHERE category = ?");
            $stmt->execute([$category]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        } else {
            $stmt = $pdo->query("SELECT * FROM questions");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;
    case 'POST':
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $data = json_decode(file_get_contents("php://input"), true);
        } else {
            $data = $_POST;
        }
        $stmt = $pdo->prepare("INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_option, category, created_by_ai)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['question'], $data['option_a'], $data['option_b'],
            $data['option_c'], $data['option_d'], $data['correct_option'],
            $data['category'] ?? null,
            $data['created_by_ai'] ?? 0
        ]);
        echo json_encode(["success" => true, "message" => "Question added successfully!"]);
        break;
    case 'PUT':
        parse_str(file_get_contents("php://input"), $data);
        $stmt = $pdo->prepare("UPDATE questions SET question=?, option_a=?, option_b=?, option_c=?, option_d=?, correct_option=?, category=? WHERE id=?");
        $stmt->execute([
            $data['question'], $data['option_a'], $data['option_b'],
            $data['option_c'], $data['option_d'], $data['correct_option'],
            $data['category'] ?? null,
            $data['id']
        ]);
        echo json_encode(["success" => true]);
        break;
    case 'DELETE':
        parse_str(file_get_contents("php://input"), $data);
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id=?");
        $stmt->execute([$data['id']]);
        echo json_encode(["success" => true]);
        break;
}
?>