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
$id_archivo = isset($data['id_archivo']) ? intval($data['id_archivo']) : 0;

if (!$id_archivo) {
    echo json_encode(['error' => 'ID de archivo requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM ArchivoAdjunto WHERE Id_archivo = ?");
    $stmt->execute([$id_archivo]);
    $archivo = $stmt->fetch();
    
    if (!$archivo) {
        echo json_encode(['error' => 'Archivo no encontrado']);
        exit;
    }
    $stmt = $pdo->prepare("SELECT Id_usuario FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$archivo['Id_ticket']]);
    $ticket = $stmt->fetch();
    
    $usuario_id = $_SESSION['usuario_id'];
    $roles = $_SESSION['roles'] ?? ['Usuario'];
    
    if (!in_array('Administrador', $roles) && $ticket['Id_usuario'] != $usuario_id) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para eliminar este archivo']);
        exit;
    }
    
    // Eliminar archivo físico
    $ruta_completa = __DIR__ . '/../../' . $archivo['Ruta'];
    if (file_exists($ruta_completa)) {
        unlink($ruta_completa);
    }
    $stmt = $pdo->prepare("DELETE FROM ArchivoAdjunto WHERE Id_archivo = ?");
    $stmt->execute([$id_archivo]);
    
    echo json_encode(['success' => true, 'mensaje' => 'Archivo eliminado']);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>