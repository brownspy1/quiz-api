<?php
require_once 'config.php';
header('Content-Type: application/json');

// IMPORTANT: This is YOUR Gemini API key that will be used for ALL AI question generation
// Users do NOT need their own Gemini API keys - they will use this key
// If you want users to use their own keys, you would need to modify this system
$gemini_api_key = 'AIzaSyCo1Vl0PDP2-eic3FIFLsPbCCN5dzPaZx8'; // <-- Your Gemini API key for all users

// Require a valid system API key in the Api-Key header
$headers = getallheaders();
$system_api_key = $headers['Api-Key'] ?? '';
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

$prompt = $_POST['prompt'] ?? '';

// Get existing categories from database
$stmt = $pdo->query("SELECT DISTINCT category FROM questions WHERE category IS NOT NULL AND category != '' ORDER BY category");
$existing_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Create category list for AI prompt
$category_list = implode(', ', $existing_categories);
$category_instruction = $existing_categories ? "Use one of these existing categories: $category_list" : "Create a relevant category name";

// Custom pre-prompt to force strict MCQ JSON output with existing categories
$pre_prompt = "You are an MCQ generator for a quiz app. Always return a single multiple choice question in strict JSON format with these fields: question, option_a, option_b, option_c, option_d, correct_option (A/B/C/D), and category. $category_instruction. Do not return code, markdown, or explanations. Only output the JSON object. Example: {\"question\":\"What is CMS?\",\"option_a\":\"Content Management System\",\"option_b\":\"Website\",\"option_c\":\"Burger\",\"option_d\":\"Blog\",\"correct_option\":\"A\",\"category\":\"IT\"}";

$full_prompt = $pre_prompt . "\n" . $prompt;

$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$gemini_api_key";
$data = [
    "contents" => [[ "parts" => [[ "text" => $full_prompt ]] ]]
];
$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json",
        'content' => json_encode($data)
    ]
];
$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

// Try to extract question data from Gemini response
$gemini = json_decode($response, true);
$raw = '';
if (isset($gemini['candidates'][0]['content']['parts'][0]['text'])) {
    $raw = $gemini['candidates'][0]['content']['parts'][0]['text'];
} else if (isset($gemini['question'])) {
    // fallback if Gemini returns direct JSON
    $raw = json_encode($gemini);
}

// Try to find JSON in the Gemini text response
if ($raw && preg_match('/\{.*\}/s', $raw, $matches)) {
    $qdata = json_decode($matches[0], true);
} else {
    $qdata = null;
}

if (!$qdata || !isset($qdata['question'], $qdata['option_a'], $qdata['option_b'], $qdata['option_c'], $qdata['option_d'], $qdata['correct_option'])) {
    echo json_encode(["error" => "Gemini response did not contain a valid question format.", "gemini_raw" => $raw]);
    exit;
}

// Use category from Gemini response
$final_category = $qdata['category'] ?? '';

// Insert into database
$stmt = $pdo->prepare("INSERT INTO questions (question, option_a, option_b, option_c, option_d, correct_option, category, created_by_ai) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
$stmt->execute([
    $qdata['question'],
    $qdata['option_a'],
    $qdata['option_b'],
    $qdata['option_c'],
    $qdata['option_d'],
    $qdata['correct_option'],
    $final_category
]);
$insert_id = $pdo->lastInsertId();

// Return success response with formatted data
echo json_encode([
    "success" => true,
    "message" => "Question added successfully!",
    "question" => $qdata['question'],
    "options" => [
        "A" => $qdata['option_a'],
        "B" => $qdata['option_b'],
        "C" => $qdata['option_c'],
        "D" => $qdata['option_d']
    ],
    "correct_option" => $qdata['correct_option'],
    "category" => $final_category,
    "id" => $insert_id
]);
?>