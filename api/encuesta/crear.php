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

if (!isset($data['id_ticket'])) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO EncuestaSatisfaccion 
                          (Id_ticket, Calificacion_respeto, Calificacion_tiempo, 
                           Calificacion_conocimiento, Informado, Calificacion_archivos,
                           Calificacion_facilidad, Contacto_externo, Interacciones, Comentarios) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $data['id_ticket'],
        $data['calificacion_respeto'] ?? null,
        $data['calificacion_tiempo'] ?? null,
        $data['calificacion_conocimiento'] ?? null,
        $data['informado'] ?? null,
        $data['calificacion_archivos'] ?? null,
        $data['calificacion_facilidad'] ?? null,
        $data['contacto_externo'] ?? null,
        $data['interacciones'] ?? null,
        $data['comentarios'] ?? null
    ]);

    $id_encuesta = $pdo->lastInsertId();

    $stmt = $pdo->prepare("UPDATE Ticket SET Id_encuesta = ? WHERE Id_ticket = ?");
    $stmt->execute([$id_encuesta, $data['id_ticket']]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id_encuesta' => $id_encuesta,
        'mensaje' => 'Encuesta creada exitosamente'
    ]);

} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>