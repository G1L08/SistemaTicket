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
    $stmt = $pdo->prepare("SELECT t.Id_usuario, t.Folio, t.Titulo,
                                  e.*,
                                  DATEDIFF(NOW(), COALESCE(e.Fecha_envio, NOW())) >= 15 AS expirada
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
        http_response_code(403);
        echo json_encode(['error' => 'No tienes permiso para ver esta encuesta']);
        exit;
    }

    if (empty($data['Id_encuesta'])) {
        echo json_encode(['error' => 'Este ticket no tiene encuesta asociada']);
        exit;
    }

    // Cerrar automáticamente si expiró y no ha sido respondida
    if (!empty($data['expirada']) && $data['Respondida'] == 0) {
        $stmt = $pdo->prepare("UPDATE EncuestaSatisfaccion 
                               SET Respondida = 1,
                                   Fecha_respuesta = NOW(),
                                   Comentarios = CONCAT(COALESCE(Comentarios, ''), ' (Encuesta cerrada automáticamente por tiempo límite)')
                               WHERE Id_encuesta = ?");
        $stmt->execute([$data['Id_encuesta']]);

        $data['Respondida'] = 1;
        $data['Fecha_respuesta'] = date('Y-m-d H:i:s');
        $data['Comentarios'] = ($data['Comentarios'] ?? '') . ' (Encuesta cerrada automáticamente por tiempo límite)';
    }

    echo json_encode($data);

} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>