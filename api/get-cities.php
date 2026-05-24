<?php
/**
 * AJAX API: Get Cities by State
 * Student Record Management System
 */
header('Content-Type: application/json');
require_once(__DIR__ . '/../config.php');

// Security Check: Only logged in admins can access
if (!isset($_SESSION['alogin']) || strlen($_SESSION['alogin']) == 0) {
    http_response_code(403);
    echo json_encode(array('error' => 'Unauthorized access'));
    exit;
}

$state_id = isset($_GET['state_id']) ? intval($_GET['state_id']) : 0;

if ($state_id <= 0) {
    echo json_encode(array());
    exit;
}

try {
    $stmt = $dbh->prepare("SELECT id, name FROM cities WHERE state_id = :sid ORDER BY name ASC");
    $stmt->execute(array(':sid' => $state_id));
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($cities);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
?>
