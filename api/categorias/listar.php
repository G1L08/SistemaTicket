<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

try {
    $stmt = $pdo->query("SELECT Id_categoria, Nombre, Descripcion 
                         FROM Categoria 
                         WHERE Activo = 1 
                         ORDER BY Nombre");
    $categorias = $stmt->fetchAll();
    echo json_encode($categorias);
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>