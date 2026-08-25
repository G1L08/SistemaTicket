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

$id_ticket = intval($data['id_ticket']);

try {
    $stmt = $pdo->prepare("SELECT Id_ticket, Folio FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT Id_encuesta FROM Ticket WHERE Id_ticket = ? AND Id_encuesta IS NOT NULL");
    $stmt->execute([$id_ticket]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Este ticket ya tiene una encuesta asociada']);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO EncuestaSatisfaccion (Id_ticket) VALUES (?)");
    $stmt->execute([$id_ticket]);
    $id_encuesta = $pdo->lastInsertId();
    $stmt = $pdo->prepare("UPDATE Ticket SET Id_encuesta = ? WHERE Id_ticket = ?");
    $stmt->execute([$id_encuesta, $id_ticket]);
    
    echo json_encode([
        'success' => true,
        'id_encuesta' => $id_encuesta,
        'mensaje' => 'Encuesta creada exitosamente'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>