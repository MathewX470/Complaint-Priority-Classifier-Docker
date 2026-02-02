<?php
require_once '../backend/config.php';
require_once '../backend/complaint_api.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(false, 'Unauthorized', null, 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET['id'])) {
    jsonResponse(false, 'Invalid request', null, 400);
}

$complaintAPI = new ComplaintAPI();
$result = $complaintAPI->getComplaintDetails($_GET['id']);

if ($result['success']) {
    jsonResponse(true, 'Details retrieved successfully', [
        'details' => $result['details'],
        'history' => $result['history']
    ]);
} else {
    jsonResponse(false, $result['message'], null, 404);
}
?>
