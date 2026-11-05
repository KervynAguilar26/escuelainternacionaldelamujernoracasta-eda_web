<?php
/**
 * PLANTILLA DE ENCABEZADO BASE
 */
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../auth.php';

$rolUsuarioActual = $_SESSION['admin_rol'] ?? 'Control de Estudio';
$nombreUsuarioActual = $_SESSION['admin_nombre'] ?? 'Usuario Sistema';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador | Escuela Nora Castañeda</title>
    
    <link rel="icon" type="image/png" sizes="16x16" href="../public/assets/img/Logo 16 x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-vinotinto: #521f29;
            --color-vinotinto-dark: #3b151d;
            --color-dorado: #e5b871;
            --color-crema: #fdf8f3;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f4f6f9; }

        .sidebar {
            width: 260px; height: 100vh; position: fixed; top: 0; left: 0;
            background-color: var(--color-vinotinto-dark); color: #ffffff; z-index: 1000;
        }

        .main-content { margin-left: 260px; padding: 30px; }
        .nav-link-admin { color: rgba(255,255,255,0.75); font-weight: 600; border-radius: 12px; margin-bottom: 6px; padding: 10px 16px; }
        .nav-link-admin:hover, .nav-link-admin.active { background-color: var(--color-vinotinto); color: #ffffff !important; }
        .btn-vinotinto { background-color: var(--color-vinotinto); color: #ffffff; }
        .btn-vinotinto:hover { background-color: var(--color-vinotinto-dark); color: #ffffff; }
    </style>
</head>
<body>