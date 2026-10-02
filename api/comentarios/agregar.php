<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once '../config/database.php';
require_once '../notificaciones/crear.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_ticket']) || !isset($data['contenido'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id_ticket = intval($data['id_ticket']);
$contenido = trim($data['contenido']);
$usuario_id = $_SESSION['usuario_id'];

if (empty($contenido)) {
    echo json_encode(['error' => 'El comentario no puede estar vacio']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT t.Id_ticket, t.Folio, t.Titulo, t.Id_usuario, t.Id_tecnico_asignado 
                           FROM Ticket t
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    $stmtU = $pdo->prepare("SELECT Nombre, Apellido_paterno FROM Usuario WHERE Id_usuario = ?");
    $stmtU->execute([$usuario_id]);
    $u = $stmtU->fetch(PDO::FETCH_ASSOC);
    $nombreComentador = trim(($u['Nombre'] ?? '') . ' ' . ($u['Apellido_paterno'] ?? ''));
    if ($nombreComentador === '') $nombreComentador = 'Un usuario';
    
    $stmt = $pdo->prepare("INSERT INTO Comentario (Id_ticket, Id_usuario, Contenido) VALUES (?, ?, ?)");
    $stmt->execute([$id_ticket, $usuario_id, $contenido]);
    
    $id_comentario = $pdo->lastInsertId();

    ob_start();
    try {
        if (function_exists('crearNotificacion')) {
            $extracto = mb_strlen($contenido) > 100 ? mb_substr($contenido, 0, 100) . '...' : $contenido;
            $mensaje = "{$nombreComentador} comentó en el ticket {$ticket['Folio']} - \"{$ticket['Titulo']}\": {$extracto}";

            $destinatarios = array_unique(array_filter([
                $ticket['Id_usuario'],
                $ticket['Id_tecnico_asignado']
            ]));

            foreach ($destinatarios as $dest_id) {
                if ($dest_id == $usuario_id) continue;
                crearNotificacion($dest_id, $id_ticket, 'ticket_comentario', $mensaje);
            }

            if (function_exists('notificarAdministradores')) {
                $excluir = array_merge([$usuario_id], $destinatarios);
                notificarAdministradores($mensaje, 'ticket_comentario', $id_ticket, $excluir);
            }
        }
    } catch (Exception $e) {
        error_log('Notificación comentario: ' . $e->getMessage());
    }
    ob_end_clean();
    
    echo json_encode([
        'success' => true,
        'id' => $id_comentario
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>