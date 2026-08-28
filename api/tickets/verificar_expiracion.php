<?php
require_once '../config/database.php';
require_once '../config/mail.php';
require_once '../config/bitacora.php';

function verificarTicketsExpirados() {
    global $pdo;
    
    // Obtener tickets próximos a expirar
    $stmt = $pdo->prepare("SELECT t.*, u.correo as tecnico_correo, u.Nombre as tecnico_nombre
                          FROM Ticket t
                          LEFT JOIN Usuario u ON t.Id_tecnico_asignado = u.Id_usuario
                          WHERE t.Id_estado IN (1, 2, 3)
                          AND t.Fecha_vencimiento IS NOT NULL
                          AND t.Fecha_vencimiento < DATE_ADD(NOW(), INTERVAL 4 HOUR)
                          AND t.Fecha_vencimiento > NOW()");
    $stmt->execute();
    $tickets = $stmt->fetchAll();
    
    foreach ($tickets as $ticket) {
        // Notificar al técnico
        if ($ticket['tecnico_correo']) {
            notificarTicketExpirado($ticket, $ticket);
        }
        registrarBitacora('Tickets', 'TICKET_PROXIMO_EXPIRAR', 'OK', $ticket['Id_ticket'], 
                          "Ticket {$ticket['Folio']} próximo a expirar");
    }
    
    // Tickets expirados
    $stmt = $pdo->prepare("SELECT t.* 
                          FROM Ticket t
                          WHERE t.Id_estado IN (1, 2, 3)
                          AND t.Fecha_vencimiento IS NOT NULL
                          AND t.Fecha_vencimiento < NOW()");
    $stmt->execute();
    $expirados = $stmt->fetchAll();
    
    foreach ($expirados as $ticket) {
        // pool
        $stmt = $pdo->prepare("UPDATE Ticket SET Id_estado = 1, Id_tecnico_asignado = NULL 
                              WHERE Id_ticket = ?");
        $stmt->execute([$ticket['Id_ticket']]);
        $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                              (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                              VALUES (?, 'En proceso', 'Expirado - Pool', NULL)");
        $stmt->execute([$ticket['Id_ticket']]);
        
        registrarBitacora('Tickets', 'TICKET_EXPIRADO', 'OK', $ticket['Id_ticket'], 
                          "Ticket {$ticket['Folio']} expirado, vuelve a pool");
    }
    
    return ['proximos' => count($tickets), 'expirados' => count($expirados)];
}


if (php_sapi_name() === 'cli') {
    verificarTicketsExpirados();
}
?>