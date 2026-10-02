<?php

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=tickets_' . date('Y-m-d') . '.csv');
header('Access-Control-Allow-Origin: *');

echo "\xEF\xBB\xBF";

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    die('No autorizado');
}

if (!in_array('Administrador', $_SESSION['roles'] ?? [])) {
    die('Permisos insuficientes');
}

$ids_seleccionados = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : null;
$estado = isset($_GET['estado']) ? intval($_GET['estado']) : null;
$prioridad = isset($_GET['prioridad']) ? intval($_GET['prioridad']) : null;
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
$fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;
$mes = isset($_GET['mes']) ? $_GET['mes'] : null;

try {
    $sql = "SELECT t.Folio, 
                   t.Titulo, 
                   e.Nombre as estado, 
                   p.Nombre as prioridad, 
                   c.Nombre as categoria,
                   CONCAT(u.Nombre, ' ', u.Apellido_paterno) as solicitante,
                   CONCAT(tec.Nombre, ' ', tec.Apellido_paterno) as tecnico,
                   DATE_FORMAT(t.Fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
                   DATE_FORMAT(t.Fecha_vencimiento, '%d/%m/%Y %H:%i') as fecha_vencimiento,
                   t.Descripcion_solucion as solucion,
                   t.Prioridad_confirmada as prioridad_confirmada,
                   (SELECT DATE_FORMAT(MAX(Fecha_cambio), '%d/%m/%Y %H:%i') 
                    FROM historialticket h 
                    WHERE h.Id_ticket = t.Id_ticket AND h.Estado_nuevo = 'Cerrado') as fecha_solucion,
                   IF(t.Tiempo_extendido > 0, 'Sí', 'No') as tiempo_extendido
            FROM Ticket t
            LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
            LEFT JOIN Prioridad p ON t.Id_prioridad = p.Id_prioridad
            LEFT JOIN Categoria c ON t.Id_categoria = c.Id_categoria
            LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
            LEFT JOIN Usuario tec ON t.Id_tecnico_asignado = tec.Id_usuario
            WHERE 1=1";

    $params = [];

    if (!empty($ids_seleccionados)) {
        $placeholders = implode(',', array_fill(0, count($ids_seleccionados), '?'));
        $sql .= " AND t.Id_ticket IN ($placeholders)";
        $params = array_merge($params, $ids_seleccionados);
    } else {
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

        if ($mes) {
            $sql .= " AND DATE_FORMAT(t.Fecha_creacion, '%Y-%m') = ?";
            $params[] = $mes;
        }
    }

    $sql .= " ORDER BY t.Fecha_creacion DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = fopen('php://output', 'w');

    fputcsv($output, [
        'Folio',
        'Titulo',
        'Estado',
        'Prioridad',
        'Categoria',
        'Solicitante',
        'Tecnico',
        'Fecha Creacion',
        'Fecha Vencimiento',
        'Solucion',
        'Prioridad Confirmada',
        'Fecha Solucion',
        '¿Tiempo extendido?'
    ]);

    foreach ($tickets as $ticket) {
        fputcsv($output, $ticket);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>