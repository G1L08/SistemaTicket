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

$data = json_decode(file_get_contents('php://input'), true);
$id_notificacion = isset($data['id_notificacion']) ? intval($data['id_notificacion']) : 0;

if (!$id_notificacion) {
    echo json_encode(['error' => 'ID de notificacion requerido']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
    $stmt = $pdo->prepare("UPDATE notificacion SET Leida = 1 WHERE Id_notificacion = ? AND Id_usuario = ?");
    $stmt->execute([$id_notificacion, $usuario_id]);
    echo json_encode(['success' => true]);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>