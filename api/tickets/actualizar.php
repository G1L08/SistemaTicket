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

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['estado'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$id = intval($data['id']);
$nuevo_estado = intval($data['estado']);
$usuario_id = $_SESSION['usuario_id'];
<<<<<<< HEAD
=======

>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
$descripcion_solucion = isset($data['descripcion_solucion']) ? trim($data['descripcion_solucion']) : null;

try {
    $stmt = $pdo->prepare("SELECT Id_usuario, Id_tecnico_asignado, Id_estado FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id]);
    $ticket = $stmt->fetch();
<<<<<<< HEAD

=======
    
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
<<<<<<< HEAD

    $usuario_es_tecnico = ($ticket['Id_tecnico_asignado'] == $usuario_id);
    $usuario_es_admin = in_array('Administrador', $_SESSION['roles'] ?? []);

=======
    
    $usuario_es_tecnico = ($ticket['Id_tecnico_asignado'] == $usuario_id);
    $usuario_es_admin = in_array('Administrador', $_SESSION['roles'] ?? []);
    
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
    if (!$usuario_es_admin && !$usuario_es_tecnico) {
        echo json_encode(['error' => 'No tienes permiso para cambiar el estado de este ticket']);
        exit;
    }
<<<<<<< HEAD

    $stmt = $pdo->prepare("SELECT Nombre FROM Estado WHERE Id_estado = ?");
    $stmt->execute([$ticket['Id_estado']]);
    $estado_anterior_nombre = $stmt->fetchColumn() ?: 'Desconocido';

    $stmt = $pdo->prepare("SELECT Nombre FROM Estado WHERE Id_estado = ?");
    $stmt->execute([$nuevo_estado]);
    $estado_nuevo_nombre = $stmt->fetchColumn() ?: 'Desconocido';

    if ($nuevo_estado == 4 && empty($descripcion_solucion)) {
        echo json_encode(['error' => 'Debes registrar una solucion para cerrar el ticket']);
        exit;
    }

    $pdo->beginTransaction();

=======
    
    $stmt = $pdo->prepare("SELECT Nombre FROM Estado WHERE Id_estado = ?");
    $stmt->execute([$ticket['Id_estado']]);
    $estado_anterior_nombre = $stmt->fetchColumn() ?: 'Desconocido';
    
    $stmt = $pdo->prepare("SELECT Nombre FROM Estado WHERE Id_estado = ?");
    $stmt->execute([$nuevo_estado]);
    $estado_nuevo_nombre = $stmt->fetchColumn() ?: 'Desconocido';
    
    if ($nuevo_estado == 4 && empty($descripcion_solucion)) {
        echo json_encode(['error' => 'Debes registrar una solución para cerrar el ticket']);
        exit;
    }
    
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
    if ($nuevo_estado == 4) {
        $stmt = $pdo->prepare("UPDATE Ticket SET Id_estado = ?, Descripcion_solucion = ?, Fecha_actualizacion = NOW() WHERE Id_ticket = ?");
        $stmt->execute([$nuevo_estado, $descripcion_solucion, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE Ticket SET Id_estado = ?, Fecha_actualizacion = NOW() WHERE Id_ticket = ?");
        $stmt->execute([$nuevo_estado, $id]);
    }
<<<<<<< HEAD

    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario, Fecha_cambio) VALUES (?, ?, ?, ?, NOW())");
=======
    
    $stmt = $pdo->prepare("INSERT INTO HistorialTicket (Id_ticket, Estado_anterior, Estado_nuevo, Id_usuario, Fecha_cambio) 
                          VALUES (?, ?, ?, ?, NOW())");
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
    $stmt->execute([$id, $estado_anterior_nombre, $estado_nuevo_nombre, $usuario_id]);

    if ($nuevo_estado == 4) {
        $stmt = $pdo->prepare("SELECT Id_encuesta FROM Ticket WHERE Id_ticket = ?");
        $stmt->execute([$id]);
        $ticket_data = $stmt->fetch();
<<<<<<< HEAD

        if (!$ticket_data['Id_encuesta']) {
            $stmt = $pdo->prepare("INSERT INTO EncuestaSatisfaccion (Id_ticket, Fecha_envio, Fecha_cierre) VALUES (?, NOW(), DATE_ADD(NOW(), INTERVAL 15 DAY))");
            $stmt->execute([$id]);
            $id_encuesta = $pdo->lastInsertId();

=======
        
        if (!$ticket_data['Id_encuesta']) {
            $stmt = $pdo->prepare("INSERT INTO EncuestaSatisfaccion (Id_ticket) VALUES (?)");
            $stmt->execute([$id]);
            $id_encuesta = $pdo->lastInsertId();
            
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
            $stmt = $pdo->prepare("UPDATE Ticket SET Id_encuesta = ? WHERE Id_ticket = ?");
            $stmt->execute([$id_encuesta, $id]);
        }
    }
<<<<<<< HEAD

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'mensaje' => 'Estado actualizado correctamente'
    ]);

} catch(PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
=======
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Estado actualizado correctamente',
        'estado_anterior' => $estado_anterior_nombre,
        'estado_nuevo' => $estado_nuevo_nombre
    ]);
    
} catch(PDOException $e) {
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>