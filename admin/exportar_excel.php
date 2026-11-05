<?php
/**
 * ==============================================================================
 * EXPORTACIÓN A EXCEL DEL LISTADO DE INSCRITOS
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Genera un reporte descargable en formato Excel (.xls) con la
 *              lista de estudiantes registradas.
 * Mejoras de Seguridad:
 *  - Verificación previa de sesión con `auth.php` para bloquear descargas
 *    no autorizadas a través de peticiones directas.
 * ==============================================================================
 */

require_once __DIR__ . '/auth.php'; // Protección de acceso
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=Inscritos_Escuela_Nora_Castaneda_' . date('Y-m-d') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

echo "\xEF\xBB\xBF"; // UTF-8 BOM para soporte de acentos en Excel

try {
    if (!isset($pdo) || $pdo === null) {
        throw new Exception("Sin conexión a la base de datos.");
    }

    $stmt = $pdo->query("SELECT * FROM public.inscripciones ORDER BY id DESC");
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'>";
    echo "<tr style='background-color: #521f29; color: #ffffff;'>
            <th>ID</th>
            <th>Nacionalidad</th>
            <th>Cédula</th>
            <th>Nombre y Apellido</th>
            <th>Fecha Nacimiento</th>
            <th>Edad</th>
            <th>Sexo</th>
            <th>País</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Cursos Seleccionados</th>
            <th>Fecha Registro</th>
          </tr>";

    foreach ($registros as $r) {
        echo "<tr>";
        echo "<td>" . intval($r['id']) . "</td>";
        echo "<td>" . htmlspecialchars($r['nacionalidad'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['cedula'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['nombre_apellido'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['fecha_nacimiento'] ?? '') . "</td>";
        echo "<td>" . intval($r['edad'] ?? 0) . "</td>";
        echo "<td>" . htmlspecialchars($r['sexo'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['pais_origen'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['correo'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['telefono'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['cursos_seleccionados'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($r['creado_en'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    error_log("Error en exportar_excel: " . $e->getMessage());
    echo "Error al generar el archivo de reporte.";
}