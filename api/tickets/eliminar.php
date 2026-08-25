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
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

$id = intval($data['id']);
$usuario_id = $_SESSION['usuario_id'];
$roles = $_SESSION['roles'] ?? ['Usuario'];

try {
    $stmt = $pdo->prepare("SELECT Id_usuario FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    if (!in_array('Administrador', $roles) && $ticket['Id_usuario'] != $usuario_id) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para eliminar este ticket']);
        exit;
    }
    
    // Eliminar ticket 
    //preguntar a luis si lo elimina de la base o solo de interfaz
    $stmt = $pdo->prepare("DELETE FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id]);
    
    echo json_encode(['success' => true, 'mensaje' => 'Ticket eliminado correctamente']);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>