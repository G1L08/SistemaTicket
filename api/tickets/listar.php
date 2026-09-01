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
<<<<<<< HEAD
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : null;
$estado = isset($_GET['estado']) ? intval($_GET['estado']) : null;
$prioridad = isset($_GET['prioridad']) ? intval($_GET['prioridad']) : null;
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

$page = isset($_GET['page']) ? intval($_GET['page']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 6;
$offset = ($page - 1) * $limit;

try {
    $sql_base = "SELECT t.*, 
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
        $sql_base .= " AND t.Id_usuario = ?";
        $params[] = $usuario_id;
    } elseif ($tipo == 'asignados') {
        $sql_base .= " AND t.Id_tecnico_asignado = ?";
        $params[] = $usuario_id;
    } elseif ($tipo == 'mis_tickets') {
        $sql_base .= " AND (t.Id_usuario = ? OR t.Id_tecnico_asignado = ?)";
        $params[] = $usuario_id;
        $params[] = $usuario_id;
    } elseif ($tipo == 'pool') {
        $sql_base .= " AND t.Id_tecnico_asignado IS NULL AND t.Id_estado = 1";
    }

    if ($estado) {
        $sql_base .= " AND t.Id_estado = ?";
        $params[] = $estado;
    }

    if ($prioridad) {
        $sql_base .= " AND t.Id_prioridad = ?";
        $params[] = $prioridad;
    }

    if ($categoria) {
        $sql_base .= " AND t.Id_categoria = ?";
        $params[] = $categoria;
    }

    // ===== SI NO HAY PAGINACIÓN → DEVOLVER ARRAY DIRECTO =====
    if ($page === null) {
        $sql = $sql_base . " ORDER BY t.Fecha_creacion DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tickets);
        exit;
    }

    // ===== CON PAGINACIÓN =====
    $sql_count = "SELECT COUNT(*) as total FROM (" . $sql_base . ") as subquery";
    $stmt_count = $pdo->prepare($sql_count);
    $stmt_count->execute($params);
    $total_result = $stmt_count->fetch(PDO::FETCH_ASSOC);
    $total = intval($total_result['total']);
    $pages = ceil($total / $limit);

    $sql = $sql_base . " ORDER BY t.Fecha_creacion DESC LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'data' => $tickets,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'pages' => $pages
    ]);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
=======
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
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
}
?>