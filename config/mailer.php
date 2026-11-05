<?php
/**
 * SERVICIO DE ENVÍO DE CORREOS PARA NUEVOS USUARIOS ADMINISTRATIVOS
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Carga modular de PHPMailer (misma estructura de tu proyecto)
$phpmailerPath = __DIR__ . '/../libs/PHPMailer/src/';
if (file_exists($phpmailerPath . 'PHPMailer.php')) {
    require_once $phpmailerPath . 'Exception.php';
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
}

function enviarCorreoNotificacion($emailDestino, $nombreUsuario, $usuarioAcceso, $passwordPlana, $rolAsignado) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;

    $mail = new PHPMailer(true);

    try {
        // SERVIDOR SMTP GMAIL (Usa las mismas credenciales de tu archivo de inscripciones)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tecnologianoracastaneda@gmail.com'; 
        $mail->Password   = 'niqxrnncvjyxbavn';              
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Opciones SSL para entornos locales/desarrollo
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        // Remitente y Destinatario
        $mail->setFrom('tecnologianoracastaneda@gmail.com', 'Escuela Nora Castañeda');
        $mail->addAddress($emailDestino, $nombreUsuario);

        // Contenido en HTML
        $mail->isHTML(true);
        $mail->Subject = 'Bienvenido/a al Sistema Administrador - Escuela Nora Castañeda';

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; background-color: #fdf8f3;'>
            <div style='background-color: #521f29; padding: 20px; text-align: center;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 18px;'>ESCUELA INTERNACIONAL DE LIDERAZGO DE LA MUJER</h2>
                <h3 style='color: #e5b871; margin: 5px 0 0 0; font-size: 16px;'>\"NORA CASTAÑEDA\"</h3>
            </div>
            <div style='padding: 25px; color: #333333;'>
                <h3 style='color: #521f29; margin-top: 0;'>¡Bienvenido/a, {$nombreUsuario}!</h3>
                <p>Se ha creado exitosamente tu cuenta de acceso al Panel Administrativo.</p>
                
                <div style='background-color: #ffffff; border-left: 4px solid #e5b871; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <h4 style='color: #521f29; margin-top: 0;'>Tus Credenciales de Acceso:</h4>
                    <p style='margin: 4px 0;'><strong>Usuario:</strong> {$usuarioAcceso}</p>
                    <p style='margin: 4px 0;'><strong>Contraseña Inicial:</strong> <code style='background: #f4f4f4; padding: 2px 6px; border-radius: 4px;'>{$passwordPlana}</code></p>
                    <p style='margin: 4px 0;'><strong>Rol Asignado:</strong> {$rolAsignado}</p>
                </div>

                <p style='font-size: 13px; color: #666;'>Por razones de seguridad, te recomendamos iniciar sesión y cambiar tu contraseña en tu primer ingreso.</p>
                
                <div style='text-align: center; margin-top: 25px;'>
                    <a href='http://localhost/sistema-nora/admin/login.php' style='display: inline-block; padding: 12px 25px; color: #ffffff; background-color: #521f29; text-decoration: none; border-radius: 25px; font-weight: bold;'>Acceder al Panel Admin</a>
                </div>
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de usuario: " . $mail->ErrorInfo);
        return false;
    }
}