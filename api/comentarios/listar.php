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
    $stmt = $pdo->query("SHOW TABLES LIKE 'Comentario'");
    if ($stmt->rowCount() == 0) {
        echo json_encode([]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT c.*, 
                           u.Nombre as usuario_nombre,
                           u.Apellido_paterno as usuario_apellido,
                           u.No_empleado as usuario_empleado
                           FROM Comentario c
                           LEFT JOIN Usuario u ON c.Id_usuario = u.Id_usuario
                           WHERE c.Id_ticket = ?
                           ORDER BY c.Fecha_registro ASC");
    $stmt->execute([$id]);
    $comentarios = $stmt->fetchAll();
    
    echo json_encode($comentarios);
    
} catch(PDOException $e) {
    echo json_encode([]);
}
?>