<?php
require_once '../backend/config.php';
require_once '../backend/complaint_api.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    jsonResponse(false, 'Unauthorized', null, 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$required = ['complaint_id', 'new_status'];
foreach ($required as $field) {
    if (!isset($input[$field])) {
        jsonResponse(false, ucfirst($field) . ' is required', null, 400);
    }
}

$complaintAPI = new ComplaintAPI();
$result = $complaintAPI->updateComplaintStatus(
    $input['complaint_id'],
    $input['new_status'],
    $_SESSION['user_id'],
    $input['notes'] ?? null
);

if ($result['success']) {
    jsonResponse(true, $result['message']);
} else {
    jsonResponse(false, $result['message'], null, 400);
}
?>
