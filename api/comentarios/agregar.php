<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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
    $stmt = $pdo->prepare("SELECT t.Id_ticket, t.Folio, t.Id_usuario, t.Id_tecnico_asignado, u.Nombre as usuario_nombre
                           FROM Ticket t
                           LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO Comentario (Id_ticket, Id_usuario, Contenido) VALUES (?, ?, ?)");
    $stmt->execute([$id_ticket, $usuario_id, $contenido]);
    
    $id_comentario = $pdo->lastInsertId();
    
    $mensaje = "Nuevo comentario en el ticket {$ticket['Folio']}: " . substr($contenido, 0, 50) . "...";
    
    notificarUsuariosTicket($id_ticket, $mensaje, 'comentario', [$usuario_id]);
    
    echo json_encode([
        'success' => true,
        'id' => $id_comentario
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>