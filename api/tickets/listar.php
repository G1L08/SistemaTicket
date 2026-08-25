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

$usuario_id = $_SESSION['usuario_id'];
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'creados';
$estado = isset($_GET['estado']) ? intval($_GET['estado']) : null;
$prioridad = isset($_GET['prioridad']) ? intval($_GET['prioridad']) : null;

try {
    $sql = "SELECT t.*, 
            u.Nombre as usuario_nombre, 
            u.Apellido_paterno as usuario_apellido,
            u.No_empleado as usuario_empleado,
            e.Nombre as estado_nombre,
            p.Nombre as prioridad_nombre,
            c.Nombre as categoria_nombre,
            tec.Nombre as tecnico_nombre,
            tec.Apellido_paterno as tecnico_apellido
            FROM Ticket t
            LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
            LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
            LEFT JOIN Prioridad p ON t.Id_prioridad = p.Id_prioridad
            LEFT JOIN Categoria c ON t.Id_categoria = c.Id_categoria
            LEFT JOIN Usuario tec ON t.Id_tecnico_asignado = tec.Id_usuario
            WHERE 1=1";
    
    $params = [];
    
    if ($tipo == 'creados') {
        $sql .= " AND t.Id_usuario = ?";
        $params[] = $usuario_id;
    } elseif ($tipo == 'asignados') {
        $sql .= " AND t.Id_tecnico_asignado = ?";
        $params[] = $usuario_id;
    } elseif ($tipo == 'mis_tickets') {
        $sql .= " AND (t.Id_usuario = ? OR t.Id_tecnico_asignado = ?)";
        $params[] = $usuario_id;
        $params[] = $usuario_id;
    } elseif ($tipo == 'todos') {
        if (!in_array('Administrador', $_SESSION['roles'] ?? [])) {
            $sql .= " AND t.Id_usuario = ?";
            $params[] = $usuario_id;
        }
    }
    
    if ($estado) {
        $sql .= " AND t.Id_estado = ?";
        $params[] = $estado;
    }
    
    if ($prioridad) {
        $sql .= " AND t.Id_prioridad = ?";
        $params[] = $prioridad;
    }
    
    $sql .= " ORDER BY t.Fecha_creacion DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();
    
    echo json_encode($tickets);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>