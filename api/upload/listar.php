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

$id_ticket = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_ticket) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM ArchivoAdjunto 
                          WHERE Id_ticket = ? 
                          ORDER BY Fecha_carga DESC");
    $stmt->execute([$id_ticket]);
    $archivos = $stmt->fetchAll();
    
    echo json_encode($archivos);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>