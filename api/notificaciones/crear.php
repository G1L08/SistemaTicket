<?php
require_once '../config/database.php';

function crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje) {
    global $pdo;
    
    $stmt = $pdo->prepare("INSERT INTO Notificacion (Id_usuario, Id_ticket, Tipo, Mensaje) 
                          VALUES (?, ?, ?, ?)");
    return $stmt->execute([$usuario_id, $ticket_id, $tipo, $mensaje]);
}

function notificarUsuariosTicket($ticket_id, $mensaje, $tipo, $usuarios_excluir = []) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT Id_usuario FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$ticket_id]);
    $ticket = $stmt->fetch();
    $stmt = $pdo->prepare("SELECT Id_tecnico_asignado FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$ticket_id]);
    $tecnico = $stmt->fetch();
    
    $usuarios = [$ticket['Id_usuario']];
    if ($tecnico['Id_tecnico_asignado']) {
        $usuarios[] = $tecnico['Id_tecnico_asignado'];
    }
    
    foreach ($usuarios as $usuario_id) {
        if (!in_array($usuario_id, $usuarios_excluir)) {
            crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje);
        }
    }
}
?>