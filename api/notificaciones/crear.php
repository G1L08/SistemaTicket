<?php
require_once __DIR__ . '/../config/Mailer.php';

function crearNotificacion($usuario_id, $ticket_id, $tipo, $mensaje) {
    global $pdo;

    $stmt = $pdo->prepare("INSERT INTO notificacion 
                          (Id_usuario, Id_ticket, Tipo, Mensaje, Leida, Fecha_creacion) 
                          VALUES (?, ?, ?, ?, 0, NOW())");
    $ok = $stmt->execute([$usuario_id, $ticket_id, $tipo, $mensaje]);

    try {
        $mailer = new Mailer();
        if (!$mailer->estaActivo()) return $ok;

        static $columnas = null;
        if ($columnas === null) {
            $columnas = [];
            $res = $pdo->query("SHOW COLUMNS FROM Usuario");
            foreach ($res->fetchAll(PDO::FETCH_COLUMN) as $c) {
                $columnas[] = $c;
            }
        }

        $col_nombre   = null;
        $col_apellido = null;
        $col_correo   = null;

        foreach ($columnas as $c) {
            $lc = strtolower($c);
            if (!$col_nombre   && (strpos($lc, 'nombre') !== false || $lc === 'name')) $col_nombre = $c;
            if (!$col_apellido && (strpos($lc, 'apellido') !== false || $lc === 'lastname')) $col_apellido = $c;
            if (!$col_correo   && (strpos($lc, 'correo') !== false || strpos($lc, 'email') !== false || strpos($lc, 'mail') !== false)) $col_correo = $c;
        }

        if (!$col_correo) {
            error_log("crearNotificacion: no encontré columna de correo en Usuario");
            return $ok;
        }

        $campos = [$col_correo];
        if ($col_nombre)   $campos[] = $col_nombre;
        if ($col_apellido) $campos[] = $col_apellido;

        $sql = "SELECT " . implode(', ', $campos) . " FROM Usuario WHERE Id_usuario = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario || empty($usuario[$col_correo])) {
            error_log("crearNotificacion: usuario $usuario_id sin correo");
            return $ok;
        }

        $correoDestino = $usuario[$col_correo];
        $nombreCompleto = trim(
            ($col_nombre   ? ($usuario[$col_nombre]   ?? '') : '') . ' ' .
            ($col_apellido ? ($usuario[$col_apellido] ?? '') : '')
        );
        if ($nombreCompleto === '') $nombreCompleto = 'Usuario';

        $ticket = null;
        if ($ticket_id) {
            $stmt = $pdo->prepare("
                SELECT t.Id_ticket, t.Folio, t.Titulo,
                       e.Nombre AS estado_nombre,
                       p.Nombre AS prioridad_nombre,
                       c.Nombre AS categoria_nombre
                FROM Ticket t
                LEFT JOIN Estado e     ON t.Id_estado     = e.Id_estado
                LEFT JOIN Prioridad p  ON t.Id_prioridad  = p.Id_prioridad
                LEFT JOIN Categoria c  ON t.Id_categoria  = c.Id_categoria
                WHERE t.Id_ticket = ?
            ");
            $stmt->execute([$ticket_id]);
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $asuntos = [
            'ticket_nuevo'       => 'Nuevo ticket registrado',
            'ticket_asignado'    => 'Ticket asignado',
            'ticket_tomado'      => 'Ticket tomado',
            'ticket_comentario'  => 'Nuevo comentario en tu ticket',
            'ticket_estado'      => 'Cambio de estado en tu ticket',
            'ticket_cerrado'     => 'Ticket cerrado',
            'ticket_reabierto'   => 'Ticket reabierto',
            'ticket_prioridad'   => 'Prioridad confirmada',
            'ticket_por_expirar' => 'Ticket próximo a expirar',
            'ticket_vencido'     => 'Ticket vencido'
        ];
        $asunto = $asuntos[$tipo] ?? 'Notificación';

        $datos = array_merge($ticket ?? [], [
            'mensaje'             => $mensaje,
            'destinatario_nombre' => $nombreCompleto
        ]);

        $html = $mailer->plantillaTicket($tipo, $datos);
        $resultado = $mailer->enviar($correoDestino, $asunto, $html, $mensaje, [
            'folio' => $ticket['Folio'] ?? null,
            'tipo'  => $tipo
        ]);

        if (!$resultado['success']) {
            error_log("crearNotificacion: error al enviar a {$correoDestino} — {$resultado['error']}");
        }

    } catch (Exception $e) {
        error_log('crearNotificacion: excepción — ' . $e->getMessage());
    }

    return $ok;
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
                           WHERE r.Nombre_rol = 'Tecnico'");
    $stmt->execute();
    $tecnicos = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $tecnicos = array_diff($tecnicos, $usuarios_excluir);
    foreach ($tecnicos as $tecnico_id) {
        crearNotificacion($tecnico_id, $ticket_id, $tipo, $mensaje);
    }
}
?>