<?php
/**
 * API AJAX - PANEL ADMINISTRATIVO
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Sesión no autorizada']);
    exit();
}

require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Obtener fotos de una galería
if ($action === 'obtener_fotos_galeria') {
    $galeriaId = intval($_GET['galeria_id'] ?? 0);
    try {
        $stmt = $pdo->prepare("SELECT id, archivo FROM galeria_fotos WHERE galeria_id = :gid ORDER BY id DESC");
        $stmt->execute(['gid' => $galeriaId]);
        $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'fotos' => $fotos]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// Eliminar foto individual de una galería
if ($action === 'eliminar_foto_galeria') {
    $fotoId = intval($_POST['foto_id'] ?? $_GET['foto_id'] ?? 0);
    try {
        $stmtF = $pdo->prepare("SELECT archivo FROM galeria_fotos WHERE id = :id");
        $stmtF->execute(['id' => $fotoId]);
        $archivo = $stmtF->fetchColumn();

        if ($archivo) {
            $path = __DIR__ . '/../public/uploads/galerias/' . $archivo;
            if (file_exists($path)) @unlink($path);

            $stmtDel = $pdo->prepare("DELETE FROM galeria_fotos WHERE id = :id");
            $stmtDel->execute(['id' => $fotoId]);
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Acción no válida']);
exit();