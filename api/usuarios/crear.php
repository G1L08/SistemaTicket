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

// Validar campos requeridos
$campos = ['no_empleado', 'nombre', 'apellido_paterno', 'correo', 'contraseña', 'id_rol'];
foreach ($campos as $campo) {
    if (!isset($data[$campo]) || empty($data[$campo])) {
        echo json_encode(['error' => "El campo $campo es requerido"]);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    // Insertar usuario
    $stmt = $pdo->prepare("INSERT INTO Usuario 
                          (No_empleado, Nombre, Apellido_paterno, Apellido_materno, correo, contraseña, Puesto, estado) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['no_empleado'],
        $data['nombre'],
        $data['apellido_paterno'],
        $data['apellido_materno'] ?? '',
        $data['correo'],
        $data['contraseña'],
        $data['puesto'] ?? '',
        $data['estado'] ?? 1
    ]);

    $usuario_id = $pdo->lastInsertId();

    // Asignar rol
    $stmt = $pdo->prepare("INSERT INTO UsuarioRol (Id_rol, Id_usuario) VALUES (?, ?)");
    $stmt->execute([$data['id_rol'], $usuario_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'id' => $usuario_id,
        'mensaje' => 'Usuario creado exitosamente'
    ]);

} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>