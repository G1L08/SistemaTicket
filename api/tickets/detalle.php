<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$roles = $_SESSION['roles'] ?? ['Usuario'];
if (is_string($roles)) {
    $decoded = json_decode($roles, true);
    $roles = is_array($decoded) ? $decoded : [$roles];
}
if (!is_array($roles)) {
    $roles = ['Usuario'];
}

try {
    $stmt = $pdo->prepare("SELECT t.*, 
                           COALESCE(u.Nombre, 'Usuario desconocido') as usuario_nombre, 
                           COALESCE(u.Apellido_paterno, '') as usuario_apellido,
                           COALESCE(u.correo, 'No disponible') as usuario_correo,
                           COALESCE(u.No_empleado, 'No disponible') as usuario_empleado,
                           e.Nombre as estado_nombre,
                           p.Nombre as prioridad_nombre,
                           p.Horas_resolucion as prioridad_horas,
                           c.Nombre as categoria_nombre,
                           tec.Nombre as tecnico_nombre,
                           tec.Apellido_paterno as tecnico_apellido,
                           tec.correo as tecnico_correo
                           FROM Ticket t
                           LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
                           LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
                           LEFT JOIN Prioridad p ON t.Id_prioridad = p.Id_prioridad
                           LEFT JOIN Categoria c ON t.Id_categoria = c.Id_categoria
                           LEFT JOIN Usuario tec ON t.Id_tecnico_asignado = tec.Id_usuario
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id]);
    $ticket = $stmt->fetch();
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }

    $esAdmin           = in_array('Administrador', $roles);
    $esTecnico         = in_array('Tecnico', $roles);
    $esSolicitante     = ($ticket['Id_usuario'] == $usuario_id);
    $esTecnicoAsignado = ($ticket['Id_tecnico_asignado'] == $usuario_id);

    $puedeVer = $esAdmin
             || $esSolicitante
             || $esTecnicoAsignado
             || ($esTecnico && $ticket['Id_tecnico_asignado'] === null);

    if (!$puedeVer) {
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para ver este ticket']);
        exit;
    }

    echo json_encode($ticket);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}