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

if (!isset($data['id_ticket']) || !isset($data['prioridad'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id_ticket = intval($data['id_ticket']);
$prioridad = trim($data['prioridad']);
$usuario_id = $_SESSION['usuario_id'];
$roles = $_SESSION['roles'] ?? ['Usuario'];


$prioridades_validas = ['Baja', 'Media', 'Alta', 'Crítica'];
if (!in_array($prioridad, $prioridades_validas)) {
    echo json_encode(['error' => 'Prioridad no válida']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT t.*, e.Nombre as estado_nombre 
                           FROM Ticket t
                           LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    // Solo técnicos o admin pueden cambiar prioridad
    if (!in_array('Administrador', $roles) && !in_array('Técnico', $roles)) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para cambiar la prioridad']);
        exit;
    }
    if ($ticket['Prioridad_confirmada'] !== null) {
        echo json_encode(['error' => 'Este ticket ya tiene una prioridad confirmada. No se puede volver a cambiar.']);
        exit;
    }
    
    // Guardar prioridad confirmada
    $stmt = $pdo->prepare("UPDATE Ticket 
                          SET Prioridad_confirmada = ?,
                              Fecha_actualizacion = NOW()
                          WHERE Id_ticket = ?");
    $stmt->execute([$prioridad, $id_ticket]);
    $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                          (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario, Fecha_cambio) 
                          VALUES (?, ?, CONCAT('Prioridad confirmada: ', ?), ?, NOW())");
    $estado_actual = $ticket['estado_nombre'] ?? 'Abierto';
    $stmt->execute([$id_ticket, $estado_actual, $prioridad, $usuario_id]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => "Prioridad confirmada: $prioridad",
        'prioridad' => $prioridad
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>