<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<pre style='font-family: monospace; padding: 20px;'>";
echo "🔍 DIAGNÓSTICO\n\n";
echo "1. Directorio actual: " . __DIR__ . "\n";
echo "2. Existe Mailer.php? " . (file_exists(__DIR__ . '/Mailer.php') ? '✅ SÍ' : '❌ NO') . "\n";
echo "3. Existe mail.php?   " . (file_exists(__DIR__ . '/mail.php')   ? '✅ SÍ' : '❌ NO') . "\n\n";

require_once __DIR__ . '/Mailer.php';
echo "4. Mailer.php cargado ✅\n";
echo "5. ¿Existe la clase Mailer? " . (class_exists('Mailer') ? '✅ SÍ' : '❌ NO') . "\n";

if (!class_exists('Mailer')) {
    echo "\n❌ La clase Mailer no se declaró. Revisa que el archivo Mailer.php no tenga errores de sintaxis.\n";
    echo "</pre>";
    exit;
}

$mailer = new Mailer();
echo "6. Mailer instanciado ✅\n";
echo "7. ¿Correo activo? " . ($mailer->estaActivo() ? '✅ SÍ' : '⚠️ NO (activo=false en mail.php)') . "\n";

// Verificar PHPMailer
$autoload = __DIR__ . '/../../vendor/autoload.php';
$manual   = __DIR__ . '/PHPMailer/PHPMailer.php';
echo "8. vendor/autoload.php? " . (file_exists($autoload) ? '✅ SÍ' : '❌ NO') . "\n";
echo "9. api/config/PHPMailer/PHPMailer.php? " . (file_exists($manual) ? '✅ SÍ' : '❌ NO') . "\n";
echo "10. ¿Clase PHPMailer disponible? " . (class_exists('PHPMailer\PHPMailer\PHPMailer') ? '✅ SÍ' : '❌ NO') . "\n\n";

if (!$mailer->estaActivo()) {
    echo "⚠️ El correo está desactivado en mail.php (activo = false).\nCambia a true para probar.\n";
    echo "</pre>";
    exit;
}

$datos = [
    'Id_ticket'           => 999,
    'Folio'               => 'TKT-2026-09-18-0042',
    'Titulo'              => 'Prueba de correo desde el sistema',
    'estado_nombre'       => 'Abierto',
    'prioridad_nombre'    => 'Alta',
    'categoria_nombre'    => 'Software',
    'mensaje'             => 'Este es un correo de prueba generado desde test-mail.php. Si lo recibes, la configuración SMTP funciona correctamente.',
    'destinatario_nombre' => 'Administrador'
];

$html = $mailer->plantillaTicket('ticket_nuevo', $datos);
echo "11. Plantilla HTML generada ✅ (" . strlen($html) . " bytes)\n";
echo "12. Enviando a TU_CORREO...\n\n";


$destino = 'ra399194@uaeh.edu.mx';

$resultado = $mailer->enviar($destino, 'Prueba - Sistema de Tickets', $html);

if ($resultado['success']) {
    echo "✅ CORREO ENVIADO EXITOSAMENTE A: {$destino}\n\nRevisa tu bandeja de entrada (y la carpeta de spam).";
} else {
    echo "❌ ERROR AL ENVIAR:\n\n" . htmlspecialchars($resultado['error']);
}

echo "</pre>";