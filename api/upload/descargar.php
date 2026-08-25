<?php
require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    die('No autorizado');
}

$id_archivo = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_archivo) {
    die('ID de archivo requerido');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM ArchivoAdjunto WHERE Id_archivo = ?");
    $stmt->execute([$id_archivo]);
    $archivo = $stmt->fetch();
    
    if (!$archivo) {
        die('Archivo no encontrado');
    }
    
    $ruta_completa = __DIR__ . '/../../' . $archivo['Ruta'];
    
    if (!file_exists($ruta_completa)) {
        die('El archivo físico no existe');
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $archivo['Nombre_original'] . '"');
    header('Content-Length: ' . filesize($ruta_completa));
    header('Cache-Control: private, max-age=0, must-revalidate');
    
    readfile($ruta_completa);
    
} catch(PDOException $e) {
    die('Error: ' . $e->getMessage());
}
?>