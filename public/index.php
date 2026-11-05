<?php
/**
 * ==============================================================================
 * PORTAL INSTITUCIONAL PRINCIPAL
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Landing page informativa con fundamentos, revista digital, 
 *              enlace al Sistema Administrativo, noticias y mapa de la sede.
 * Auditoría:
 *  - Carga dinámica de cursos y noticias destacadas desde la BD.
 *  - Enlace corregido al Sistema Administrativo (`sistema_nora/index.php`).
 *  - Modal interactivo JS para visualizar notas de prensa completas.
 * ==============================================================================
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    @include_once $database_path;
}

$cursosItems   = [];
$noticiasItems = [];
$revistaOficial = null;

if (isset($pdo) && $pdo !== null) {
    // Lectura de Cursos Publicados
    try {
        $stmt = $pdo->query("SELECT * FROM cursos WHERE publicado = TRUE ORDER BY id DESC");
        if ($stmt) $cursosItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Lectura de Noticias Publicadas
    try {
        $stmt = $pdo->query("SELECT * FROM portal_noticias WHERE publicado = TRUE ORDER BY fecha_publicacion DESC, id DESC");
        if ($stmt) $noticiasItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Búsqueda del último insumo tipo Revista Digital
    try {
        $stmtR = $pdo->query("SELECT * FROM recursos_digitales WHERE publicado = TRUE AND (tipo_recurso LIKE '%Revista%' OR titulo LIKE '%Revista%') ORDER BY id DESC LIMIT 1");
        if ($stmtR) $revistaOficial = $stmtR->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

$pageTitle = 'Escuela Internacional de Liderazgo "Nora Castañeda"';
include_once __DIR__ . '/includes/header.php';
?>

    <!-- Banner Principal Hero -->
    <section id="inicio" class="hero-banner">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 text-center text-lg-start">
                    <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold mb-3">Vanguardia femenina para la formación y el empoderamiento.</span>
                    <h5 class="text-uppercase tracking-wide text-white-50 fw-semibold mb-1">Escuela Internacional de Liderazgo de la Mujer</h5>
                    <h1 class="display-4 fw-extrabold text-white mb-3">NORA CASTAÑEDA</h1>
                    <p class="lead opacity-90 mb-4" style="max-width: 760px;">
                       «Yo soy de las que cree, que es tiempo de dificultades, pero también de esperanzas y la esperanza es un motor importante en tiempos de transformación, en la concreción de los sueños».
                       <br><strong>Nora Castañeda</strong>
                    </p>
                </div>
                <div class="col-lg-4 text-center">
                    <img src="assets/img/Logo Nora Castañeda 239 x 246-01.png" alt="Sello Nora Castañeda" class="img-fluid hero-logo-img">
                </div>
            </div>
        </div>
    </section>

    <!-- Ejes y Enfoque Pedagógico -->
    <section id="fundamentos" class="py-5 bg-white">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h2 class="section-title">Ejes y Enfoque Pedagógico</h2>
                <p class="text-muted mt-2">Principios que orientan la propuesta educativa emancipadora</p>
                <div class="section-divider"></div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-4">
                    <div class="card-fundamento-original">
                        <div class="card-header-naranja">
                            <i class="bi bi-heart-pulse-fill fs-1 d-block mb-1"></i>
                            <h5 class="fw-bold mb-0">Economía para la Vida</h5>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Reconocimiento del trabajo doméstico y de cuidado no remunerado como actividad generadora de riqueza y pilar fundamental de la Sostenibilidad de la Vida (Art. 88 CRBV).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card-fundamento-original">
                        <div class="card-header-vinotinto">
                            <i class="bi bi-journal-check fs-1 text-warning d-block mb-1"></i>
                            <h5 class="fw-bold mb-0">Matriz INVEDECOR</h5>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Sistema de evaluación integral que articula la <strong>Investigación</strong>, <strong>Educación</strong>, <strong>Comunicación</strong> y <strong>Organización</strong> comunitaria para validar el saber popular.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card-fundamento-original">
                        <div class="card-header-naranja">
                            <i class="bi bi-diagram-3-fill fs-1 d-block mb-1"></i>
                            <h5 class="fw-bold mb-0">Tres Ejes Estratégicos</h5>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-secondary small mb-0" style="line-height: 1.6;">
                                Formación integral estructurada en <strong>Transformación Social</strong>, <strong>Seguridad y Defensa de la Nación</strong>, y <strong>Economía Productiva</strong> con aval para financiamientos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BLOQUE DE REVISTA CON BOTÓN NARANJA -->
            <div class="p-4 rounded-4 shadow-sm text-center border-start border-5" style="background-color: var(--color-crema-suave); border-color: var(--color-naranja) !important;">
                <div class="row align-items-center">
                    <div class="col-md-8 text-md-start mb-3 mb-md-0">
                        <h5 class="fw-bold mb-1" style="color: var(--color-vinotinto);">
                            <i class="bi bi-book-half text-warning fs-4 me-2"></i>Revista e Insumos Político-Pedagógicos
                        </h5>
                        <p class="text-muted small mb-0">Consulta la publicación oficial donde se profundizan las metodologías, la matriz INVEDECOR y la filosofía de Nora Castañeda.</p>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <?php if (!empty($revistaOficial) && !empty($revistaOficial['archivo'])): ?>
                            <a href="uploads/<?= htmlspecialchars($revistaOficial['archivo']) ?>" target="_blank" class="btn-revista-naranja">
                                <i class="bi bi-download me-2"></i>Descargar Revista PDF
                            </a>
                        <?php else: ?>
                            <a href="recursos.php" class="btn-revista-naranja">
                                <i class="bi bi-folder-symlink me-2"></i>Ver Insumos Formativos
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Módulos de Oferta y Accesos Directos -->
    <section id="cursos" class="py-5" style="background-color: var(--color-crema-suave);">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h2 class="section-title">Plataforma Académica y Recursos</h2>
                <p class="text-muted mt-2">Módulos de formación, espacios de debate e insumos formativos</p>
                <div class="section-divider"></div>
            </div>

            <div class="row g-4 justify-content-center">
                <div class="col-lg-3 col-md-6 text-center">
                    <!-- Enlace corregido al Sistema Administrativo Interno de la Escuela -->
                    <a href="http://localhost/sistema_nora/index.php" class="text-decoration-none d-block">
                        <div class="flyer-container-719x1265">
                            <span class="badge bg-danger flyer-badge rounded-pill fw-bold">Sistema Administrativo</span>
                            <img src="assets/img/escuela.png" class="flyer-img-719x1265" alt="Sistema Administrativo Nora">
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 text-center">
                    <a href="ofertas.php" class="text-decoration-none d-block">
                        <div class="flyer-container-719x1265">
                            <span class="badge bg-success flyer-badge rounded-pill fw-bold">Socio-Productivo</span>
                            <img src="assets/img/emprendimiento.jpeg" class="flyer-img-719x1265" alt="Cursos y Talleres">
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 text-center">
                    <a href="foros.php" class="text-decoration-none d-block">
                        <div class="flyer-container-719x1265">
                            <span class="badge bg-danger flyer-badge rounded-pill fw-bold">Debate y PDF</span>
                            <img src="assets/img/Foro.jpeg" class="flyer-img-719x1265" alt="Foros Digitales">
                        </div>
                    </a>
                </div>

                <div class="col-lg-3 col-md-6 text-center">
                    <a href="recursos.php" class="text-decoration-none d-block">
                        <div class="flyer-container-719x1265">
                            <span class="badge bg-warning text-dark flyer-badge rounded-pill fw-bold">Multimedia</span>
                            <img src="assets/img/recurso_digitales.jpeg" class="flyer-img-719x1265" alt="Banco Multimedia">
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección de Noticias -->
    <section id="noticias" class="py-5 bg-white">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h2 class="section-title">Noticias y Actualidad</h2>
                <p class="text-muted mt-2">Reseñas de actividades e hitos territoriales</p>
                <div class="section-divider"></div>
            </div>

            <div class="row g-4 justify-content-center">
                <?php if (!empty($noticiasItems)): ?>
                    <?php foreach ($noticiasItems as $noticia): ?>
                        <?php 
                            $fotoNombre = !empty($noticia['imagen_portada']) ? trim($noticia['imagen_portada']) : '';
                            $rutaFoto   = 'assets/img/Banner 1224x568_Mesa de trabajo 1 copia 2.jpg';

                            if (!empty($fotoNombre)) {
                                $dirUploads = __DIR__ . '/uploads/';
                                if (file_exists($dirUploads . $fotoNombre)) {
                                    $rutaFoto = 'uploads/' . $fotoNombre;
                                }
                            }
                        ?>
                        <div class="col-md-4">
                            <div class="card flyer-card h-100" onclick='verDetalleNoticia(<?= json_encode($noticia) ?>, "<?= $rutaFoto ?>")'>
                                <div class="flyer-img-container">
                                    <img src="<?= $rutaFoto ?>" class="flyer-img-full" alt="Noticia">
                                </div>
                                <div class="card-body p-4 d-flex flex-column">
                                    <small class="fw-bold text-danger mb-1"><?= date('d/m/Y', strtotime($noticia['fecha_publicacion'])) ?> | <?= htmlspecialchars($noticia['periodista']) ?></small>
                                    <h5 class="fw-bold mb-2" style="color: var(--color-vinotinto);"><?= htmlspecialchars($noticia['titulo']) ?></h5>
                                    <p class="text-muted small flex-grow-1"><?= htmlspecialchars(substr($noticia['resumen'], 0, 100)) ?>...</p>
                                    <span class="fw-semibold small mt-2" style="color: var(--color-naranja);">Leer nota completa <i class="bi bi-arrow-right"></i></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-md-4">
                        <div class="card flyer-card h-100" onclick="verDetalleNoticia({titulo: 'Inauguración de la Sede Principal Maracay', fecha_publicacion: '2025-12-30', periodista: 'Prensa MinMujer', resumen: 'Apertura oficial de las instalaciones con residencia para 200 lideresas y Unidad de Salud Integral.', contenido_completo: 'La Escuela Internacional de Liderazgo de la Mujer Nora Castañeda cuenta con 18 aulas formativas, dormitorios, galpones socio-productivos y atención médica integral.'})">
                            <div class="flyer-img-container">
                                <img src="assets/img/Banner 1224x568_Mesa de trabajo 1 copia 2.jpg" class="flyer-img-full" alt="Inauguración">
                            </div>
                            <div class="card-body p-4">
                                <small class="fw-bold text-danger mb-1">30/12/2025 | Prensa MinMujer</small>
                                <h5 class="fw-bold mb-2" style="color: var(--color-vinotinto);">Sede Principal Maracay</h5>
                                <p class="text-muted small mb-0">Instalaciones activas con alojamiento para 200 lideresas, galpones productivos y centro médico.</p>
                                <span class="fw-semibold small mt-3 d-inline-block" style="color: var(--color-naranja);">Leer nota completa <i class="bi bi-arrow-right"></i></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Sección Nosotros (Misión y Visión) -->
    <section id="nosotros" class="py-5" style="background-color: var(--color-crema-suave);">
        <div class="container py-3">
            <div class="text-center mb-5">
                <h2 class="section-title">Misión y Visión Emancipadora</h2>
                <p class="text-muted mt-2">Marco ético e histórico de la institución</p>
                <div class="section-divider"></div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-4 bg-white rounded-4 shadow-sm h-100 border-start border-4 border-warning">
                        <h4 class="fw-bold mb-3" style="color: var(--color-vinotinto);"><i class="bi bi-bullseye me-2" style="color: var(--color-naranja);"></i> Misión</h4>
                        <p class="text-secondary mb-0" style="line-height: 1.7;">
                            Despatriarcalizar el poder y la economía mediante la formación técnica, sociopolítica y decolonial de las mujeres. Garantizar herramientas para la Sostenibilidad de la Vida, el empoderamiento territorial y la consolidación del Socialismo Feminista.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="p-4 bg-white rounded-4 shadow-sm h-100 border-start border-4" style="border-color: var(--color-vinotinto) !important;">
                        <h4 class="fw-bold mb-3" style="color: var(--color-vinotinto);"><i class="bi bi-eye-fill me-2" style="color: var(--color-naranja);"></i> Visión</h4>
                        <p class="text-secondary mb-0" style="line-height: 1.7;">
                            Ser el semillero referente de pensamiento crítico y desarrollo socio-productivo en Venezuela y Nuestra América, reconocido por integrar el rigor técnico con la investigación-acción para la liberación de las mujeres.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ubicación Sede Principal con Mapa Google Embed -->
    <section id="contacto" class="py-5 bg-white">
        <div class="container py-3">
            <div class="row align-items-center g-4">
                <div class="col-lg-5">
                    <h2 class="fw-bold mb-3" style="color: var(--color-vinotinto);">Sede Principal Maracay</h2>
                    <p class="text-secondary mb-4">Complejo formativo con aulas, laboratorios, dormitorios y atención en salud integral para las participantes.</p>
                    
                    <div class="d-flex align-items-start mb-3">
                        <div class="p-3 bg-light shadow-sm rounded-circle me-3" style="color: var(--color-vinotinto);">
                            <i class="bi bi-geo-alt-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Dirección Oficial</span>
                            <strong style="color: var(--color-vinotinto);">Bello Monte, Calle Principal Agrícola, Parcela 6, Maracay, Edo. Aragua.</strong>
                        </div>
                    </div>

                    <div class="d-flex align-items-center mb-3">
                        <div class="p-3 bg-light shadow-sm rounded-circle me-3" style="color: var(--color-vinotinto);">
                            <i class="bi bi-envelope-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small">Correo Electrónico</span>
                            <strong style="color: var(--color-vinotinto);">tecnologianoracastaneda@gmail.com</strong>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="rounded-4 overflow-hidden shadow-sm border border-2 border-light" style="height: 350px;">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3926.311029111867!2d-67.5767356!3d10.1642706!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMTDCsDA5JzUxLjQiTiA2N8KwMzQnMzYuMiJX!5e0!3m2!1ses!2sve!4v1710000000000!5m2!1ses!2sve" 
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal para Detalle de Noticia -->
    <div class="modal fade" id="modalNoticia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header text-white" style="background-color: var(--color-vinotinto);">
                    <h5 class="modal-title fw-bold" id="noticiaTituloModal">Noticia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <img id="noticiaImagenModal" src="" class="img-fluid rounded-3 mb-3 w-100" style="max-height: 350px; object-fit: cover;" alt="Portada">
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span id="noticiaPeriodistaModal"></span>
                        <span id="noticiaFechaModal"></span>
                    </div>
                    <p id="noticiaResumenModal" class="fw-semibold text-secondary mb-3"></p>
                    <div id="noticiaContenidoModal" class="text-dark" style="line-height: 1.8; white-space: pre-line;"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función JS para popular dinámicamente el Modal de Noticias
        function verDetalleNoticia(noticia, rutaImagenCalculada) {
            document.getElementById('noticiaTituloModal').innerText     = noticia.titulo;
            document.getElementById('noticiaPeriodistaModal').innerText = "Por: " + (noticia.periodista || "Redacción Prensa");
            document.getElementById('noticiaFechaModal').innerText      = noticia.fecha_publicacion || "";
            document.getElementById('noticiaResumenModal').innerText    = noticia.resumen;
            document.getElementById('noticiaContenidoModal').innerText  = noticia.contenido_completo || noticia.resumen;

            const imgModal = document.getElementById('noticiaImagenModal');
            if (rutaImagenCalculada) {
                imgModal.src           = rutaImagenCalculada;
                imgModal.style.display = "block";
            } else {
                imgModal.style.display = "none";
            }

            new bootstrap.Modal(document.getElementById('modalNoticia')).show();
        }
    </script>

<?php include_once __DIR__ . '/includes/footer.php'; ?>