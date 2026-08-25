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

CREATE TABLE LogSeguridad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NULL,
    ip VARCHAR(45) NOT NULL,
    user_agent VARCHAR(255),
    evento VARCHAR(100) NOT NULL,
    descripcion TEXT,
    nivel ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') DEFAULT 'INFO',
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_evento (evento),
    INDEX idx_usuario (usuario_id),
    INDEX idx_timestamp (timestamp)
);
?>