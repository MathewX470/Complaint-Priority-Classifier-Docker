<?php
require_once '../backend/config.php';
require_once '../backend/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$required = ['fullName', 'email', 'password'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        jsonResponse(false, ucfirst($field) . ' is required', null, 400);
    }
}

$auth = new Auth();
$result = $auth->register(
    $input['fullName'],
    $input['email'],
    $input['password'],
    $input['phone'] ?? null
);

if ($result['success']) {
    jsonResponse(true, $result['message'], ['user_id' => $result['user_id']]);
} else {
    jsonResponse(false, $result['message'], null, 400);
}
?>
