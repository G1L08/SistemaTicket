<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['no_empleado']) || !isset($data['contraseña'])) {
    echo json_encode(['error' => 'Faltan credenciales']);
    exit;
}

$no_empleado = trim($data['no_empleado']);
$contraseña = $data['contraseña'];

try {
    $stmt = $pdo->prepare("SELECT u.*, GROUP_CONCAT(r.Nombre_rol) as roles 
                           FROM Usuario u 
                           LEFT JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario 
                           LEFT JOIN Rol r ON ur.Id_rol = r.Id_rol 
                           WHERE u.No_empleado = ? 
                           GROUP BY u.Id_usuario");
    $stmt->execute([$no_empleado]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit;
    }

    if ($usuario['estado'] == 0) {
        echo json_encode([
            'error' => 'usuario_inactivo',
            'mensaje' => 'Su cuenta está inactiva. Por favor, comuníquese con el departamento de TI.'
        ]);
        exit;
    }
    
    if ($data['contraseña'] !== $usuario['contraseña']) {
        $stmt = $pdo->prepare("UPDATE Usuario 
                               SET intentos_fallidos = intentos_fallidos + 1,
                                   ultimo_intento = NOW() 
                               WHERE No_empleado = ?");
        $stmt->execute([$no_empleado]);
        
        echo json_encode(['error' => 'Credenciales inválidas']);
        exit;
    }
    
    
    $pdo->prepare("UPDATE Usuario SET intentos_fallidos = 0 WHERE No_empleado = ?")
        ->execute([$no_empleado]);
    
    session_start();
    $_SESSION['usuario_id'] = $usuario['Id_usuario'];
    $_SESSION['nombre'] = $usuario['Nombre'] . ' ' . $usuario['Apellido_paterno'];
    $_SESSION['roles'] = $usuario['roles'] ? explode(',', $usuario['roles']) : ['Usuario'];
    
    echo json_encode([
        'success' => true,
        'usuario' => [
            'id' => $usuario['Id_usuario'],
            'nombre' => $_SESSION['nombre'],
            'roles' => $_SESSION['roles']
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>