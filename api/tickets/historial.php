<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT h.*, 
                           u.Nombre as usuario_nombre,
                           u.Apellido_paterno as usuario_apellido
                           FROM HistorialTicket h
                           LEFT JOIN Usuario u ON h.Id_usuario = u.Id_usuario
                           WHERE h.Id_ticket = ?
                           ORDER BY h.Fecha_cambio DESC");
    $stmt->execute([$id]);
    $historial = $stmt->fetchAll();
    
    echo json_encode($historial);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>