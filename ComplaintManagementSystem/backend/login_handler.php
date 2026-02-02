<?php
require_once '../backend/config.php';
require_once '../backend/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !isset($input['password'])) {
    jsonResponse(false, 'Email and password are required', null, 400);
}

$auth = new Auth();
$result = $auth->login($input['email'], $input['password']);

if ($result['success']) {
    jsonResponse(true, $result['message'], ['role' => $result['role']]);
} else {
    jsonResponse(false, $result['message'], null, 401);
}
?>
