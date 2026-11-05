<?php
/**
 * ==============================================================================
 * MÓDULO DE CIERRE Y DESTRUCCIÓN DE SESIÓN
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Limpia las variables globales, expira las cookies asociadas
 *              y destruye la sesión activa para evitar reutilización.
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpieza total del array $_SESSION
$_SESSION = array();

// Expiración forzada de la cookie de sesión en el navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrucción formal de la sesión
session_destroy();

// Redirección directa al inicio de sesión
header("Location: login.php");
exit();
?>