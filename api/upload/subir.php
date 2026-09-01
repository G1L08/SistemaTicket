<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar que se envió un archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No se recibió ningún archivo o hubo un error']);
    exit;
}

$id_ticket = isset($_POST['id_ticket']) ? intval($_POST['id_ticket']) : 0;
if (!$id_ticket) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

// Obtener el folio del ticket
try {
    $stmt = $pdo->prepare("SELECT Folio FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    
    $folio = $ticket['Folio'];
    
    $archivo = $_FILES['archivo'];
    $nombre_original = basename($archivo['name']);
    $tipo = $archivo['type'];
    $tamanio = $archivo['size'];
    $temp_path = $archivo['tmp_name'];
    
    // tamaño de archivo
    $tamano_maximo = 5 * 1024 * 1024;
    if ($tamanio > $tamano_maximo) {
        echo json_encode([
            'error' => 'El archivo excede el tamaño máximo de 5 MB. Tamaño actual: ' . 
                      round($tamanio / 1024 / 1024, 2) . ' MB'
        ]);
        exit;
    }
    
    // formatos de archivos
    $tipos_permitidos = [
        'application/pdf',          
        'image/jpeg',               
        'image/png'                 
    ];
    
    $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($tipo, $tipos_permitidos)) {
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
        if (!in_array($extension, $extensiones_permitidas)) {
            echo json_encode([
                'error' => 'Tipo de archivo no permitido. Solo se aceptan: PDF, JPG, JPEG, PNG'
            ]);
            exit;
        }
    }
    
    // archivo valido
    if (strpos($tipo, 'image/') === 0) {
        $image_info = getimagesize($temp_path);
        if ($image_info === false) {
            echo json_encode(['error' => 'El archivo no es una imagen válida']);
            exit;
        }
    }
    
    //Verificar que es un PDF válido
    if ($tipo === 'application/pdf') {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $temp_path);
        finfo_close($finfo);
        if ($mime !== 'application/pdf') {
            echo json_encode(['error' => 'El archivo no es un PDF válido']);
            exit;
        }
    }
    
    // Folio 
    $upload_dir = __DIR__ . '/../../uploads/';
    $folio_dir = $upload_dir . $folio . '/';
    
    if (!is_dir($folio_dir)) {
        if (!mkdir($folio_dir, 0777, true)) {
            echo json_encode(['error' => 'No se pudo crear la carpeta para el folio']);
            exit;
        }
    }
    
    // Guardar Archivos
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    $nombre_guardado = uniqid() . '.' . $extension;
    $ruta_completa = $folio_dir . $nombre_guardado;
    $ruta_relativa = 'uploads/' . $folio . '/' . $nombre_guardado;
    
    if (!move_uploaded_file($temp_path, $ruta_completa)) {
        echo json_encode(['error' => 'Error al mover el archivo']);
        exit;
    }
    
    // Conexion con la base
    $stmt = $pdo->prepare("INSERT INTO ArchivoAdjunto 
                          (Id_ticket, Folio, Nombre_original, Nombre_guardado, Ruta, Tipo, Tamanio_bytes) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id_ticket,
        $folio,
        $nombre_original,
        $nombre_guardado,
        $ruta_relativa,
        $tipo,
        $tamanio
    ]);
    
    $id_archivo = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'id_archivo' => $id_archivo,
        'nombre_original' => $nombre_original,
        'ruta' => $ruta_relativa,
        'tamanio' => $tamanio,
        'mensaje' => 'Archivo subido exitosamente'
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>