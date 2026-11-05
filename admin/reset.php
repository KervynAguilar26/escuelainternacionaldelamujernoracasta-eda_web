<?php
/**
 * ==============================================================================
 * MÓDULO DE RESTABLECIMIENTO DE CONTRASEÑA POR TOKEN
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Procesa enlaces generados desde la pantalla de login, valida la
 *              expiración del token y solicita la verificación de respuestas.
 * Mejoras de Seguridad:
 *  - Limpieza completa del token (`NULL`) tras completar el restablecimiento
 *    para evitar reuso del enlace.
 *  - Verificación estricta de tiempo de expiración mediante `NOW()`.
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 0);
error_reporting(E_ALL);

$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    require_once $database_path;
}

$token =$_GET['token'] ?? $_POST['token'] ?? '';$error = '';
$exito = '';$user = null;

if (empty($token)) {
    die("Token no proporcionado o inválido.");
}

// 1. BUSCAR USUARIO Y VALIDAR EXPIRACIÓN DEL TOKEN
if (isset($pdo)) {
    $stmt =$pdo->prepare("SELECT * FROM usuarios_admin WHERE reset_token = :t AND reset_token_expira > NOW() LIMIT 1");
    $stmt->execute(['t' =>$token]);
    $user =$stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {$error = "El enlace de recuperación es inválido o ha expirado. Por favor solicita uno nuevo.";
    }
}

// 2. PROCESAR FORMULARIO POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user && isset($pdo)) {
    $r1_ingresada = strtolower(trim($_POST['respuesta_1'] ?? ''));
    $r2_ingresada = strtolower(trim($_POST['respuesta_2'] ?? ''));
    $nueva_pass   =$_POST['nueva_password'] ?? '';

    $db_r1 =$user['respuesta_seguridad_1'] ?? '';
    $db_r2 =$user['respuesta_seguridad_2'] ?? '';

    // Verificación segura mediante password_verify
    $valida1 = !empty($db_r1) && (password_verify($r1_ingresada,$db_r1) || $r1_ingresada === strtolower(trim($db_r1)));
    $valida2 = !empty($db_r2) && (password_verify($r2_ingresada,$db_r2) || $r2_ingresada === strtolower(trim($db_r2)));

    if (!$valida1 || !$valida2) {$error = "Una o ambas respuestas de seguridad son incorrectas.";
    } elseif (strlen($nueva_pass) < 6) {$error = "La nueva contraseña debe tener al menos 6 caracteres.";
    } else {
        $new_hash = password_hash($nueva_pass, PASSWORD_BCRYPT);

        // Invalidación del token tras su uso exitoso
        $update =$pdo->prepare("UPDATE usuarios_admin 
                                 SET password_hash = :p, reset_token = NULL, reset_token_expira = NULL 
                                 WHERE id = :id");
        $update->execute(['p' => $new_hash, 'id' =>$user['id']]);

        $exito = "¡Contraseña actualizada correctamente! Ya puedes iniciar sesión.";
        $user = null; // Ocultar formulario
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña | Escuela Nora Castañeda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #521f29, #3b151d); 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        .reset-card { 
            background: #fff; 
            border-radius: 20px; 
            width: 100%; 
            max-width: 450px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
        }
        .btn-vinotinto {
            background-color: #521f29;
            color: #ffffff;
        }
        .btn-vinotinto:hover {
            background-color: #3b151d;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="reset-card p-4">
        <div class="text-center mb-4">
            <h5 class="fw-bold text-dark mb-0">Restablecer Contraseña</h5>
            <small class="text-muted">Escuela Nora Castañeda</small>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($exito): ?>
            <div class="alert alert-success py-3 text-center small">
                <?= htmlspecialchars($exito) ?><br><br>
                <a href="login.php" class="btn btn-vinotinto btn-sm rounded-pill px-4 fw-bold">Ir al Login</a>
            </div>
        <?php endif; ?>

        <?php if ($user): ?>
            <form action="reset.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">
                        1. <?= htmlspecialchars($user['pregunta_seguridad_1'] ?? 'Primera Pregunta de Seguridad') ?>
                    </label>
                    <input type="text" name="respuesta_1" class="form-control" required placeholder="Tu respuesta">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">
                        2. <?= htmlspecialchars($user['pregunta_seguridad_2'] ?? 'Segunda Pregunta de Seguridad') ?>
                    </label>
                    <input type="text" name="respuesta_2" class="form-control" required placeholder="Tu respuesta">
                </div>

                <hr class="my-3">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nueva Contraseña</label>
                    <input type="password" name="nueva_password" class="form-control" minlength="6" required placeholder="Mínimo 6 caracteres">
                </div>

                <button type="submit" class="btn btn-vinotinto w-100 py-2 fw-bold rounded-pill">
                    Guardar Nueva Contraseña
                </button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>