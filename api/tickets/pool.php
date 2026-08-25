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

try {
    $prioridad = isset($_GET['prioridad']) ? intval($_GET['prioridad']) : null;
    $categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
    
    $sql = "SELECT t.*, 
            u.Nombre as usuario_nombre, 
            u.Apellido_paterno as usuario_apellido,
            u.No_empleado as usuario_empleado,
            e.Nombre as estado_nombre,
            p.Nombre as prioridad_nombre,
            c.Nombre as categoria_nombre
            FROM Ticket t
            LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
            LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
            LEFT JOIN Prioridad p ON t.Id_prioridad = p.Id_prioridad
            LEFT JOIN Categoria c ON t.Id_categoria = c.Id_categoria
            WHERE t.Id_tecnico_asignado IS NULL 
            AND t.Id_estado = 1"; 
    
    $params = [];
    
    if ($prioridad) {
        $sql .= " AND t.Id_prioridad = ?";
        $params[] = $prioridad;
    }
    
    if ($categoria) {
        $sql .= " AND t.Id_categoria = ?";
        $params[] = $categoria;
    }
    
    $sql .= " ORDER BY 
              CASE 
                  WHEN p.Nombre = 'Crítica' THEN 1
                  WHEN p.Nombre = 'Alta' THEN 2
                  WHEN p.Nombre = 'Media' THEN 3
                  WHEN p.Nombre = 'Baja' THEN 4
                  ELSE 5
              END ASC,
              t.Fecha_creacion ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll();
    
    echo json_encode($tickets);
    
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>