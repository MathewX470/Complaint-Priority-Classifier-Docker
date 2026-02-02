<?php
require_once '../backend/config.php';
require_once '../backend/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: login.php');
exit;
?>
