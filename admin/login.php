<?php
/**
 * ==============================================================================
 * SISTEMA DE ACCESO Y RECUPERACIÓN POR TOKEN DE 15 MINUTOS (PHPMailer SMTP)
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// CARGA MODULAR DE PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$phpmailerPath = __DIR__ . '/../libs/PHPMailer/src/';
if (file_exists($phpmailerPath . 'PHPMailer.php')) {
    require_once $phpmailerPath . 'Exception.php';
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
}

$mensajeError = '';
$mensajeExito = '';
$pasoRecuperacion = $_GET['paso'] ?? 'login';

// ------------------------------------------------------------------------------
// 1. PROCESO DE INICIO DE SESIÓN
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_login'])) {
    $usuarioInput = trim($_POST['usuario'] ?? '');
    $passwordInput = $_POST['password'] ?? '';

    if (!empty($usuarioInput) && !empty($passwordInput)) {
        // Permite loguearse usando Nombre de Usuario O Cédula O Correo
        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE (usuario = :u OR cedula = :u OR email = :u) AND activo = true LIMIT 1");
        $stmt->execute(['u' => $usuarioInput]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($passwordInput, $user['password_hash'])) {
            $_SESSION['admin_logged'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_cedula'] = $user['cedula'];
            $_SESSION['admin_nombre'] = $user['nombre'];
            $_SESSION['admin_usuario'] = $user['usuario'];
            $_SESSION['admin_rol'] = $user['rol'];

            header("Location: dashboard.php");
            exit();
        } else {
            $mensajeError = 'Credenciales de acceso incorrectas o usuario inactivo.';
        }
    } else {
        $mensajeError = 'Por favor complete todos los campos.';
    }
}

// ------------------------------------------------------------------------------
// 2. ENVIAR CÓDIGO DE RECUPERACIÓN VÍA PHPMAILER CON LOGO EMBEBIDO (Paso 1)
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_solicitar_codigo'])) {
    $cedulaInput = trim($_POST['cedula'] ?? '');

    if (!empty($cedulaInput)) {
        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE cedula = :c AND activo = true LIMIT 1");
        $stmt->execute(['c' => $cedulaInput]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['email'])) {
            // Generar código numérico de 6 dígitos
            $codigoPlano = str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
            $tokenHash = password_hash($codigoPlano, PASSWORD_BCRYPT);
            
            // Expiración a los 15 minutos desde el momento actual
            $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $update = $pdo->prepare("UPDATE usuarios_admin SET reset_token_hash = :t, reset_token_expira = :e WHERE id = :id");
            $update->execute(['t' => $tokenHash, 'e' => $expiracion, 'id' => $user['id']]);

            // ENVÍO DE CORREO USANDO PHPMAILER SMTP
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'tecnologianoracastaneda@gmail.com'; 
                    $mail->Password   = 'niqxrnncvjyxbavn';              
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->CharSet    = 'UTF-8';

                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                            'allow_self_signed' => true
                        )
                    );

                    $mail->setFrom('tecnologianoracastaneda@gmail.com', 'Escuela Nora Castañeda');
                    $mail->addAddress($user['email'], $user['nombre']);

                    // Adjuntar Logo de forma embebida vía CID
                    $logoPath = __DIR__ . '/../public/assets/img/LogoEscuelaNora_NUEVO.png';
                    if (file_exists($logoPath)) {
                        $mail->addEmbeddedImage($logoPath, 'logo_escuela');
                    }

                    $mail->isHTML(true);
                    $mail->Subject = 'Código de Recuperación de Contraseña - Escuela Nora Castañeda';
                    $mail->Body    = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; background-color: #fdf8f3;'>
                        <div style='background-color: #521f29; padding: 25px 20px; text-align: center;'>
                            <img src='cid:logo_escuela' alt='Logo Escuela Nora Castañeda' style='width: 75px; height: 75px; border-radius: 50%; border: 2px solid #e5b871; margin-bottom: 10px;'>
                            <h2 style='color: #ffffff; margin: 0; font-size: 17px; text-transform: uppercase;'>Escuela Internacional de Liderazgo de la Mujer</h2>
                            <h3 style='color: #e5b871; margin: 5px 0 0 0; font-size: 15px;'>\"NORA CASTAÑEDA\"</h3>
                        </div>
                        <div style='padding: 25px; color: #333333;'>
                            <h3 style='color: #521f29; margin-top: 0;'>Hola, {$user['nombre']}</h3>
                            <p>Has solicitado restablecer tu contraseña de acceso al Panel Administrativo.</p>
                            
                            <div style='background-color: #ffffff; border-left: 4px solid #e5b871; padding: 20px; margin: 20px 0; border-radius: 6px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05);'>
                                <span style='font-size: 13px; color: #666; font-weight: bold;'>Tu código de verificación de 6 dígitos es:</span>
                                <div style='font-size: 32px; font-weight: bold; color: #521f29; letter-spacing: 6px; margin-top: 10px;'>{$codigoPlano}</div>
                            </div>

                            <p style='font-size: 13px; color: #d9534f;'><strong>Nota de seguridad:</strong> Este código expira automáticamente en 15 minutos.</p>
                            <p style='font-size: 12px; color: #777;'>Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.</p>
                        </div>
                    </div>";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Error enviando token: " . $mail->ErrorInfo);
                }
            }

            header("Location: login.php?paso=validar&cedula=" . urlencode($cedulaInput) . "&msg=enviado");
            exit();
        } else {
            header("Location: login.php?paso=validar&cedula=" . urlencode($cedulaInput) . "&msg=enviado");
            exit();
        }
    } else {
        $mensajeError = 'Ingrese su número de cédula.';
    }
}

// ------------------------------------------------------------------------------
// 3. CAMBIAR CONTRASEÑA CON EL CÓDIGO (Paso 2)
// ------------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_cambiar_clave'])) {
    $cedulaInput = trim($_POST['cedula'] ?? '');
    $codigoInput = trim($_POST['codigo'] ?? '');
    $newPass = $_POST['nueva_password'] ?? '';
    $confirmPass = $_POST['confirmar_password'] ?? '';

    if (empty($cedulaInput) || empty($codigoInput) || empty($newPass) || empty($confirmPass)) {
        $mensajeError = 'Todos los campos son obligatorios.';
    } elseif ($newPass !== $confirmPass) {
        $mensajeError = 'Las contraseñas no coinciden.';
    } elseif (strlen($newPass) < 6) {
        $mensajeError = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE cedula = :c AND activo = true LIMIT 1");
        $stmt->execute(['c' => $cedulaInput]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && !empty($user['reset_token_hash']) && !empty($user['reset_token_expira'])) {
            $ahora = date('Y-m-d H:i:s');
            
            if ($ahora > $user['reset_token_expira']) {
                $mensajeError = 'El código de seguridad de 15 minutos ha expirado. Solicite uno nuevo.';
            } elseif (password_verify($codigoInput, $user['reset_token_hash'])) {
                // Código correcto y a tiempo -> Actualizar clave y limpiar token
                $nuevaHash = password_hash($newPass, PASSWORD_BCRYPT);
                $update = $pdo->prepare("UPDATE usuarios_admin SET password_hash = :p, reset_token_hash = NULL, reset_token_expira = NULL WHERE id = :id");
                $update->execute(['p' => $nuevaHash, 'id' => $user['id']]);

                $mensajeExito = '¡Contraseña actualizada con éxito! Ya puede iniciar sesión.';
                $pasoRecuperacion = 'login';
            } else {
                $mensajeError = 'El código de seguridad ingresado es incorrecto.';
            }
        } else {
            $mensajeError = 'No se encontró una solicitud válida para esta cédula.';
        }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'enviado') {
    $mensajeExito = 'Si la cédula se encuentra registrada, se enviará un código de 6 dígitos a su correo institucional (válido por 15 minutos).';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso / Recuperación | Escuela Nora Castañeda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #5a1224;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login {
            width: 100%;
            max-width: 440px;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }
        .btn-vinotinto {
            background-color: #5a1224;
            color: #fff;
            border: none;
        }
        .btn-vinotinto:hover {
            background-color: #3f0c19;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="card card-login border-0 bg-white p-4">
    <div class="text-center mb-3">
        <img src="../public/assets/img/LogoEscuelaNora_NUEVO.png" width="70" height="70" class="rounded-circle border border-warning mb-2" alt="Logo">
        <h5 class="fw-bold mb-0 text-dark">Panel Administrativo</h5>
        <small class="text-muted">Escuela Nora Castañeda</small>
    </div>

    <?php if (!empty($mensajeError)): ?>
        <div class="alert alert-danger alert-dismissible fade show small fw-semibold" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($mensajeError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($mensajeExito)): ?>
        <div class="alert alert-success alert-dismissible fade show small fw-semibold" role="alert">
            <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($mensajeExito) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- PANTALLA 1: FORMULARIO DE LOGIN -->
    <?php if ($pasoRecuperacion === 'login'): ?>
        <form method="POST" action="login.php">
            <input type="hidden" name="action_login" value="1">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Usuario o Cédula</label>
                <input type="text" name="usuario" class="form-control" required placeholder="Ej: V-12345678">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Contraseña</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-vinotinto w-100 fw-bold rounded-3 py-2 mb-3">Iniciar Sesión</button>
            <div class="text-center">
                <a href="login.php?paso=solicitar" class="text-decoration-none small text-danger fw-bold">¿Olvidaste tu contraseña?</a>
            </div>
        </form>

    <!-- PANTALLA 2: SOLICITAR CÓDIGO POR CÉDULA -->
    <?php elseif ($pasoRecuperacion === 'solicitar'): ?>
        <form method="POST" action="login.php">
            <input type="hidden" name="action_solicitar_codigo" value="1">
            <div class="alert alert-warning small py-2">
                <i class="bi bi-shield-lock me-1"></i> Ingrese su <strong>Cédula de Identidad</strong> para enviar el código de seguridad a su correo.
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Cédula de Identidad</label>
                <input type="text" name="cedula" class="form-control" placeholder="Ej: V-12345678" required>
            </div>
            <button type="submit" class="btn btn-vinotinto w-100 fw-bold rounded-3 py-2 mb-3">Enviar Código de Seguridad</button>
            <div class="text-center">
                <a href="login.php" class="text-decoration-none small text-secondary"><i class="bi bi-arrow-left"></i> Volver al Login</a>
            </div>
        </form>

    <!-- PANTALLA 3: INGRESAR CÓDIGO Y NUEVA CONTRASEÑA -->
    <?php elseif ($pasoRecuperacion === 'validar'): ?>
        <form method="POST" action="login.php">
            <input type="hidden" name="action_cambiar_clave" value="1">
            <div class="mb-3">
                <label class="form-label fw-semibold small">Cédula de Identidad</label>
                <input type="text" name="cedula" class="form-control bg-light" value="<?= htmlspecialchars($_GET['cedula'] ?? '') ?>" required readonly>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Código de Seguridad (6 dígitos)</label>
                <input type="text" name="codigo" class="form-control fw-bold text-center fs-5" maxlength="6" placeholder="000000" required autocomplete="off">
                <small class="text-muted extra-small">Revisa tu correo electrónico. Expira en 15 minutos.</small>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Nueva Contraseña</label>
                <input type="password" name="nueva_password" class="form-control" minlength="6" required placeholder="Mínimo 6 caracteres">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Confirmar Nueva Contraseña</label>
                <input type="password" name="confirmar_password" class="form-control" minlength="6" required placeholder="Repite la nueva contraseña">
            </div>
            <button type="submit" class="btn btn-vinotinto w-100 fw-bold rounded-3 py-2 mb-3">Restablecer Contraseña</button>
            <div class="text-center">
                <a href="login.php" class="text-decoration-none small text-secondary"><i class="bi bi-arrow-left"></i> Cancelar y Volver</a>
            </div>
        </form>
    <?php endif; ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>