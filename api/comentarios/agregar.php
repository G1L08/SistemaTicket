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

if (!isset($data['id_ticket']) || !isset($data['contenido'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id_ticket = intval($data['id_ticket']);
$contenido = trim($data['contenido']);
$usuario_id = $_SESSION['usuario_id'];

if (empty($contenido)) {
    echo json_encode(['error' => 'El comentario no puede estar vacío']);
    exit;
}

try {
    // Verificar que el ticket exista
    $stmt = $pdo->prepare("SELECT Id_ticket FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO Comentario (Id_ticket, Id_usuario, Contenido) 
                          VALUES (?, ?, ?)");
    $stmt->execute([$id_ticket, $usuario_id, $contenido]);
    
    echo json_encode([
        'success' => true,
        'id' => $pdo->lastInsertId()
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>