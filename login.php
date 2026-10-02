<?php
header('Content-Type: application/json');

require_once 'api/config/database.php';

function obtenerIPReal() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } elseif (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$data = json_decode(file_get_contents('php://input'), true);
$noEmpleado = $data['noEmpleado'] ?? '';
$password = $data['password'] ?? '';

if (empty($noEmpleado) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Por favor, llena todos los campos']);
    exit;
}

$ip = obtenerIPReal();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido';

try {
    $stmt = $pdo->prepare("SELECT Id_usuario, Nombre, contraseña, intentos_fallidos, ultimo_intento FROM usuario WHERE No_empleado = ?");
    $stmt->execute([$noEmpleado]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        $sqlLog = "INSERT INTO logseguridad (usuario_id, ip, user_agent, evento, descripcion, nivel, timestamp) 
                   VALUES (NULL, ?, ?, 'LOGIN_FALLIDO', 'Intento de login con usuario inexistente: $noEmpleado', 'WARNING', NOW())";
        $pdo->prepare($sqlLog)->execute([$ip, $user_agent]);
        
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
        exit;
    }

    $usuario_id = $usuario['Id_usuario'];
    $intentos = $usuario['intentos_fallidos'];
    $ultimo_intento = $usuario['ultimo_intento'];

    if ($intentos >= 5 && $ultimo_intento) {
        $tiempo_ultimo_intento = strtotime($ultimo_intento);
        $tiempo_actual = time();
        $diferencia = $tiempo_actual - $tiempo_ultimo_intento;

        if ($diferencia < 60) {
            $segundos_restantes = 60 - $diferencia;
            
            $descripcion = "Cuenta bloqueada temporalmente. Intento de acceso con $segundos_restantes segundos restantes de bloqueo.";
            $sqlLog = "INSERT INTO logseguridad (usuario_id, ip, user_agent, evento, descripcion, nivel, timestamp) 
                       VALUES (?, ?, ?, 'BLOQUEO_TEMPORAL', ?, 'WARNING', NOW())";
            $pdo->prepare($sqlLog)->execute([$usuario_id, $ip, $user_agent, $descripcion]);

            echo json_encode([
                'success' => false, 
                'message' => "Demasiados intentos fallidos. Por favor, espera $segundos_restantes segundos antes de intentarlo de nuevo."
            ]);
            exit;
        } else {
            $intentos = 0;
            $pdo->prepare("UPDATE usuario SET intentos_fallidos = 0 WHERE Id_usuario = ?")->execute([$usuario_id]);
        }
    }

    if ($password === $usuario['contraseña']) {
        $pdo->prepare("UPDATE usuario SET intentos_fallidos = 0, ultimo_intento = NOW() WHERE Id_usuario = ?")->execute([$usuario_id]);

        $descripcion = 'El usuario ' . $usuario['Nombre'] . ' (' . $noEmpleado . ') inició sesión correctamente.';
        $sqlLog = "INSERT INTO logseguridad (usuario_id, ip, user_agent, evento, descripcion, nivel, timestamp) 
                   VALUES (?, ?, ?, 'LOGIN_EXITOSO', ?, 'INFO', NOW())";
        $pdo->prepare($sqlLog)->execute([$usuario_id, $ip, $user_agent, $descripcion]);

        session_start();
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['nombre'] = $usuario['Nombre'];

        echo json_encode(['success' => true, 'message' => 'Login exitoso']);
        
    } else {
        $nuevos_intentos = $intentos + 1;
        
        $pdo->prepare("UPDATE usuario SET intentos_fallidos = ?, ultimo_intento = NOW() WHERE Id_usuario = ?")->execute([$nuevos_intentos, $usuario_id]);

        if ($nuevos_intentos >= 5) {
            $mensaje = "Has superado el límite de 5 intentos. La cuenta ha sido bloqueada por 1 minuto.";
            $nivel = 'ERROR';
        } else {
            $intentos_restantes = 5 - $nuevos_intentos;
            $mensaje = "Contraseña incorrecta. Te quedan $intentos_restantes intentos.";
            $nivel = 'WARNING';
        }

        $descripcion = "Intento fallido #$nuevos_intentos para el empleado: $noEmpleado. $mensaje";
        $sqlLog = "INSERT INTO logseguridad (usuario_id, ip, user_agent, evento, descripcion, nivel, timestamp) 
                   VALUES (?, ?, ?, 'LOGIN_FALLIDO', ?, ?, NOW())";
        $pdo->prepare($sqlLog)->execute([$usuario_id, $ip, $user_agent, $descripcion, $nivel]);

        echo json_encode(['success' => false, 'message' => $mensaje]);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}
?>