<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (!in_array('Administrador', $_SESSION['roles'] ?? [])) {
    http_response_code(403);
    echo json_encode(['error' => 'Permisos insuficientes']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['error' => 'ID de usuario requerido']);
    exit;
}

$id = intval($data['id']);
$puesto = isset($data['puesto']) ? trim($data['puesto']) : null;
$id_rol = isset($data['id_rol']) ? intval($data['id_rol']) : null;
$estado = isset($data['estado']) ? intval($data['estado']) : null;

try {
    $stmt = $pdo->prepare("SELECT Id_usuario FROM Usuario WHERE Id_usuario = ?");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    $pdo->beginTransaction();
    
    $sql = "UPDATE Usuario SET ";
    $params = [];
    
    if ($puesto !== null) {
        $sql .= "Puesto = ?, ";
        $params[] = $puesto;
    }
    
    if ($estado !== null) {
        $sql .= "estado = ?, ";
        $params[] = $estado;
    }
    
    $sql = rtrim($sql, ', ');
    $sql .= " WHERE Id_usuario = ?";
    $params[] = $id;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    if ($id_rol !== null) {
        $stmt = $pdo->prepare("DELETE FROM UsuarioRol WHERE Id_usuario = ?");
        $stmt->execute([$id]);
        
        $stmt = $pdo->prepare("INSERT INTO UsuarioRol (Id_rol, Id_usuario) VALUES (?, ?)");
        $stmt->execute([$id_rol, $id]);
    }
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Usuario actualizado correctamente'
    ]);
    
} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
?>