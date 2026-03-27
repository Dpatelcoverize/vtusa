<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/includes/dbConnect.php';
require_once "../encrypt.php";

header('Content-Type: application/json');

if (!isset($_POST['id'], $_POST['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = decryptData($_POST['id']);
$status = $_POST['status'] === 'Y' ? 'Y' : 'N';

$stmt = $link->prepare("UPDATE Users SET isActive = ? WHERE userID = ?");
$stmt->bind_param("si", $status, $userId);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'User status updated successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Database update failed'
    ]);
}
