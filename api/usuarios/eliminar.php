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

if (!isset($data['id'])) {
    echo json_encode(['error' => 'ID de usuario requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM Usuario WHERE Id_usuario = ?");
    $stmt->execute([$data['id']]);
    
    echo json_encode(['success' => true, 'mensaje' => 'Usuario eliminado']);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>