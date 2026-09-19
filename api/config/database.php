<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$host = getenv('TICKET_DB_HOST') ?: '127.0.0.1';
$dbname = getenv('TICKET_DB_NAME') ?: 'ticket_system_dar';
$username = getenv('TICKET_DB_USER') ?: 'ticket_app';
$password = getenv('TICKET_DB_PASSWORD');

if ($password === false || $password === '') {
    http_response_code(500);
    echo json_encode([
        'error' => 'Configuración de base de datos incompleta.'
    ]);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log('SistemaTicket - Error BD: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error' => 'Error de conexión a la base de datos.'
    ]);
    exit;
}

function generarFolio() {
    return 'TKT-' . date('Y') . '-' .
        str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
}

function verificarSesion() {
    session_start();

    if (!isset($_SESSION['usuario_id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'No autorizado']);
        exit;
    }

    return $_SESSION['usuario_id'];
}
?>