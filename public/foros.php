<?php
/**
 * ==============================================================================
 * MÓDULO DE FOROS DIGITALES DE DEBATE Y LECTURA DE PONENCIAS PDF
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Permite visualizar el catálogo de foros de debate, consultar
 *              ponencias en archivos PDF integrados, publicar comentarios e
 *              intervenciones comunitarias.
 * Auditoría:
 *  - Sanitización de entradas ($_POST / $_GET) para evitar XSS.
 *  - Filtrado de foros aprobados (publicado = TRUE).
 *  - Consultas preparadas PDO en lectura de comentarios.
 * ==============================================================================
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carga del archivo de conexión a PostgreSQL
$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    @include_once $database_path;
}

/**
 * PROCESAMIENTO: Guardar nueva intervención/comentario del usuario
 */
$mensajeEstado = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'nuevo_comentario' && isset($pdo)) {
    $foro_id    = intval($_POST['foro_id'] ?? 0);
    $autor      = trim($_POST['autor'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');

    if ($foro_id > 0 && !empty($autor) && !empty($comentario)) {
        try {
            // Inserción segura del comentario
            $stmt = $pdo->prepare("INSERT INTO foro_comentarios (foro_id, autor, comentario) VALUES (:fid, :aut, :com)");
            $stmt->execute(['fid' => $foro_id, 'aut' => $autor, 'com' => $comentario]);
            
            // Redirección para evitar reenvío de formulario al recargar
            header("Location: foros.php?id=$foro_id&status=comentario_enviado");
            exit();
        } catch (Exception $e) {
            $mensajeEstado = 'Error al enviar la intervención: ' . $e->getMessage();
        }
    } else {
        $mensajeEstado = 'Por favor complete su nombre y la opinión antes de enviar.';
    }
}

/**
 * CONSULTA 1: Cargar catálogo global de foros activos
 */
$forosPublicos = [];
if (isset($pdo) && $pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT f.*, 
                             (SELECT COUNT(*) FROM foro_comentarios c WHERE c.foro_id = f.id) as total_comentarios 
                             FROM foros f 
                             WHERE f.publicado = TRUE 
                             ORDER BY f.id DESC");
        if ($stmt) $forosPublicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

/**
 * CONSULTA 2: Obtener detalle y comentarios de un foro específico si viene el parámetro "id"
 */
$foroSeleccionado = null;
$comentariosForo  = [];
$idVer            = intval($_GET['id'] ?? 0);

if ($idVer > 0 && isset($pdo)) {
    foreach ($forosPublicos as $fp) {
        if ($fp['id'] == $idVer) {
            $foroSeleccionado = $fp;
            break;
        }
    }
    if ($foroSeleccionado) {
        try {
            // Lectura de comentarios ordenados del más reciente al más antiguo
            $stmtC = $pdo->prepare("SELECT autor, comentario, TO_CHAR(creado_en, 'DD/MM/YYYY HH12:MI AM') as fecha 
                                    FROM foro_comentarios 
                                    WHERE foro_id = :fid 
                                    ORDER BY id DESC");
            $stmtC->execute(['fid' => $idVer]);
            $comentariosForo = $stmtC->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {}
    }
}

// Configuración del título y carga del encabezado visual unificado
$pageTitle = "Foros Digitales y Debate | Escuela Nora Castañeda";
require_once __DIR__ . '/includes/header.php';
?>

    <!-- Header de la sección -->
    <section class="py-4 text-center bg-white border-bottom">
        <div class="container">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">PARTICIPACIÓN Y DEBATE</span>
            <h1 class="fw-extrabold text-dark mb-1">Foros Digitales e Intercambio de Saberes</h1>
            <p class="lead text-muted mb-0 small">Espacios dialécticos de debate, lectura de ponencias en PDF y construcción colectiva.</p>
        </div>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="container py-5 flex-grow-1">

        <!-- Mensajes de estado/alerta -->
        <?php if (!empty($mensajeEstado)): ?>
            <div class="alert alert-warning alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($mensajeEstado) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'comentario_enviado'): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> ¡Tu comentario ha sido publicado exitosamente!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($foroSeleccionado): ?>
            <!-- VISTA DETALLADA DE UN FORO SELECCIONADO -->
            <div class="mb-4">
                <a href="foros.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mb-3 fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Catálogo de Foros
                </a>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fw-bold"><?= htmlspecialchars($foroSeleccionado['categoria'] ?? 'Formación General') ?></span>
                        <span class="text-muted small"><i class="bi bi-chat-quote-fill me-1" style="color: var(--color-naranja);"></i> Foro #<?= $foroSeleccionado['id'] ?></span>
                    </div>

                    <h2 class="fw-bold mb-3" style="color: var(--color-vinotinto);"><?= htmlspecialchars($foroSeleccionado['titulo']) ?></h2>
                    <p class="text-secondary fs-6 mb-4" style="line-height: 1.8; white-space: pre-line;"><?= htmlspecialchars($foroSeleccionado['descripcion']) ?></p>
                    
                    <!-- Visor de PDF adjunto -->
                    <?php if (!empty($foroSeleccionado['pdf_archivo'])): ?>
                        <div class="p-3 my-4 rounded-4 shadow-sm bg-light border">
                            <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                                <span class="fw-bold text-dark fs-6">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Documento de Lectura Adjunto
                                </span>
                                <a href="uploads/<?= htmlspecialchars($foroSeleccionado['pdf_archivo']) ?>" download class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                    <i class="bi bi-download me-1"></i> Descargar PDF
                                </a>
                            </div>
                            <div class="pdf-frame-container" style="height: 550px;">
                                <iframe src="uploads/<?= htmlspecialchars($foroSeleccionado['pdf_archivo']) ?>#toolbar=1" class="w-100 h-100" style="border: none;"></iframe>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FORMULARIO PARA ENVIAR NUDO DE DEBATE / COMENTARIO -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold mb-3" style="color: var(--color-vinotinto);"><i class="bi bi-pencil-square me-2" style="color: var(--color-naranja);"></i>Participa en la Discusión</h5>
                    <form method="POST" action="foros.php">
                        <input type="hidden" name="action" value="nuevo_comentario">
                        <input type="hidden" name="foro_id" value="<?= $foroSeleccionado['id'] ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nombres y Apellidos</label>
                                <input type="text" name="autor" class="form-control rounded-3" placeholder="Ej: Carmen Elena Pérez" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Aporte o Comentario</label>
                                <textarea name="comentario" class="form-control rounded-3" rows="3" placeholder="Escriba sus reflexiones respecto al tema abordado..." required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-naranja px-4 py-2">
                                    <i class="bi bi-send-fill me-1"></i> Publicar Mi Comentario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- LISTADO DE INTERVENCIONES REGISTRADAS -->
                <h5 class="fw-bold mb-3" style="color: var(--color-vinotinto);">Intervenciones de la Comunidad (<?= count($comentariosForo) ?>)</h5>
                <?php if (!empty($comentariosForo)): ?>
                    <?php foreach ($comentariosForo as $com): ?>
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-dark"><i class="bi bi-person-circle text-primary me-1"></i> <?= htmlspecialchars($com['autor']) ?></strong>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i><?= $com['fecha'] ?></small>
                            </div>
                            <p class="text-secondary mb-0 small" style="white-space: pre-line;"><?= htmlspecialchars($com['comentario']) ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-light text-center py-4 border rounded-4">
                        <i class="bi bi-chat-dots fs-3 text-muted d-block mb-1"></i>
                        Aún no existen comentarios en este foro. ¡Sé la primera persona en compartir sus ideas!
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- VISTA GENERAL: CATÁLOGO DE FOROS DISPONIBLES -->
            <div class="row g-4 justify-content-center">
                <?php if (!empty($forosPublicos)): ?>
                    <?php foreach ($forosPublicos as $f): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card border-0 shadow-sm rounded-4 p-4 d-flex flex-column h-100 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold"><?= htmlspecialchars($f['categoria'] ?? 'General') ?></span>
                                    <?php if (!empty($f['pdf_archivo'])): ?>
                                        <span class="badge bg-danger rounded-pill" title="Contiene Material PDF"><i class="bi bi-file-earmark-pdf-fill me-1"></i> PDF</span>
                                    <?php endif; ?>
                                </div>
                                <h5 class="fw-bold mb-2" style="color: var(--color-vinotinto);"><?= htmlspecialchars($f['titulo']) ?></h5>
                                <p class="text-muted small flex-grow-1" style="line-height: 1.6;"><?= htmlspecialchars(substr($f['descripcion'], 0, 130)) ?>...</p>
                                
                                <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                    <span class="text-muted small"><i class="bi bi-chat-left-dots-fill me-1" style="color: var(--color-naranja);"></i> <?= $f['total_comentarios'] ?> Comentarios</span>
                                    <a href="foros.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-naranja px-3">
                                        Entrar al Debate <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-md-8 text-center py-5">
                        <div class="p-5 bg-white rounded-4 shadow-sm">
                            <i class="bi bi-chat-square-x fs-1 text-muted"></i>
                            <h4 class="fw-bold mt-3 text-dark">No hay foros activos actualmente</h4>
                            <p class="text-muted mb-0">Próximamente se estará aperturando nuevo contenido de debate institucional.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <!-- Pie de página institucional -->
    <footer class="py-4 text-center footer-naranja mt-auto">
        <div class="container">
            <p class="mb-0 small opacity-90 fw-bold">&copy; 2026 Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda". Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>