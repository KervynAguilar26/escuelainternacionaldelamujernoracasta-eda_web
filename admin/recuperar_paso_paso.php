<?php
/**
 * ==============================================================================
 * RECUPERACIÓN PASO A PASO POR CÉDULA Y PREGUNTAS DE SEGURIDAD
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Módulo asistido en 3 pasos para redefinir clave mediante
 *              cédula y verificación de preguntas de seguridad.
 * Mejoras de Seguridad:
 *  - Verificación estricta de hash en respuestas con `password_verify()`.
 *  - Requisitos mínimos de longitud para la nueva clave.
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

$step = 1; 
$mensaje = '';
$usuarioEncontrado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // PASO 1: Búsqueda por cédula
    if (isset($_POST['verificar_cedula'])) {
        $cedulaInput = trim($_POST['cedula'] ?? '');
        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE cedula = :ci AND activo = TRUE");
        $stmt->execute(['ci' => $cedulaInput]);
        $usuarioEncontrado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuarioEncontrado) {
            $_SESSION['reset_user_id'] = $usuarioEncontrado['id'];
            $step = 2;
        } else {
            $mensaje = "No se encontró ningún usuario activo con la cédula ingresada.";
        }
    }
    // PASO 2: Validación de preguntas
    elseif (isset($_POST['validar_respuestas'])) {
        $userId = $_SESSION['reset_user_id'] ?? 0;
        $r1Input = strtolower(trim($_POST['respuesta_1'] ?? ''));
        $r2Input = strtolower(trim($_POST['respuesta_2'] ?? ''));

        $stmt = $pdo->prepare("SELECT * FROM usuarios_admin WHERE id = :id AND activo = TRUE");
        $stmt->execute(['id' => $userId]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($u && password_verify($r1Input, $u['respuesta_seguridad_1']) && password_verify($r2Input, $u['respuesta_seguridad_2'])) {
            $step = 3;
            $usuarioEncontrado = $u;
        } else {
            $mensaje = "Respuestas de seguridad incorrectas.";
            $step = 2;
            $usuarioEncontrado = $u;
        }
    }
    // PASO 3: Cambio de contraseña
    elseif (isset($_POST['cambiar_password'])) {
        $userId = $_SESSION['reset_user_id'] ?? 0;
        $nuevaPass = $_POST['nueva_password'] ?? '';

        if (strlen($nuevaPass) >= 6) {
            $hash = password_hash($nuevaPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET password_hash = :p WHERE id = :id");
            $stmt->execute(['p' => $hash, 'id' => $userId]);

            unset($_SESSION['reset_user_id']);
            header("Location: login.php?res=password_actualizada");
            exit();
        } else {
            $mensaje = "La contraseña debe tener al menos 6 caracteres.";
            $step = 3;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Acceso | Escuela Nora Castañeda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

<div class="card border-0 shadow-lg rounded-4 p-4" style="max-width: 450px; width: 100%;">
    <h4 class="fw-bold text-center mb-3" style="color: #521f29;">Recuperar Credenciales</h4>

    <?php if ($mensaje): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <form method="POST">
            <p class="text-muted small">Ingrese su número de cédula de identidad registrado como llave de verificación.</p>
            <div class="mb-3">
                <label class="form-label fw-bold">Cédula de Identidad</label>
                <input type="text" name="cedula" class="form-control" placeholder="Ej: V-12345678" required>
            </div>
            <button type="submit" name="verificar_cedula" class="btn btn-primary w-100 rounded-pill fw-bold">Verificar Cédula</button>
        </form>

    <?php elseif ($step === 2 && $usuarioEncontrado): ?>
        <form method="POST">
            <div class="alert alert-info small mb-3">
                <strong>Usuario Registrado:</strong> <?= htmlspecialchars($usuarioEncontrado['usuario']) ?>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold"><?= htmlspecialchars($usuarioEncontrado['pregunta_seguridad_1']) ?></label>
                <input type="text" name="respuesta_1" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold"><?= htmlspecialchars($usuarioEncontrado['pregunta_seguridad_2']) ?></label>
                <input type="text" name="respuesta_2" class="form-control" required>
            </div>
            <button type="submit" name="validar_respuestas" class="btn btn-primary w-100 rounded-pill fw-bold">Validar Respuestas</button>
        </form>

    <?php elseif ($step === 3): ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold">Nueva Contraseña</label>
                <input type="password" name="nueva_password" class="form-control" minlength="6" required>
            </div>
            <button type="submit" name="cambiar_password" class="btn btn-success w-100 rounded-pill fw-bold">Actualizar Contraseña</button>
        </form>
    <?php endif; ?>

    <div class="text-center mt-3">
        <a href="login.php" class="text-decoration-none small text-secondary">Volver al inicio de sesión</a>
    </div>
</div>

</body>
</html>