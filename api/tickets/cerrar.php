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

if (!isset($data['id_ticket']) || !isset($data['descripcion_solucion'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

try {
    $pdo->beginTransaction();

    $id_ticket = intval($data['id_ticket']);
    $descripcion = trim($data['descripcion_solucion']);
    if (empty($descripcion)) {
        echo json_encode(['error' => 'Debes registrar una solución para cerrar el ticket']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE Ticket 
                          SET Id_estado = 4, 
                              Descripcion_solucion = ?,
                              Fecha_actualizacion = NOW()
                          WHERE Id_ticket = ?");
    $stmt->execute([$descripcion, $id_ticket]);
    $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                          (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                          VALUES (?, 'En proceso', 'Cerrado', ?)");
    $stmt->execute([$id_ticket, $_SESSION['usuario_id']]);
    $stmt = $pdo->prepare("INSERT INTO EncuestaSatisfaccion (Id_ticket) VALUES (?)");
    $stmt->execute([$id_ticket]);
    $id_encuesta = $pdo->lastInsertId();

    // Asociar encuesta al ticket
    $stmt = $pdo->prepare("UPDATE Ticket SET Id_encuesta = ? WHERE Id_ticket = ?");
    $stmt->execute([$id_encuesta, $id_ticket]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id_encuesta' => $id_encuesta,
        'mensaje' => 'Ticket cerrado exitosamente. Encuesta enviada.'
    ]);

} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>