<?php
function registrarLog($evento, $descripcion, $nivel = 'INFO') {
    global $pdo;
    
    $datos = [
        'usuario_id' => $_SESSION['usuario_id'] ?? null,
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'evento' => $evento,
        'descripcion' => $descripcion,
        'nivel' => $nivel
    ];
    
    $stmt = $pdo->prepare("INSERT INTO LogSeguridad 
                          (usuario_id, ip, user_agent, evento, descripcion, nivel) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $datos['usuario_id'],
        $datos['ip'],
        $datos['user_agent'],
        $datos['evento'],
        $datos['descripcion'],
        $datos['nivel']
    ]);
}

?>