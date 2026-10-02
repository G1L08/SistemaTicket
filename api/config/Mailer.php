<?php

class Mailer {
    private $config;
    private $phpmailerListo = false;

    public function __construct() {
        $this->config = require __DIR__ . '/mail.php';
    }

    public function estaActivo() {
        return !empty($this->config['activo']);
    }

    private function cargarPHPMailer() {
        if ($this->phpmailerListo) return true;

        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $this->phpmailerListo = true;
            return true;
        }

        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $manual = __DIR__ . '/PHPMailer/';
            if (file_exists($manual . 'PHPMailer.php')) {
                require_once $manual . 'Exception.php';
                require_once $manual . 'PHPMailer.php';
                require_once $manual . 'SMTP.php';
            }
        }

        $this->phpmailerListo = class_exists('PHPMailer\PHPMailer\PHPMailer');
        return $this->phpmailerListo;
    }

    /**
     * Envía un correo.
     * 
     * @param string $destinatario
     * @param string $asunto
     * @param string $cuerpoHTML
     * @param string $cuerpoTexto
     * @param array  $opciones  ['folio' => 'TKT-...', 'tipo' => 'ticket_nuevo', 'id_ticket' => 123]
     */
    public function enviar($destinatario, $asunto, $cuerpoHTML, $cuerpoTexto = '', $opciones = []) {
        if (!$this->estaActivo()) {
            return ['success' => false, 'error' => 'Correo desactivado en config'];
        }
        if (!$this->cargarPHPMailer()) {
            return ['success' => false, 'error' => 'PHPMailer no está instalado'];
        }
        if (empty($destinatario) || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Correo destino inválido: ' . $destinatario];
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = $this->config['secure'];
            $mail->Port       = $this->config['port'];
            $mail->CharSet    = 'UTF-8';
            $mail->SMTPDebug  = $this->config['debug'] ?? 0;
            $mail->Timeout    = $this->config['timeout'] ?? 15;

            $mail->setFrom($this->config['from'], $this->config['from_name']);
            $mail->addAddress($destinatario);

            // ============================================
            // THREADING: subject + headers RFC
            // ============================================
            $folio = $opciones['folio'] ?? null;
            $tipo  = $opciones['tipo']  ?? null;

            if ($folio) {
                $dominio = $this->extraerDominio($this->config['from']);
                $rootMessageId = '<ticket-' . $folio . '-root@' . $dominio . '>';

                // Subject con [FOLIO] — todos los correos del ticket lo comparten
                $mail->Subject = '[Sistema de Tickets] ' . $folio;

                if ($tipo === 'ticket_nuevo') {
                    // PRIMER correo del hilo: define el Message-ID raíz.
                    // Aquí NO ponemos In-Reply-To ni References porque es el origen.
                    $mail->MessageID = $rootMessageId;
                } else {
                    // Los demás correos del ticket: apuntan al root.
                    $uniqueId = time() . '-' . bin2hex(random_bytes(4));
                    $mail->MessageID = '<ticket-' . $folio . '-' . $uniqueId . '@' . $dominio . '>';
                    $mail->addCustomHeader('In-Reply-To', $rootMessageId);
                    $mail->addCustomHeader('References', $rootMessageId);
                }
            } else {
                $mail->Subject = $asunto;
            }

            $mail->isHTML(true);
            $mail->Body    = $cuerpoHTML;
            $mail->AltBody = $cuerpoTexto ?: strip_tags($cuerpoHTML);

            $mail->send();
            return ['success' => true];
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            return ['success' => false, 'error' => $mail->ErrorInfo];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function extraerDominio($correo) {
        $partes = explode('@', $correo);
        return $partes[1] ?? 'localhost';
    }

    // =========================================================
    // PLANTILLA HTML PARA TICKETS
    // =========================================================
    public function plantillaTicket($tipo, $datos) {
        $urlBase   = rtrim($this->config['base_url'] ?? 'http://localhost/ticket-system', '/');
        $urlTicket = $urlBase . '/detalle-ticket.html?id=' . intval($datos['Id_ticket'] ?? 0);

        $folio     = htmlspecialchars($datos['Folio'] ?? '');
        $titulo    = htmlspecialchars($datos['Titulo'] ?? '');
        $estado    = htmlspecialchars($datos['estado_nombre'] ?? '');
        $prioridad = htmlspecialchars($datos['prioridad_nombre'] ?? '');
        $categoria = htmlspecialchars($datos['categoria_nombre'] ?? '');
        $mensaje   = nl2br(htmlspecialchars($datos['mensaje'] ?? ''));
        $destino   = htmlspecialchars($datos['destinatario_nombre'] ?? 'Usuario');

        $colores = [
            'ticket_nuevo'       => ['#0d6efd', 'Nuevo ticket registrado'],
            'ticket_asignado'    => ['#b8860b', 'Ticket asignado'],
            'ticket_tomado'      => ['#b8860b', 'Ticket tomado'],
            'ticket_comentario'  => ['#6f42c1', 'Nuevo comentario'],
            'ticket_archivo'     => ['#6f42c1', 'Nuevo archivo adjunto'],
            'ticket_estado'      => ['#0d6efd', 'Cambio de estado'],
            'ticket_cerrado'     => ['#28a745', 'Ticket cerrado'],
            'ticket_reabierto'   => ['#fd7e14', 'Ticket reabierto'],
            'ticket_prioridad'   => ['#0d6efd', 'Prioridad confirmada'],
            'ticket_por_expirar' => ['#ffc107', 'Ticket próximo a expirar'],
            'ticket_vencido'     => ['#dc3545', 'Ticket vencido']
        ];
        $color      = $colores[$tipo][0] ?? '#861f1b';
        $tituloTipo = $colores[$tipo][1] ?? 'Notificación';

        return "
        <!DOCTYPE html>
        <html lang='es'>
        <head><meta charset='UTF-8'></head>
        <body style='margin:0;padding:0;background:#f4f4f4;font-family:Segoe UI,Arial,sans-serif;'>
          <table role='presentation' width='100%' cellspacing='0' cellpadding='0' style='background:#f4f4f4;padding:30px 0;'>
            <tr><td align='center'>
              <table role='presentation' width='600' cellspacing='0' cellpadding='0' style='background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);'>
                <tr><td style='background:#861f1b;padding:22px 30px;color:#fff;'>
                  <h1 style='margin:0;font-size:20px;font-weight:600;'>Sistema de Tickets - DAR TI</h1>
                </td></tr>
                <tr><td style='padding:30px;'>
                  <h2 style='margin:0 0 8px 0;color:{$color};font-size:20px;'>{$tituloTipo}</h2>
                  <p style='margin:0 0 20px 0;color:#6c757d;font-size:14px;'>Hola <strong>{$destino}</strong>,</p>

                  <div style='background:#f8f9fa;border-left:4px solid {$color};padding:16px 18px;border-radius:8px;margin-bottom:20px;'>
                    <div style='font-size:13px;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;'>Folio</div>
                    <div style='font-size:18px;color:#212529;font-weight:700;margin-bottom:10px;'>{$folio}</div>
                    <div style='font-size:13px;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;'>Título</div>
                    <div style='font-size:15px;color:#212529;margin-bottom:10px;'>{$titulo}</div>
                    <div style='font-size:14px;color:#495057;margin-bottom:10px;'>{$mensaje}</div>
                    <table width='100%' style='font-size:13px;color:#495057;margin-top:8px;'>
                      " . ($estado    ? "<tr><td style='padding:2px 0;'><strong>Estado:</strong> {$estado}</td></tr>" : "") . "
                      " . ($prioridad ? "<tr><td style='padding:2px 0;'><strong>Prioridad:</strong> {$prioridad}</td></tr>" : "") . "
                      " . ($categoria ? "<tr><td style='padding:2px 0;'><strong>Categoría:</strong> {$categoria}</td></tr>" : "") . "
                    </table>
                  </div>

                  <div style='text-align:center;margin:25px 0;'>
                    <a href='{$urlTicket}' style='display:inline-block;background:#861f1b;color:#fff;text-decoration:none;padding:12px 30px;border-radius:8px;font-weight:600;font-size:15px;'>Ver ticket</a>
                  </div>

                  <p style='margin:20px 0 0 0;color:#6c757d;font-size:12px;text-align:center;'>
                    Este es un correo automático del Sistema de Tickets. No respondas a este mensaje.
                  </p>
                </td></tr>
                <tr><td style='background:#f8f9fa;padding:15px 30px;text-align:center;color:#6c757d;font-size:12px;'>
                  DAR TI &mdash; Departamento de Tecnología
                </td></tr>
              </table>
            </td></tr>
          </table>
        </body>
        </html>";
    }
}