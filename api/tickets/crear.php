<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 1;
$id_prioridad = isset($_POST['id_prioridad']) ? intval($_POST['id_prioridad']) : 3;

if (empty($titulo) || empty($descripcion)) {
    echo json_encode(['error' => 'Titulo y descripcion son obligatorios']);
    exit;
}

try {
    $pdo->beginTransaction();

    $folio = generarFolio();
    $fecha_vencimiento = date('Y-m-d H:i:s', strtotime('+48 hours'));

    $stmt = $pdo->prepare("INSERT INTO Ticket 
                          (Folio, Titulo, Descripcion, Id_usuario, Id_categoria, Id_prioridad, Fecha_vencimiento) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$folio, $titulo, $descripcion, $usuario_id, $id_categoria, $id_prioridad, $fecha_vencimiento]);
    $ticket_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                          VALUES (?, 'Nuevo', 'Abierto', ?)");
    $stmt->execute([$ticket_id, $usuario_id]);

    $pdo->commit();

    // notificaciones
    $notificaciones_path = __DIR__ . '/../notificaciones/crear.php';
    if (file_exists($notificaciones_path)) {
        require_once $notificaciones_path;
        if (function_exists('notificarAdministradores')) {
            $mensaje = "Nuevo ticket: {$folio} - {$titulo}";
            notificarAdministradores($mensaje, 'ticket_nuevo', $ticket_id, [$usuario_id]);
        }
        if (function_exists('notificarTecnicos')) {
            $mensaje = "Nuevo ticket: {$folio} - {$titulo}";
            notificarTecnicos($mensaje, 'ticket_nuevo', $ticket_id, [$usuario_id]);
        }
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