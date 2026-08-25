<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Límite de intentos
$max_intentos = 5;
$tiempo_bloqueo = 900; // 15 minutos 
$data = json_decode(file_get_contents('php://input'), true);
$no_empleado = $data['no_empleado'] ?? '';
$contraseña = $data['contraseña'] ?? '';

try {
    $stmt = $pdo->prepare("SELECT intentos_fallidos, ultimo_intento 
                           FROM Usuario 
                           WHERE No_empleado = ?");
    $stmt->execute([$no_empleado]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        if ($usuario['intentos_fallidos'] >= $max_intentos) {
            $tiempo_restante = $tiempo_bloqueo - (time() - strtotime($usuario['ultimo_intento']));
            if ($tiempo_restante > 0) {
                echo json_encode([
                    'error' => "Usuario bloqueado. Intente en " . ceil($tiempo_restante/60) . " minutos"
                ]);
                exit;
            } else {
                // reinicio de intentos
                $pdo->prepare("UPDATE Usuario SET intentos_fallidos = 0 WHERE No_empleado = ?")
                    ->execute([$no_empleado]);
            }
        }
    }
    
    // validacion de credenciales
    $stmt = $pdo->prepare("SELECT u.*, GROUP_CONCAT(r.Nombre_rol) as roles 
                           FROM Usuario u 
                           LEFT JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario 
                           LEFT JOIN Rol r ON ur.Id_rol = r.Id_rol 
                           WHERE u.No_empleado = ? 
                           GROUP BY u.Id_usuario");
    $stmt->execute([$no_empleado]);
    $usuario = $stmt->fetch();
    
    if (!$usuario || !password_verify($contraseña, $usuario['contraseña'])) {
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
    iniciarSesionSegura(
        $usuario['Id_usuario'],
        $usuario['Nombre'] . ' ' . $usuario['Apellido_paterno'],
        explode(',', $usuario['roles'] ?? 'Usuario')
    );
    
    echo json_encode([
        'success' => true,
        'usuario' => [
            'id' => $usuario['Id_usuario'],
            'nombre' => $_SESSION['nombre'],
            'roles' => $_SESSION['roles']
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error en el servidor']);
    error_log($e->getMessage());
}
?>