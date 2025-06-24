<?php
$host = "localhost";
$dbname = "quiz_db";
$username = "root";
$password = "";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
} catch (PDOException $e) {
    die(json_encode(["error" => "Database connection failed."]));
}
?>