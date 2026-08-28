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

if (!in_array('Administrador', $_SESSION['roles'] ?? [])) {
    http_response_code(403);
    echo json_encode(['error' => 'Permisos insuficientes']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    echo json_encode(['error' => 'ID de usuario requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT u.*, 
                           GROUP_CONCAT(r.Nombre_rol) as rol_nombre
                           FROM Usuario u
                           LEFT JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario
                           LEFT JOIN Rol r ON ur.Id_rol = r.Id_rol
                           WHERE u.Id_usuario = ?
                           GROUP BY u.Id_usuario");
    $stmt->execute([$id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    
    echo json_encode($usuario);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>