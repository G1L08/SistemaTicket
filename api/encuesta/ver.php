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

$id_ticket = isset($_GET['id_ticket']) ? intval($_GET['id_ticket']) : 0;

if (!$id_ticket) {
    echo json_encode(['error' => 'ID de ticket requerido']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

try {
<<<<<<< HEAD
    $stmt = $pdo->prepare("SELECT t.Id_usuario, t.Folio, t.Titulo, 
                           e.Id_encuesta, e.Respondida, e.Fecha_respuesta,
                           COALESCE(e.Fecha_envio, NOW()) as Fecha_envio,
                           DATEDIFF(NOW(), COALESCE(e.Fecha_envio, NOW())) >= 15 as expirada
                           FROM Ticket t
                           LEFT JOIN EncuestaSatisfaccion e ON t.Id_encuesta = e.Id_encuesta
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }

    if ($data['Id_usuario'] != $usuario_id && !in_array('Administrador', $_SESSION['roles'] ?? [])) {
=======
    // Verificar que el ticket existe y obtener el solicitante
    $stmt = $pdo->prepare("SELECT Id_usuario FROM Ticket WHERE Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        echo json_encode(['error' => 'Ticket no encontrado']);
        exit;
    }
    if ($ticket['Id_usuario'] != $usuario_id && !in_array('Administrador', $_SESSION['roles'] ?? [])) {
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para ver esta encuesta']);
        exit;
    }
<<<<<<< HEAD

    if (!$data['Id_encuesta']) {
        echo json_encode(['error' => 'Este ticket no tiene encuesta asociada']);
        exit;
    }

    echo json_encode($data);

=======
    
    $stmt = $pdo->prepare("SELECT e.*, 
                           t.Folio, t.Titulo,
                           DATEDIFF(NOW(), e.Fecha_envio) as dias_transcurridos,
                           IF(DATEDIFF(NOW(), e.Fecha_envio) >= 15, 1, 0) as expirada
                           FROM EncuestaSatisfaccion e
                           INNER JOIN Ticket t ON t.Id_encuesta = e.Id_encuesta
                           WHERE t.Id_ticket = ?");
    $stmt->execute([$id_ticket]);
    $encuesta = $stmt->fetch();
    
    if (!$encuesta) {
        echo json_encode(['error' => 'Encuesta no encontrada']);
        exit;
    }
    
    // responder encuesta si no lo hacen
    if ($encuesta['expirada'] && $encuesta['Respondida'] == 0) {
        $stmt = $pdo->prepare("UPDATE EncuestaSatisfaccion 
                              SET Respondida = 1, 
                                  Fecha_respuesta = NOW(),
                                  Comentarios = CONCAT(Comentarios, ' (Encuesta cerrada automáticamente por tiempo límite)')
                              WHERE Id_encuesta = ?");
        $stmt->execute([$encuesta['Id_encuesta']]);
        $encuesta['Respondida'] = 1;
        $encuesta['Comentarios'] .= ' (Encuesta cerrada automáticamente por tiempo límite)';
    }
    
    echo json_encode($encuesta);
    
>>>>>>> 736f6aadba33521ce6ddde9feb0616d51817ec85
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>