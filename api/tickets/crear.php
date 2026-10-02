<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        while (ob_get_level() > 0) { ob_end_clean(); }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'error' => 'Fatal: ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']
        ]);
    }
});

require_once '../config/database.php';

session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 1;
$id_prioridad = isset($_POST['id_prioridad']) ? intval($_POST['id_prioridad']) : 3;

if (empty($titulo) || empty($descripcion)) {
    echo json_encode(['error' => 'Titulo y descripcion son obligatorios']);
    exit;
}

if (!function_exists('generarFolioTKT')) {
    function generarFolioTKT($pdo) {
        $fecha = date('Y-m-d');
        $intentos = 0;
        do {
            $numero = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $folio = 'TKT-' . $fecha . '-' . $numero;
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM Ticket WHERE Folio = ?");
            $stmt->execute([$folio]);
            $existe = $stmt->fetchColumn() > 0;
            $intentos++;
        } while ($existe && $intentos < 20);
        return $folio;
    }
}

if (!function_exists('validarArchivoTKT')) {
    function validarArchivoTKT($archivo) {
        $nombre_original = basename($archivo['name']);
        $tipo = $archivo['type'];
        $tamanio = $archivo['size'];
        $temp_path = $archivo['tmp_name'];

        if ($tamanio > 5 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'El archivo ' . $nombre_original . ' excede el tamaño máximo de 5 MB'];
        }

        $extensiones_permitidas = ['pdf', 'jpg', 'jpeg', 'png', 'zip', 'rar'];
        $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

        if (!in_array($extension, $extensiones_permitidas)) {
            return ['ok' => false, 'error' => 'El archivo ' . $nombre_original . ' no tiene un formato permitido (PDF, JPG, JPEG, PNG, ZIP, RAR)'];
        }

        if (strpos($tipo, 'image/') === 0) {
            $image_info = @getimagesize($temp_path);
            if ($image_info === false) {
                return ['ok' => false, 'error' => 'El archivo ' . $nombre_original . ' no es una imagen válida'];
            }
        }

        return ['ok' => true];
    }
}

try {
    $pdo->beginTransaction();

    $folio = generarFolioTKT($pdo);
    $fecha_vencimiento = date('Y-m-d H:i:s', strtotime('+48 hours'));

    $stmt = $pdo->prepare("INSERT INTO Ticket 
                          (Folio, Titulo, Descripcion, Id_usuario, Id_categoria, Id_prioridad, Fecha_vencimiento) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$folio, $titulo, $descripcion, $usuario_id, $id_categoria, $id_prioridad, $fecha_vencimiento]);
    $ticket_id = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario) 
                          VALUES (?, 'Nuevo', 'Abierto', ?)");
    $stmt->execute([$ticket_id, $usuario_id]);

    $archivos_subidos = [];
    $errores_archivos = [];

    if (isset($_FILES['archivos']) && is_array($_FILES['archivos']['name'])) {
        $upload_dir = __DIR__ . '/../../../tikets/';
        $folio_dir = $upload_dir . $folio . '/';

        $carpeta_ok = true;
        if (!is_dir($folio_dir)) {
            if (!@mkdir($folio_dir, 0777, true)) {
                $carpeta_ok = false;
                $errores_archivos[] = 'No se pudo crear la carpeta: ' . $folio_dir;
            }
        }

        if ($carpeta_ok) {
            $total = count($_FILES['archivos']['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['archivos']['error'][$i] !== UPLOAD_ERR_OK) {
                    $errores_archivos[] = 'Error al subir ' . $_FILES['archivos']['name'][$i] . ' (código ' . $_FILES['archivos']['error'][$i] . ')';
                    continue;
                }

                $archivo = [
                    'name' => $_FILES['archivos']['name'][$i],
                    'type' => $_FILES['archivos']['type'][$i],
                    'size' => $_FILES['archivos']['size'][$i],
                    'tmp_name' => $_FILES['archivos']['tmp_name'][$i],
                ];

                $validacion = validarArchivoTKT($archivo);
                if (!$validacion['ok']) {
                    $errores_archivos[] = $validacion['error'];
                    continue;
                }

                $nombre_original = basename($archivo['name']);
                $extension = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));
                $nombre_guardado = uniqid() . '.' . $extension;
                $ruta_completa = $folio_dir . $nombre_guardado;
                $ruta_relativa = 'tikets/' . $folio . '/' . $nombre_guardado;

                if (!@move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
                    $errores_archivos[] = 'Error al mover el archivo ' . $nombre_original;
                    continue;
                }

                $stmt = $pdo->prepare("INSERT INTO ArchivoAdjunto 
                                      (Id_ticket, Folio, Nombre_original, Nombre_guardado, Ruta, Tipo, Tamanio_bytes) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $ticket_id,
                    $folio,
                    $nombre_original,
                    $nombre_guardado,
                    $ruta_relativa,
                    $archivo['type'],
                    $archivo['size']
                ]);

                $archivos_subidos[] = [
                    'id_archivo' => $pdo->lastInsertId(),
                    'nombre_original' => $nombre_original,
                    'ruta' => $ruta_relativa,
                    'tamanio' => $archivo['size']
                ];
            }
        }
    }

    $pdo->commit();

    ob_start();
    try {
        $notificaciones_path = __DIR__ . '/../notificaciones/crear.php';
        if (file_exists($notificaciones_path)) {
            require_once $notificaciones_path;

            if (function_exists('crearNotificacion')) {
                $mensajeCreador = "Tu ticket {$folio} - \"{$titulo}\" ha sido registrado correctamente. Te notificaremos cuando un técnico lo tome.";
                crearNotificacion($usuario_id, $ticket_id, 'ticket_nuevo', $mensajeCreador);
            }

            if (function_exists('notificarAdministradores')) {
                $mensaje = "Nuevo ticket: {$folio} - {$titulo}";
                notificarAdministradores($mensaje, 'ticket_nuevo', $ticket_id, [$usuario_id]);
            }

            if (function_exists('notificarTecnicos')) {
                $mensaje = "Nuevo ticket: {$folio} - {$titulo}";
                notificarTecnicos($mensaje, 'ticket_nuevo', $ticket_id, [$usuario_id]);
            }
        }
    } catch (Exception $e) {
        error_log('Notificaciones: ' . $e->getMessage());
    }
    ob_end_clean(); // Descarta cualquier output accidental

    echo json_encode([
        'success' => true,
        'id' => $ticket_id,
        'folio' => $folio,
        'archivos' => $archivos_subidos,
        'errores_archivos' => $errores_archivos,
        'mensaje' => 'Ticket creado exitosamente'
    ]);

} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ob_end_clean();
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    ob_end_clean();
    echo json_encode(['error' => 'Error general: ' . $e->getMessage()]);
}
?>