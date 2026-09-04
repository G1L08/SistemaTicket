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

$usuario_id = $_SESSION['usuario_id'];
$limite = isset($_GET['limite']) ? intval($_GET['limite']) : 20;

try {
    $sql = "SELECT n.*, t.Folio, t.Titulo 
            FROM notificacion n
            LEFT JOIN Ticket t ON n.Id_ticket = t.Id_ticket
            WHERE n.Id_usuario = ?
            ORDER BY n.Fecha_creacion DESC
            LIMIT " . intval($limite);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($notificaciones);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>