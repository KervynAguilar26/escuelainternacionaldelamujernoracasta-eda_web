<?php
/**
 * ==============================================================================
 * MÓDULO DE GALERÍAS Y ÁLBUMES FOTOGRÁFICOS INSTITUCIONALES
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Muestra las galerías creadas en el panel administrador y las 
 *              imágenes registradas en el banco multimedia.
 * Auditoría:
 *  - Doble fuente de datos (Álbumes organizados + Fotografías en Recursos).
 *  - Generación dinámica de rutas de imagen según tipo (`uploads/galerias/` vs `uploads/`).
 * ==============================================================================
 */

// Carga del conector PDO
$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    @include_once $database_path;
}

$galeriasConFotos = [];

if (isset($pdo) && $pdo !== null) {
    // 1. Consultar Galerías/Álbumes creados activamente
    try {
        $stmtG = $pdo->query("SELECT * FROM galerias WHERE publicado = TRUE ORDER BY id DESC");
        if ($stmtG) {
            $galeriasObtenidas = $stmtG->fetchAll(PDO::FETCH_ASSOC);
            foreach ($galeriasObtenidas as $galeria) {
                // Obtener fotos asociadas a cada álbum
                $stmtF = $pdo->prepare("SELECT archivo FROM galeria_fotos WHERE galeria_id = :gid ORDER BY id ASC");
                $stmtF->execute(['gid' => $galeria['id']]);
                $fotosAlbum = $stmtF->fetchAll(PDO::FETCH_ASSOC);

                $galeriasConFotos[] = [
                    'id'          => $galeria['id'],
                    'titulo'      => $galeria['titulo'],
                    'descripcion' => $galeria['descripcion'],
                    'fotos'       => $fotosAlbum,
                    'tipo'        => 'album'
                ];
            }
        }
    } catch (Exception $e) {}

    // 2. Consultar fotografías independientes guardadas en Recursos Digitales (Respaldo)
    try {
        $stmtR = $pdo->query("SELECT * FROM recursos_digitales WHERE publicado = TRUE AND tipo_recurso = 'Fotografía' ORDER BY id DESC");
        if ($stmtR) {
            $fotosRecursos = $stmtR->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($fotosRecursos)) {
                $fotosMapeadas = array_map(function($f) {
                    return ['archivo' => $f['archivo']];
                }, $fotosRecursos);

                $galeriasConFotos[] = [
                    'id'          => 'recursos_digitales',
                    'titulo'      => 'Banco Fotográfico Institucional',
                    'descripcion' => 'Registro fotográfico de actividades y jornadas territoriales.',
                    'fotos'       => $fotosMapeadas,
                    'tipo'        => 'recursos'
                ];
            }
        }
    } catch (Exception $e) {}
}

$pageTitle = 'Galería Fotográfica | Escuela Nora Castañeda';
include_once __DIR__ . '/includes/header.php';
?>

    <!-- Encabezado Compacto Ajustado -->
    <section class="py-3 text-white text-center" style="background: linear-gradient(135deg, var(--color-vinotinto), var(--color-vinotinto-dark)); border-bottom: 3px solid var(--color-dorado);">
        <div class="container py-1">
            <h2 class="fs-3 fw-bold mb-1"><i class="bi bi-camera-fill text-warning me-2 fs-4"></i>Galería Fotográfica Institucional</h2>
            <p class="small opacity-90 mb-0">Hitos, jornadas territoriales, talleres formativos y eventos socio-productivos.</p>
        </div>
    </section>

    <!-- Contenido Principal de Galerías -->
    <main class="container py-4 flex-grow-1">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="index.php" class="btn btn-sm btn-outline-dark rounded-pill fw-bold px-3">
                <i class="bi bi-arrow-left me-1"></i> Volver al Inicio
            </a>
            <span class="text-muted small fw-semibold">
                <i class="bi bi-images me-1 text-danger"></i> Total de Álbumes: <?= count($galeriasConFotos) ?>
            </span>
        </div>

        <?php if (!empty($galeriasConFotos)): ?>
            <?php foreach ($galeriasConFotos as $index => $galeria): ?>
                <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                    <div class="card-header bg-white p-3 px-4 border-bottom">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-0" style="color: var(--color-vinotinto);"><?= htmlspecialchars($galeria['titulo']) ?></h5>
                                <?php if (!empty($galeria['descripcion'])): ?>
                                    <p class="text-muted small mb-0"><?= htmlspecialchars($galeria['descripcion']) ?></p>
                                <?php endif; ?>
                            </div>
                            <span class="badge bg-warning text-dark px-3 py-1 rounded-pill fw-bold extra-small">
                                <i class="bi bi-image-fill me-1"></i> <?= count($galeria['fotos']) ?> Fotos
                            </span>
                        </div>
                    </div>
                    
                    <div class="card-body p-3 bg-light">
                        <div class="row g-3">
                            <?php if (!empty($galeria['fotos'])): ?>
                                <?php foreach ($galeria['fotos'] as $foto): ?>
                                    <?php 
                                        // Determinación dinámica de la subcarpeta del servidor
                                        $subCarpeta = ($galeria['tipo'] === 'album') ? 'uploads/galerias/' : 'uploads/';
                                        $rutaFoto = $subCarpeta . $foto['archivo'];
                                    ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6">
                                        <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 position-relative" style="transition: transform 0.3s ease;">
                                            <a href="<?= $rutaFoto ?>" target="_blank" class="d-block text-decoration-none">
                                                <div style="height: 200px; overflow: hidden; background-color: #eee;">
                                                    <img src="<?= $rutaFoto ?>" class="w-100 h-100" style="object-fit: cover; transition: transform 0.3s ease;" alt="Fotografía Institucional" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12 text-center py-3 text-muted">
                                    <small>Este álbum aún no contiene fotografías cargadas.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 my-4">
                <i class="bi bi-images fs-1 text-muted d-block mb-3"></i>
                <h5 class="fw-bold text-secondary">No existen galerías fotográficas publicadas</h5>
                <p class="text-muted small">Las imágenes cargadas desde el Panel de Control aparecerán en este espacio.</p>
            </div>
        <?php endif; ?>

    </main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>