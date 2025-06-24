<?php
require_once 'config.php';
$headers = apache_request_headers();
$api_key = $headers['Api-Key'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM api_keys WHERE api_key = ?");
$stmt->execute([$api_key]);
if ($stmt->rowCount() === 0) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized API Key."]);
    exit;
}
?>