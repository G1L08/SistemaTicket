<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';
<<<<<<< HEAD
require_once '../notificaciones/crear.php';
=======
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85

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
    echo json_encode(['error' => 'El comentario no puede estar vacío']);
    exit;
}

try {
<<<<<<< HEAD
    $stmt = $pdo->prepare("SELECT Id_ticket, Folio, Id_usuario, Id_tecnico_asignado FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
=======
    // Verificar que el ticket exista
    $stmt = $pdo->prepare("SELECT Id_ticket FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    if (!$stmt->fetch()) {
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO Comentario (Id_ticket, Id_usuario, Contenido) 
                          VALUES (?, ?, ?)");
    $stmt->execute([$id_ticket, $usuario_id, $contenido]);
    
<<<<<<< HEAD
    $nombre_usuario = $_SESSION['nombre'] ?? 'Usuario';
    $mensaje = "{$nombre_usuario} comentó en el ticket {$ticket['Folio']}: " . substr($contenido, 0, 50) . "...";

    notificarUsuariosTicket(
        $id_ticket,
        $mensaje,
        'comentario',
        [$usuario_id]
    );
    
    notificarAdministradores(
        $mensaje,
        'comentario',
        $id_ticket,
        [$usuario_id]
    );
    
=======
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId()
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>