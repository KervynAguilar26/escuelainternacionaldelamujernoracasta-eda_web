<?php
/**
 * ==============================================================================
 * BANCO DE RECURSOS DIGITALES Y MULTIMEDIA
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Despliega guías formativas, infografías, documentos legales, 
 *              audios y videos cargados desde la base de datos.
 * Auditoría:
 *  - Detección dinámica de extensiones para renderizado multimedia nativo (HTML5 <video>/<audio>).
 *  - Búsqueda en tiempo real mediante JavaScript.
 *  - Filtros dinámicos por categorías.
 * ==============================================================================
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carga de base de datos
$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    @include_once $database_path;
}

$recursosItems = [];

if (isset($pdo) && $pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT * FROM recursos_digitales WHERE publicado = TRUE ORDER BY id DESC");
        if ($stmt) $recursosItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

$pageTitle = "Recursos Digitales | Escuela Nora Castañeda";
require_once __DIR__ . '/includes/header.php';
?>

    <!-- Header de la sección -->
    <section class="py-4 text-center bg-white border-bottom">
        <div class="container">
            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-2">BANCO MULTIMEDIA</span>
            <h1 class="fw-extrabold text-dark mb-1">Recursos Digitales y Audiovisuales</h1>
            <p class="lead text-muted mb-0 small">Accede a contenidos formativos, guías, conferencias en audio y video institucional.</p>
        </div>
    </section>

    <!-- Contenido Principal -->
    <main class="container py-5 flex-grow-1">

        <!-- Barra de Búsqueda y Filtros por Categoría -->
        <div class="row g-3 justify-content-between align-items-center mb-5">
            <div class="col-md-7">
                <div class="d-flex flex-wrap gap-2" id="filterGroup">
                    <button class="btn btn-outline-secondary rounded-pill px-3 active" onclick="filtrar('todos', this)">Todos</button>
                    <button class="btn btn-outline-secondary rounded-pill px-3" onclick="filtrar('Guía Formativa', this)">Guías Formativas</button>
                    <button class="btn btn-outline-secondary rounded-pill px-3" onclick="filtrar('Infografía', this)">Infografías</button>
                    <button class="btn btn-outline-secondary rounded-pill px-3" onclick="filtrar('Documento Legal', this)">Documentos Legales</button>
                </div>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="text" id="inputSearch" class="form-control rounded-pill pe-5" placeholder="Buscar por título..." onkeyup="buscarRecurso()">
                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 text-muted z-3"><i class="bi bi-search"></i></span>
                </div>
            </div>
        </div>

        <!-- Grilla Dinámica de Recursos -->
        <div class="row g-4" id="contenedorRecursos">
            <?php if (!empty($recursosItems)): ?>
                <?php foreach ($recursosItems as $rec): ?>
                    <?php
                        $nombreArchivo = !empty($rec['archivo']) ? $rec['archivo'] : ($rec['archivo_path'] ?? '');
                        $rutaArchivo   = 'uploads/' . $nombreArchivo;
                        $ext           = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

                        // Mapeo del tipo de archivo multimedia
                        $esVideo      = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'mkv']);
                        $esAudio      = in_array($ext, ['mp3', 'wav', 'aac', 'm4a']);
                        $esImagen     = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $esPdf        = ($ext === 'pdf');
                        $tipoEtiqueta = !empty($rec['tipo_recurso']) ? $rec['tipo_recurso'] : ($rec['tipo'] ?? 'Guía Formativa');
                    ?>

                    <div class="col-md-6 col-lg-4 item-card" data-tipo="<?= htmlspecialchars($tipoEtiqueta) ?>">
                        <div class="card border-0 rounded-4 shadow-sm h-100 d-flex flex-column bg-white">

                            <!-- Reproductor de Video -->
                            <?php if ($esVideo && file_exists(__DIR__ . '/' . $rutaArchivo)): ?>
                                <div class="bg-dark rounded-top d-flex align-items-center justify-content-center" style="min-height: 200px;">
                                    <video controls preload="metadata" style="max-height: 220px; width: 100%;">
                                        <source src="<?= $rutaArchivo ?>" type="video/<?= $ext === 'mp4' ? 'mp4' : $ext ?>">
                                    </video>
                                </div>

                            <!-- Reproductor de Audio -->
                            <?php elseif ($esAudio && file_exists(__DIR__ . '/' . $rutaArchivo)): ?>
                                <div class="p-4 text-center bg-dark text-white rounded-top">
                                    <i class="bi bi-music-note-beaming fs-1 text-warning mb-2 d-block"></i>
                                    <audio controls class="mt-2 w-100">
                                        <source src="<?= $rutaArchivo ?>" type="audio/<?= $ext === 'mp3' ? 'mpeg' : $ext ?>">
                                    </audio>
                                </div>

                            <!-- Visualizador de Imágenes/Infografías -->
                            <?php elseif ($esImagen && file_exists(__DIR__ . '/' . $rutaArchivo)): ?>
                                <img src="<?= $rutaArchivo ?>" class="card-img-top rounded-top" style="height: 200px; object-fit: cover;" alt="<?= htmlspecialchars($rec['titulo']) ?>">

                            <!-- Documento de Texto / PDF -->
                            <?php else: ?>
                                <div class="p-4 text-center bg-light border-bottom rounded-top">
                                    <i class="bi <?= $esPdf ? 'bi-file-earmark-pdf-fill text-danger' : 'bi-file-earmark-text-fill text-primary' ?> display-4 mb-2 d-block"></i>
                                    <span class="badge bg-secondary text-uppercase"><?= $ext ? $ext : 'DOCUMENTO' ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-warning text-dark fw-bold"><?= htmlspecialchars($tipoEtiqueta) ?></span>
                                </div>

                                <h5 class="fw-bold text-title" style="color: var(--color-vinotinto);"><?= htmlspecialchars($rec['titulo']) ?></h5>
                                <p class="text-muted small flex-grow-1"><?= htmlspecialchars($rec['descripcion'] ?? '') ?></p>

                                <?php if (!empty($nombreArchivo) && file_exists(__DIR__ . '/' . $rutaArchivo)): ?>
                                    <a href="<?= $rutaArchivo ?>" download class="btn btn-naranja w-100 py-2 mt-3">
                                        <i class="bi bi-download me-2"></i>Descargar Archivo
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 py-2 mt-3" disabled>Archivo No Disponible</button>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-folder-x display-1 text-muted d-block mb-3"></i>
                    <h4 class="fw-bold text-muted">No se han encontrado recursos cargados en el portal.</h4>
                </div>
            <?php endif; ?>
        </div>

        <div id="noResults" class="text-center py-5 d-none">
            <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
            <h5 class="text-muted">No hay resultados que coincidan con el criterio de búsqueda.</h5>
        </div>

    </main>

    <!-- Pie de página -->
    <footer class="py-4 text-center footer-naranja mt-auto">
        <div class="container">
            <p class="mb-0 small opacity-90 fw-bold">&copy; 2026 Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda". Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función JS para filtrar elementos según el tipo seleccionado
        function filtrar(tipo, btn) {
            document.querySelectorAll('#filterGroup button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const items = document.querySelectorAll('.item-card');
            let contador = 0;

            items.forEach(item => {
                const itemTipo = item.getAttribute('data-tipo');
                if (tipo === 'todos' || itemTipo.toLowerCase() === tipo.toLowerCase()) {
                    item.style.display = 'block';
                    contador++;
                } else {
                    item.style.display = 'none';
                }
            });

            document.getElementById('noResults').classList.toggle('d-none', contador > 0);
        }

        // Búsqueda en vivo por coincidencia en el título
        function buscarRecurso() {
            const query = document.getElementById('inputSearch').value.toLowerCase();
            const items = document.querySelectorAll('.item-card');
            let contador = 0;

            items.forEach(item => {
                const titulo = item.querySelector('.text-title').textContent.toLowerCase();
                if (titulo.includes(query)) {
                    item.style.display = 'block';
                    contador++;
                } else {
                    item.style.display = 'none';
                }
            });

            document.getElementById('noResults').classList.toggle('d-none', contador > 0);
        }
    </script>
</body>
</html>