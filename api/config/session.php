<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); 
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.use_only_cookies', 1);


function iniciarSesionSegura($usuario_id, $nombre, $roles) {
    session_regenerate_id(true); 
    
    $_SESSION['usuario_id'] = $usuario_id;
    $_SESSION['nombre'] = $nombre;
    $_SESSION['roles'] = $roles;
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['ultima_actividad'] = time();
}


function verificarSesionSegura() {
    session_start();
    
    if (!isset($_SESSION['usuario_id'])) {
        return false;
    }
    
    //User Agent
    if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR'] || 
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        return false;
    }
    
    // Verificar inactividad (30 minutos)
    if (time() - $_SESSION['ultima_actividad'] > 1800) {
        session_destroy();
        return false;
    }
    
    $_SESSION['ultima_actividad'] = time();
    return true;
}
?>