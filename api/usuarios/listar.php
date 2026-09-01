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

try {
    $stmt = $pdo->query("SELECT u.*, 
                          GROUP_CONCAT(r.Nombre_rol) as rol_nombre
                         FROM Usuario u
                         LEFT JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario
                         LEFT JOIN Rol r ON ur.Id_rol = r.Id_rol
                         GROUP BY u.Id_usuario
                         ORDER BY u.Id_usuario");
    $usuarios = $stmt->fetchAll();
    echo json_encode($usuarios);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>