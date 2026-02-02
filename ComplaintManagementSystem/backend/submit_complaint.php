<?php
require_once '../backend/config.php';
require_once '../backend/complaint_api.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized', null, 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method', null, 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['complaint_text']) || empty(trim($input['complaint_text']))) {
    jsonResponse(false, 'Complaint text is required', null, 400);
}

$complaintAPI = new ComplaintAPI();
$result = $complaintAPI->submitComplaint($_SESSION['user_id'], $input['complaint_text']);

if ($result['success']) {
    jsonResponse(true, $result['message'], [
        'complaint_id' => $result['complaint_id'],
        'priority' => $result['priority']
    ]);
} else {
    jsonResponse(false, $result['message'], null, 400);
}
?>
