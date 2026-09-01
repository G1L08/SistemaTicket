<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT Id_prioridad, Nombre, Descripcion, Horas_resolucion 
                         FROM Prioridad 
                         WHERE Activo = 1 
                         ORDER BY Horas_resolucion ASC");
    $prioridades = $stmt->fetchAll();
    echo json_encode($prioridades);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>