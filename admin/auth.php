<?php
/**
 * ==============================================================================
 * MIDDLEWARE DE VERIFICACIÓN DE SESIÓN ADMINISTRATIVA
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Controla que únicamente usuarios autenticados accedan
 *              a los módulos internos del panel administrativo.
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificación estricta del indicador de sesión activa
if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
?>