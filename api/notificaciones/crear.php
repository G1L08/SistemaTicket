<?php
require_once '../config/database.php';

function crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notificacion (Id_usuario, Id_ticket, Tipo, Mensaje, Leida, Fecha_creacion) 
                           VALUES (?, ?, ?, ?, 0, NOW())");
    return $stmt->execute([$usuario_id, $ticket_id, $tipo, $mensaje]);
}

function notificarUsuariosTicket($ticket_id, $mensaje, $tipo, $usuarios_excluir = []) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT Id_usuario, Id_tecnico_asignado FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$ticket) return;
    $usuarios = [];
    if ($ticket['Id_usuario']) $usuarios[] = $ticket['Id_usuario'];
    if ($ticket['Id_tecnico_asignado']) $usuarios[] = $ticket['Id_tecnico_asignado'];
    $usuarios = array_unique($usuarios);
    $usuarios = array_diff($usuarios, $usuarios_excluir);
    foreach ($usuarios as $usuario_id) {
        crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje);
    }
}

function notificarAdministradores($mensaje, $tipo, $ticket_id = null, $usuarios_excluir = []) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.Id_usuario 
                           FROM Usuario u
                           JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario
                           JOIN Rol r ON ur.Id_rol = r.Id_rol
                           WHERE r.Nombre_rol = 'Administrador'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $admins = array_diff($admins, $usuarios_excluir);
    foreach ($admins as $admin_id) {
        crearNotificacion($admin_id, $ticket_id, $tipo, $mensaje);
    }
}

function notificarTecnicos($mensaje, $tipo, $ticket_id = null, $usuarios_excluir = []) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.Id_usuario 
                           FROM Usuario u
                           JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario
                           JOIN Rol r ON ur.Id_rol = r.Id_rol
                           WHERE r.Nombre_rol = 'Técnico'");
    $stmt->execute();
    $tecnicos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tecnicos = array_diff($tecnicos, $usuarios_excluir);
    foreach ($tecnicos as $tecnico_id) {
        crearNotificacion($tecnico_id, $ticket_id, $tipo, $mensaje);
    }
}
?>