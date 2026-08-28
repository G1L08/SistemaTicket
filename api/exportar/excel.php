<?php
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=tickets_' . date('Y-m-d') . '.csv');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    die('No autorizado');
}

if (!in_array('Administrador', $_SESSION['roles'] ?? [])) {
    die('Permisos insuficientes');
}

try {
    //entrada de datos
    $stmt = $pdo->query("SELECT t.Folio, t.Titulo, 
                         e.Nombre as estado, 
                         p.Nombre as prioridad, 
                         c.Nombre as categoria,
                         CONCAT(u.Nombre, ' ', u.Apellido_paterno) as solicitante,
                         CONCAT(tec.Nombre, ' ', tec.Apellido_paterno) as tecnico,
                         DATE_FORMAT(t.Fecha_creacion, '%d/%m/%Y %H:%i') as fecha_creacion,
                         DATE_FORMAT(t.Fecha_vencimiento, '%d/%m/%Y %H:%i') as fecha_vencimiento,
                         t.Descripcion_solucion as solucion
                         FROM Ticket t
                         LEFT JOIN Estado e ON t.Id_estado = e.Id_estado
                         LEFT JOIN Prioridad p ON t.Id_prioridad = p.Id_prioridad
                         LEFT JOIN Categoria c ON t.Id_categoria = c.Id_categoria
                         LEFT JOIN Usuario u ON t.Id_usuario = u.Id_usuario
                         LEFT JOIN Usuario tec ON t.Id_tecnico_asignado = tec.Id_usuario
                         ORDER BY t.Fecha_creacion DESC");

    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = fopen('php://output', 'w');
    fputcsv($output, [//salida de datos
        'Folio',
        'Titulo',
        'Estado',
        'Prioridad',
        'Categoria',
        'Solicitante',
        'Tecnico',
        'Fecha Creacion',
        'Fecha Vencimiento',
        'Solucion'
    ]);

    foreach ($tickets as $ticket) {
        fputcsv($output, $ticket);
    }

    fclose($output);
    exit;

} catch(PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>