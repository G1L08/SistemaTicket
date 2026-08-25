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
    $stmt = $pdo->prepare("SELECT DISTINCT u.Id_usuario, u.Nombre, u.Apellido_paterno, 
                                  u.Apellido_materno, u.No_empleado, u.correo,
                                  GROUP_CONCAT(r.Nombre_rol) as roles
                           FROM Usuario u
                           LEFT JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario
                           LEFT JOIN Rol r ON ur.Id_rol = r.Id_rol
                           WHERE r.Nombre_rol IN ('Técnico', 'Administrador')
                           AND u.estado = 1
                           GROUP BY u.Id_usuario
                           ORDER BY u.Nombre");
    $stmt->execute();
    $tecnicos = $stmt->fetchAll();
    
    echo json_encode($tecnicos);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>