<?php
require_once 'db.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$from = $_GET['from'] ?? 'index';
if($id > 0) {
    $stmt = $pdo->prepare("UPDATE donors SET availability = CASE WHEN availability='available' THEN 'not_available' ELSE 'available' END WHERE id = :id");
    $stmt->execute([':id'=>$id]);
}
header("Location: " . ($from === 'donors' ? 'donors.php' : 'index.php'));
exit;
