<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notificacion WHERE Id_usuario = ? AND Leida = 0");
    $stmt->execute([$usuario_id]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['total' => intval($count['total'])]);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>