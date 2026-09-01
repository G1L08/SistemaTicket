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

if (!isset($data['id_ticket']) || !isset($data['horas'])) {
    echo json_encode(['error' => 'ID de ticket y horas requeridas']);
    exit;
}

$id_ticket = intval($data['id_ticket']);
$horas = intval($data['horas']);
$usuario_id = $_SESSION['usuario_id'];

if ($horas <= 0 || $horas > 168) { 
    echo json_encode(['error' => 'Las horas deben ser entre 1 y 168 (7 días)']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT t.*, u.Nombre as tecnico_nombre 
                           FROM Ticket t
                           LEFT JOIN Usuario u ON t.Id_tecnico_asignado = u.Id_usuario
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    if ($ticket['Id_tecnico_asignado'] != $usuario_id && !in_array('Administrador', $_SESSION['roles'] ?? [])) {
        echo json_encode(['error' => 'No tienes permiso para extender este ticket']);
        exit;
    }
    
    if (!in_array($ticket['Id_estado'], [2, 3])) {
        echo json_encode(['error' => 'Solo se pueden extender tickets en proceso o en espera']);
        exit;
    }
    
    // Extender fecha de vencimiento
    $stmt = $pdo->prepare("UPDATE Ticket 
                          SET Fecha_vencimiento = DATE_ADD(Fecha_vencimiento, INTERVAL ? HOUR),
                              Tiempo_extendido = COALESCE(Tiempo_extendido, 0) + ?,
                              Fecha_extension = NOW(),
                              Fecha_actualizacion = NOW()
                          WHERE Id_ticket = ?");
    $stmt->execute([$horas, $horas, $id_ticket]);
    
    $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                          (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario, Fecha_cambio) 
                          VALUES (?, ?, CONCAT('Extendido +', ?, 'h'), ?, NOW())");
    $stmt->execute([$id_ticket, $ticket['estado_nombre'] ?? 'En proceso', $horas, $usuario_id]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => "Tiempo extendido por $horas horas",
        'nueva_fecha' => date('Y-m-d H:i:s', strtotime($ticket['Fecha_vencimiento'] . " + $horas hours"))
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>