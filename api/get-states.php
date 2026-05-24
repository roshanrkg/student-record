<?php
/**
 * AJAX API: Get States by Country
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

$country_id = isset($_GET['country_id']) ? intval($_GET['country_id']) : 0;

if ($country_id <= 0) {
    echo json_encode(array());
    exit;
}

try {
    $stmt = $dbh->prepare("SELECT id, name FROM states WHERE country_id = :cid ORDER BY name ASC");
    $stmt->execute(array(':cid' => $country_id));
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($states);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => 'Database error: ' . $e->getMessage()));
}
?>
