<?php
ini_set('display_errors', 0);
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

$usuario_id = $_SESSION['usuario_id'];

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No se recibió ningún archivo o hubo un error']);
    exit;
}

$id_ticket = isset($_POST['id_ticket']) ? intval($_POST['id_ticket']) : 0;
if (!$id_ticket) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT t.Folio, t.Titulo, t.Id_usuario, t.Id_tecnico_asignado
        FROM Ticket t
        WHERE t.Id_ticket = ?
    ");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
    
    $tamano_maximo = 5 * 1024 * 1024;
    if ($tamanio > $tamano_maximo) {
        echo json_encode([
            'error' => 'El archivo excede el tamaño máximo de 5 MB. Tamaño actual: ' . 
                      round($tamanio / 1024 / 1024, 2) . ' MB'
        ]);
        exit;
    }
    
    $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
    $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
    
    if (!in_array($extension, $extensiones_permitidas)) {
        echo json_encode([
            'error' => 'Tipo de archivo no permitido. Solo se aceptan: PDF, JPG, JPEG, PNG, ZIP, RAR'
        ]);
        exit;
    }
    
    if (strpos($tipo, 'image/') === 0) {
        $image_info = @getimagesize($temp_path);
        if ($image_info === false) {
            echo json_encode(['error' => 'El archivo no es una imagen válida']);
            exit;
        }
    }
    
    $upload_dir = __DIR__ . '/../../../tikets/';
    $folio_dir = $upload_dir . $folio . '/';
    
    if (!is_dir($folio_dir)) {
        if (!@mkdir($folio_dir, 0777, true)) {
            echo json_encode(['error' => 'No se pudo crear la carpeta para el ticket']);
            exit;
        }
    }
    
    $nombre_guardado = uniqid() . '.' . $extension;
    $ruta_completa = $folio_dir . $nombre_guardado;
    $ruta_relativa = 'tikets/' . $folio . '/' . $nombre_guardado;
    
    if (!@move_uploaded_file($temp_path, $ruta_completa)) {
        echo json_encode(['error' => 'Error al mover el archivo']);
        exit;
    }
    
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

    ob_start();
    try {
        $notif_path = __DIR__ . '/../notificaciones/crear.php';
        if (file_exists($notif_path)) {
            require_once $notif_path;

            if (function_exists('crearNotificacion')) {
                
                $stmtU = $pdo->prepare("SELECT Nombre, Apellido_paterno FROM Usuario WHERE Id_usuario = ?");
                $stmtU->execute([$usuario_id]);
                $u = $stmtU->fetch(PDO::FETCH_ASSOC);
                $nombreQuienSube = trim(($u['Nombre'] ?? '') . ' ' . ($u['Apellido_paterno'] ?? ''));
                if ($nombreQuienSube === '') $nombreQuienSube = 'Un usuario';

                $msg = "{$nombreQuienSube} subió un archivo al ticket {$folio} - \"{$ticket['Titulo']}\": {$nombre_original}";

                
                $destinatarios = array_unique(array_filter([
                    $ticket['Id_usuario'],
                    $ticket['Id_tecnico_asignado']
                ]));

                foreach ($destinatarios as $dest_id) {
                    if ($dest_id == $usuario_id) continue; 
                    crearNotificacion($dest_id, $id_ticket, 'ticket_archivo', $msg);
                }

                if (function_exists('notificarAdministradores')) {
                    notificarAdministradores($msg, 'ticket_archivo', $id_ticket, array_merge([$usuario_id], $destinatarios));
                }
            }
        }
    } catch (Exception $e) {
        error_log('Notificación archivo: ' . $e->getMessage());
    }
    ob_end_clean();
    
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