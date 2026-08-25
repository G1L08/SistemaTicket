<?php
require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['no_empleado']) || !isset($data['contraseña'])) {
    echo json_encode(['error' => 'Faltan credenciales']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT u.*, GROUP_CONCAT(r.Nombre_rol) as roles 
                           FROM Usuario u 
                           LEFT JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario 
                           LEFT JOIN Rol r ON ur.Id_rol = r.Id_rol 
                           WHERE u.No_empleado = ? 
                           GROUP BY u.Id_usuario");
    $stmt->execute([$data['no_empleado']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    



    
    if ($data['contraseña'] !== $usuario['contraseña']) {
        echo json_encode(['error' => 'Contraseña incorrecta']);
        exit;
    }
    
    session_start();
    $_SESSION['usuario_id'] = $usuario['Id_usuario'];
    $_SESSION['nombre'] = $usuario['Nombre'] . ' ' . $usuario['Apellido_paterno'];
    $_SESSION['roles'] = explode(',', $usuario['roles']);
    
    echo json_encode([
        'success' => true,
        'usuario' => [
            'id' => $usuario['Id_usuario'],
            'nombre' => $_SESSION['nombre'],
            'roles' => $_SESSION['roles']
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
