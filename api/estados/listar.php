<?php
require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT Id_estado, Nombre FROM Estado ORDER BY Id_estado");
    echo json_encode($stmt->fetchAll());
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>