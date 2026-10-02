<?php
require_once '../config/database.php';
require_once '../config/mail.php';
require_once '../config/bitacora.php';
require_once __DIR__ . '/../notificaciones/crear.php';

function notificacionRecienteExiste($pdo, $usuario_id, $ticket_id, $tipo, $horas = 12) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notificacion 
                          WHERE Id_usuario = ? 
                          AND Id_ticket = ? 
                          AND Tipo = ?
                          AND Fecha_creacion > DATE_SUB(NOW(), INTERVAL ? HOUR)");
    $stmt->execute([$usuario_id, $ticket_id, $tipo, $horas]);
    return $stmt->fetchColumn() > 0;
}

function notificarSeguro($usuario_id, $ticket_id, $tipo, $mensaje) {
    global $pdo;
    if (!$usuario_id) return false;
    if (notificacionRecienteExiste($pdo, $usuario_id, $ticket_id, $tipo)) return false;
    return crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje);
}

function notificarAdminsSeguro($ticket_id, $tipo, $mensaje) {
    global $pdo;
    $stmt = $pdo->query("SELECT u.Id_usuario 
                        FROM Usuario u
                        JOIN UsuarioRol ur ON u.Id_usuario = ur.Id_usuario
                        JOIN Rol r ON ur.Id_rol = r.Id_rol
                        WHERE r.Nombre_rol = 'Administrador'");
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($admins as $admin_id) {
        notificarSeguro($admin_id, $ticket_id, $tipo, $mensaje);
    }
}

function verificarTicketsExpirados() {
    global $pdo;

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
        $mensaje = "El ticket {$ticket['Folio']} - \"{$ticket['Titulo']}\" está próximo a expirar (vence: " . date('d/m/Y H:i', strtotime($ticket['Fecha_vencimiento'])) . ").";

        if ($ticket['Id_tecnico_asignado']) {
            notificarSeguro($ticket['Id_tecnico_asignado'], $ticket['Id_ticket'], 'ticket_por_expirar', $mensaje);
        }

        notificarSeguro($ticket['Id_usuario'], $ticket['Id_ticket'], 'ticket_por_expirar', 
            "Tu ticket {$ticket['Folio']} está próximo a expirar. Si no se resuelve a tiempo volverá al pool.");

        notificarAdminsSeguro($ticket['Id_ticket'], 'ticket_por_expirar', $mensaje);

        if ($ticket['tecnico_correo']) {
            notificarTicketExpirado($ticket, $ticket);
        }

        registrarBitacora('Tickets', 'TICKET_PROXIMO_EXPIRAR', 'OK', $ticket['Id_ticket'], 
                          "Ticket {$ticket['Folio']} próximo a expirar");
    }

    $stmt = $pdo->prepare("SELECT t.*, u.correo as tecnico_correo
                          FROM Ticket t
                          LEFT JOIN Usuario u ON t.Id_tecnico_asignado = u.Id_usuario
                          WHERE t.Id_estado IN (1, 2, 3)
                          AND t.Fecha_vencimiento IS NOT NULL
                          AND t.Fecha_vencimiento < NOW()");
    $stmt->execute();
    $expirados = $stmt->fetchAll();

    foreach ($expirados as $ticket) {
        $tecnico_anterior = $ticket['Id_tecnico_asignado'];

        $stmt = $pdo->prepare("UPDATE Ticket SET Id_estado = 1, Id_tecnico_asignado = NULL 
                              WHERE Id_ticket = ?");
        $stmt->execute([$ticket['Id_ticket']]);

        $stmt = $pdo->prepare("INSERT INTO HistorialTicket 
                              (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                              VALUES (?, 'En proceso', 'Expirado - Pool', NULL)");
        $stmt->execute([$ticket['Id_ticket']]);

        notificarSeguro($ticket['Id_usuario'], $ticket['Id_ticket'], 'ticket_vencido',
            "Tu ticket {$ticket['Folio']} - \"{$ticket['Titulo']}\" ha expirado y regresó al pool. Un técnico lo atenderá pronto.");

        if ($tecnico_anterior) {
            notificarSeguro($tecnico_anterior, $ticket['Id_ticket'], 'ticket_vencido',
                "El ticket {$ticket['Folio']} que tenías asignado ha expirado y regresó al pool.");
        }

        notificarAdminsSeguro($ticket['Id_ticket'], 'ticket_vencido',
            "El ticket {$ticket['Folio']} - \"{$ticket['Titulo']}\" expiró y regresó al pool.");

        if ($ticket['tecnico_correo']) {
            notificarTicketExpirado($ticket, $ticket);
        }

        registrarBitacora('Tickets', 'TICKET_EXPIRADO', 'OK', $ticket['Id_ticket'], 
                          "Ticket {$ticket['Folio']} expirado, vuelve a pool");
    }

    return ['proximos' => count($tickets), 'expirados' => count($expirados)];
}

if (php_sapi_name() === 'cli') {
    $resultado = verificarTicketsExpirados();
    echo "Próximos a expirar: {$resultado['proximos']}\n";
    echo "Expirados: {$resultado['expirados']}\n";
}
?>