<?php
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['titulo']) || !isset($data['descripcion']) || 
    !isset($data['id_categoria']) || !isset($data['id_prioridad'])) {
    echo json_encode(['error' => 'Faltan datos obligatorios']);
    exit;
}

try {
    $pdo->beginTransaction();

    
    $folio = generarFolio();
    $fecha_vencimiento = date('Y-m-d H:i:s', strtotime('+48 hours'));
    $stmt = $pdo->prepare("INSERT INTO Ticket 
                          (Folio, Titulo, Descripcion, Id_usuario, Id_categoria, Id_prioridad, Fecha_vencimiento) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $folio,
        $data['titulo'],
        $data['descripcion'],
        $usuario_id,
        $data['id_categoria'],
        $data['id_prioridad'],
        $fecha_vencimiento
    ]);

    $ticket_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                          VALUES (?, 'Nuevo', 'Abierto', ?)");
    $stmt->execute([$ticket_id, $usuario_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id' => $ticket_id,
        'folio' => $folio,
        'mensaje' => 'Ticket creado exitosamente'
    ]);

} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Error al crear ticket: ' . $e->getMessage()]);
}
?>