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

$id_ticket = isset($_GET['id_ticket']) ? intval($_GET['id_ticket']) : 0;

if (!$id_ticket) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT e.* FROM EncuestaSatisfaccion e
                          INNER JOIN Ticket t ON t.Id_encuesta = e.Id_encuesta
                          WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $encuesta = $stmt->fetch();
    
    if (!$encuesta) {
        echo json_encode(['error' => 'Encuesta no encontrada']);
        exit;
    }
    
    echo json_encode($encuesta);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>