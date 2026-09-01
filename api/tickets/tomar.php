<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../notificaciones/crear.php';

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
$usuario_nombre = $_SESSION['nombre'] ?? 'Técnico';

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
    
    if ($ticket['Id_tecnico_asignado'] !== null) {
        echo json_encode(['error' => 'Este ticket ya fue asignado a otro técnico']);
        exit;
    }
    
    if ($ticket['Id_estado'] != 1) {
        echo json_encode(['error' => 'Este ticket no está disponible (estado: ' . ($ticket['estado_nombre'] ?? 'desconocido') . ')']);
        exit;
    }
    
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE Ticket 
                          SET Id_tecnico_asignado = ?, 
                              Id_estado = 2,
                              Fecha_actualizacion = NOW()
                          WHERE Id_ticket = ?");
    $stmt->execute([$usuario_id, $id_ticket]);

    $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                          (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario, Fecha_cambio) 
                          VALUES (?, 'Abierto', 'En proceso', ?, NOW())");
    $stmt->execute([$id_ticket, $usuario_id]);
    
    $pdo->commit();

    if ($ticket['Id_usuario'] != $usuario_id) {
        notificarUsuariosTicket(
            $id_ticket,
            "El técnico {$usuario_nombre} tomó el ticket {$ticket['Folio']}",
            'ticket_tomado',
            [$usuario_id] 
        );
    }

    notificarAdministradores(
        "El técnico {$usuario_nombre} tomó el ticket {$ticket['Folio']}",
        'ticket_tomado',
        $id_ticket,
        [$usuario_id]
    );
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Ticket asignado exitosamente',
        'tecnico' => $usuario_nombre
    ]);
    
} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>