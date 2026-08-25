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

if (!isset($data['id']) || !isset($data['estado'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id = intval($data['id']);
$nuevo_estado = intval($data['estado']);
$usuario_id = $_SESSION['usuario_id'];
$roles = $_SESSION['roles'] ?? ['Usuario'];

try {
    // Verificar permisos
    $stmt = $pdo->prepare("SELECT Id_usuario, Id_tecnico_asignado, Id_estado FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    // Solo admin, técnico asignado o creador pueden cambiar estado
    if (!in_array('Administrador', $roles) && 
        $ticket['Id_tecnico_asignado'] != $usuario_id &&
        $ticket['Id_usuario'] != $usuario_id) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para cambiar el estado']);
        exit;
    }
    
    $estado_anterior = $ticket['Id_estado'];
    
    // Obtener nombre de estados
    $stmt = $pdo->prepare("SELECT Nombre FROM Estado WHERE Id_estado = ?");
    $stmt->execute([$estado_anterior]);
    $estado_anterior_nombre = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT Nombre FROM Estado WHERE Id_estado = ?");
    $stmt->execute([$nuevo_estado]);
    $estado_nuevo_nombre = $stmt->fetchColumn();
    
    // Actualizar ticket
    $stmt = $pdo->prepare("UPDATE Ticket SET Id_estado = ? WHERE Id_ticket = ?");
    $stmt->execute([$nuevo_estado, $id]);
    
    // Registrar en historial
    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                          VALUES (?, ?, ?, ?)");
    $stmt->execute([$id, $estado_anterior_nombre, $estado_nuevo_nombre, $usuario_id]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Estado actualizado correctamente'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>