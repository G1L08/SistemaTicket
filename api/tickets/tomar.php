<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

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
$usuario_nombre = $_SESSION['nombre'] ?? 'Tecnico';

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
        echo json_encode(['error' => 'Este ticket ya fue asignado a otro tecnico']);
        exit;
    }
    
    if ($ticket['Id_estado'] != 1) {
        echo json_encode(['error' => 'Este ticket no esta disponible (estado: ' . ($ticket['estado_nombre'] ?? 'desconocido') . ')']);
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

    ob_start();
    try {
        $notif_path = __DIR__ . '/../notificaciones/crear.php';
        if (file_exists($notif_path)) {
            require_once $notif_path;

            if (function_exists('crearNotificacion')) {
                $folio  = $ticket['Folio'];
                $titulo = $ticket['Titulo'];

                if (!empty($ticket['Id_usuario'])) {
                    $msgCreador = "Tu ticket {$folio} - \"{$titulo}\" ha sido tomado por el técnico {$usuario_nombre}. Ahora está en proceso.";
                    crearNotificacion($ticket['Id_usuario'], $id_ticket, 'ticket_tomado', $msgCreador);
                }

                $msgTecnico = "Has tomado el ticket {$folio} - \"{$titulo}\".";
                crearNotificacion($usuario_id, $id_ticket, 'ticket_tomado', $msgTecnico);
            }
        }
    } catch (Exception $e) {
        error_log('Notificación tomar: ' . $e->getMessage());
    }
    ob_end_clean();

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