<?php
require_once 'config.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Generate a new random API key
    $new_key = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO api_keys (api_key) VALUES (?)");
    $stmt->execute([$new_key]);
    echo json_encode(["success" => true, "api_key" => $new_key]);
    exit();
}
// GET: List all API keys
$stmt = $pdo->query("SELECT api_key FROM api_keys ORDER BY id DESC");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC)); 