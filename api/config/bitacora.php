<?php
function registrarBitacora($modulo, $accion, $resultado = 'OK', $id_ticket = null, $detalle = null) {
    global $pdo;
    
    $usuario_id = $_SESSION['usuario_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $pdo->prepare("INSERT INTO Bitacora 
                          (Id_usuario, Modulo, Accion, Id_ticket, Resultado, Ip, User_agent, Detalle_tecnico) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$usuario_id, $modulo, $accion, $id_ticket, $resultado, $ip, $user_agent, $detalle]);
}
?>