<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';
$notificaciones_path = __DIR__ . '/../notificaciones/crear.php';
if (file_exists($notificaciones_path)) {
    require_once $notificaciones_path;
} else {
    function crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje) { return true; }
    function notificarUsuariosTicket($ticket_id, $mensaje, $tipo, $usuarios_excluir = []) { return true; }
    function notificarAdministradores($mensaje, $tipo, $ticket_id = null, $usuarios_excluir = []) { return true; }
    function notificarTecnicos($mensaje, $tipo, $ticket_id = null, $usuarios_excluir = []) { return true; }
}

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['titulo']) || !isset($data['descripcion'])) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}

try {
    $pdo->beginTransaction();

    $folio = generarFolio();
    $fecha_vencimiento = date('Y-m-d H:i:s', strtotime('+48 hours'));
    $titulo = $data['titulo'];
    $descripcion = $data['descripcion'];
    $id_categoria = $data['id_categoria'] ?? 1;
    $id_prioridad = $data['id_prioridad'] ?? 3;

    $stmt = $pdo->prepare("INSERT INTO Ticket 
                          (Folio, Titulo, Descripcion, Id_usuario, Id_categoria, Id_prioridad, Fecha_vencimiento) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$folio, $titulo, $descripcion, $usuario_id, $id_categoria, $id_prioridad, $fecha_vencimiento]);
    $ticket_id = $pdo->lastInsertId();


    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                          VALUES (?, 'Nuevo', 'Abierto', ?)");
    $stmt->execute([$ticket_id, $usuario_id]);

    $pdo->commit();

    // envio de notificaciones
    if (function_exists('notificarAdministradores') && function_exists('notificarTecnicos')) {
        $mensaje = "Nuevo ticket: {$folio} - {$titulo}";
        notificarAdministradores($mensaje, 'ticket_nuevo', $ticket_id, [$usuario_id]);
        notificarTecnicos($mensaje, 'ticket_nuevo', $ticket_id, [$usuario_id]);
    }

    echo json_encode([
        'success' => true,
        'id' => $ticket_id,
        'folio' => $folio,
        'mensaje' => 'Ticket creado exitosamente'
    ]);

} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>