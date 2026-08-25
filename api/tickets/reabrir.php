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

if (!isset($data['id_ticket'])) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

$id_ticket = intval($data['id_ticket']);
$usuario_id = $_SESSION['usuario_id'];
$motivo = isset($data['motivo']) ? trim($data['motivo']) : 'Reabierto por insatisfacción';

try {
    $stmt = $pdo->prepare("SELECT t.*, u.Nombre as usuario_nombre 
                           FROM Ticket t
                           LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    if ($ticket['Id_usuario'] != $usuario_id && !in_array('Administrador', $_SESSION['roles'] ?? [])) {
        echo json_encode(['error' => 'No tienes permiso para reabrir este ticket']);
        exit;
    }
    
    if ($ticket['Id_estado'] != 4) {
        echo json_encode(['error' => 'Solo se pueden reabrir tickets cerrados']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("UPDATE Ticket 
                          SET Id_estado = 1, 
                              Id_tecnico_asignado = NULL,
                              Reabierto = 1,
                              Fecha_reabierto = NOW(),
                              Fecha_actualizacion = NOW()
                          WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    
    $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                          (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario, Fecha_cambio) 
                          VALUES (?, 'Cerrado', 'Reabierto - Pool', ?, NOW())");
    $stmt->execute([$id_ticket, $usuario_id]);
    
    $stmt = $pdo->prepare("INSERT INTO Comentario (Id_ticket, Id_usuario, Contenido, Fecha_registro) 
                          VALUES (?, ?, ?, NOW())");
    $comentario = "Ticket reabierto. Motivo: " . $motivo;
    $stmt->execute([$id_ticket, $usuario_id, $comentario]);
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Ticket reabierto y enviado a pool',
        'ticket' => [
            'id' => $id_ticket,
            'estado' => 'Abierto',
            'folio' => $ticket['Folio']
        ]
    ]);
    
} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>