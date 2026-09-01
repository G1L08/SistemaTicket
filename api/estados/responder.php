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

if (!isset($data['id_encuesta'])) {
    echo json_encode(['error' => 'ID de encuesta requerido']);
    exit;
}

$id_encuesta = intval($data['id_encuesta']);

try {
    $stmt = $pdo->prepare("SELECT * FROM EncuestaSatisfaccion WHERE Id_encuesta = ?");
    $stmt->execute([$id_encuesta]);
    $encuesta = $stmt->fetch();
    
    if (!$encuesta) {
        echo json_encode(['error' => 'Encuesta no encontrada']);
        exit;
    }
    
    if ($encuesta['Respondida'] == 1) {
        echo json_encode(['error' => 'Esta encuesta ya fue respondida']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT Id_usuario FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$encuesta['Id_ticket']]);
    $ticket = $stmt->fetch();
    
    if ($ticket['Id_usuario'] != $_SESSION['usuario_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para responder esta encuesta']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE EncuestaSatisfaccion 
                          SET Calificacion_respeto = ?,
                              Calificacion_tiempo = ?,
                              Calificacion_conocimiento = ?,
                              Informado = ?,
                              Calificacion_archivos = ?,
                              Calificacion_facilidad = ?,
                              Contacto_externo = ?,
                              Interacciones = ?,
                              Comentarios = ?,
                              Respondida = 1,
                              Fecha_respuesta = NOW()
                          WHERE Id_encuesta = ?");
    
    $stmt->execute([
        $data['calificacion_respeto'] ?? null,
        $data['calificacion_tiempo'] ?? null,
        $data['calificacion_conocimiento'] ?? null,
        $data['informado'] ?? null,
        $data['calificacion_archivos'] ?? null,
        $data['calificacion_facilidad'] ?? null,
        $data['contacto_externo'] ?? null,
        $data['interacciones'] ?? null,
        $data['comentarios'] ?? null,
        $id_encuesta
    ]);
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Encuesta respondida exitosamente'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>