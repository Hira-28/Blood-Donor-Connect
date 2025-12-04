<?php
require_once 'db.php'; 

header('Content-Type: application/json; charset=utf-8');

try {
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM donors");
    $total = $stmt ? (int)$stmt->fetchColumn() : 0;

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM donors WHERE availability = :avail");
    $stmt->execute([':avail' => 'available']);
    $available = $stmt ? (int)$stmt->fetchColumn() : 0;
 
    $unavailable = max(0, $total - $available);

    $stmt = $pdo->prepare("
        SELECT id, name, city, blood_group
        FROM donors
        WHERE availability = :avail
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([':avail' => 'available']);
    $availableList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $availableList = array_map(function($row){
        $row['id'] = isset($row['id']) ? (int)$row['id'] : null;
        return $row;
    }, $availableList);

    echo json_encode([
        'ok' => true,
        'total_donors' => $total,
        'available' => $available,
        'unavailable' => $unavailable,
        'available_list' => $availableList,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
