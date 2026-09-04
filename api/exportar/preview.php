<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (!in_array('Administrador', $_SESSION['roles'] ?? [])) {
    echo json_encode(['error' => 'Permisos insuficientes']);
    exit;
}

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
$estado = isset($_GET['estado']) ? intval($_GET['estado']) : null;
$prioridad = isset($_GET['prioridad']) ? intval($_GET['prioridad']) : null;
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;

try {
    $sql = "SELECT t.Id_ticket, t.Folio, t.Titulo, 
            e.Nombre as estado, 
            p.Nombre as prioridad, 
            c.Nombre as categoria,
            CONCAT(u.Nombre, ' ', u.Apellido_paterno) as solicitante,
            DATE_FORMAT(t.Fecha_creacion, '%d/%m/%Y') as fecha_creacion
            FROM Ticket t
            LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
            LEFT JOIN Prioridad p ON t.Id_prioridad = p.Id_prioridad
            LEFT JOIN Categoria c ON t.Id_categoria = c.Id_categoria
            LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
            WHERE 1=1";

    $params = [];

    if ($busqueda) {
        $sql .= " AND (t.Folio LIKE ? OR t.Titulo LIKE ?)";
        $params[] = '%' . $busqueda . '%';
        $params[] = '%' . $busqueda . '%';
    }

    if ($estado) {
        $sql .= " AND t.Id_estado = ?";
        $params[] = $estado;
    }

    if ($prioridad) {
        $sql .= " AND t.Id_prioridad = ?";
        $params[] = $prioridad;
    }

    if ($categoria) {
        $sql .= " AND t.Id_categoria = ?";
        $params[] = $categoria;
    }

    if ($fecha_desde) {
        $sql .= " AND DATE(t.Fecha_creacion) >= ?";
        $params[] = $fecha_desde;
    }

    if ($fecha_hasta) {
        $sql .= " AND DATE(t.Fecha_creacion) <= ?";
        $params[] = $fecha_hasta;
    }

    $sql .= " ORDER BY t.Fecha_creacion DESC LIMIT 50";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tickets);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>