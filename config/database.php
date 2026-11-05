<?php
/**
 * ==============================================================================
 * ARCHIVO DE CONFIGURACIÓN Y CONEXIÓN A BASE DE DATOS (PDO POSTGRESQL)
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Inicializa la instancia PDO con PostgreSQL bajo manejo estricto
 *              de excepciones y captura de errores.
 * Auditoría:
 *  - Driver PDO PostgreSQL.
 *  - Modo de eror configurado en ERRMODE_EXCEPTION.
 *  - Formato por defecto de respuesta asociativo (`PDO::FETCH_ASSOC`).
 * ==============================================================================
 */

$host     = 'localhost';
$port     = '5432';$db       = 'Nora_castaneda';
$user     = 'postgres';$password = '123456'; 

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db;";
    $pdo = new PDO($dsn, $user,$password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    // Interrupción segura si no se establece conexión
    die("Error en la conexión a la base de datos: " . $e->getMessage());
}
?>