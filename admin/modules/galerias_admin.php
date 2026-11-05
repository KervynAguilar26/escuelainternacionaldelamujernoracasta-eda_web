<?php
/**
 * MÓDULO DE GESTIÓN DE GALERÍAS FOTOGRÁFICAS
 * Exclusivo: Superadministrador y Prensa
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (empty($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}

$rolActual =$_SESSION['admin_rol'] ?? '';
if (!in_array($rolActual, ['Superadministrador', 'Prensa'], true)) {
    echo '<div class="alert alert-danger fw-bold"><i class="bi bi-shield-lock-fill me-2"></i> Acceso denegado. Este módulo es exclusivo para Prensa y Superadministrador.</div>';
    return;
}

require_once __DIR__ . '/../../config/database.php';

// CONSULTAR GALERÍAS EXISTENTES
$galeriasLista = [];
if (isset($pdo) &&$pdo !== null) {
    try {
        $galeriasLista =$pdo->query("SELECT g.*, (SELECT COUNT(*) FROM galeria_fotos f WHERE f.galeria_id = g.id) AS total_fotos FROM galerias g ORDER BY g.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--color-vinotinto);"><i class="bi bi-images me-2 text-warning"></i>Gestión de Galerías Fotográficas</h3>
        <p class="text-muted small mb-0">Publica álbumes de fotos institucionales y eventos territoriales.</p>
    </div>
    <button class="btn btn-vinotinto rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaGaleria">
        <i class="bi bi-plus-circle-fill me-1"></i> Crear Nueva Galería
    </button>
</div>

<!-- LISTADO DE GALERÍAS -->
<div class="row g-4">
    <?php if (!empty($galeriasLista)): ?>
        <?php foreach ($galeriasLista as$g): ?>
            <?php 
                $portada = '';
                if (isset($pdo)) {
                    $primeraFoto =$pdo->prepare("SELECT archivo FROM galeria_fotos WHERE galeria_id = :gid LIMIT 1");
                    $primeraFoto->execute(['gid' =>$g['id']]);
                    $portada =$primeraFoto->fetchColumn() ?: '';
                }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                    <div style="height: 200px; background-color: #eee; overflow: hidden; position: relative;">
                        <?php if ($portada): ?>
                            <img src="../public/uploads/galerias/<?= htmlspecialchars($portada) ?>" class="w-100 h-100" style="object-fit: cover;">
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted"><i class="bi bi-image fs-1"></i></div>
                        <?php endif; ?>
                        <span class="badge bg-dark position-absolute top-0 end-0 m-3 px-3 py-2 rounded-pill"><i class="bi bi-camera-fill me-1"></i> <?= $g['total_fotos'] ?> fotos</span>
                    </div>
                    <div class="card-body p-4 d-flex flex-column">
                        <h5 class="fw-bold text-dark mb-2"><?= htmlspecialchars($g['titulo']) ?></h5>
                        <p class="text-muted small flex-grow-1"><?= htmlspecialchars($g['descripcion']) ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <button class="btn btn-sm btn-outline-primary rounded-pill fw-bold" onclick='editarGaleria(<?= json_encode($g) ?>)'>
                                <i class="bi bi-pencil-fill me-1"></i> Editar / Añadir Fotos
                            </button>
                            <form method="POST" action="dashboard.php" onsubmit="return confirm('¿Eliminar esta galería y todas sus fotos?')">
                                <input type="hidden" name="action" value="eliminar_galeria">
                                <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar Galería Completa"><i class="bi bi-trash-fill"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-images fs-1 d-block mb-2"></i> No hay galerías registradas.
        </div>
    <?php endif; ?>
</div>

<!-- MODAL CREAR GALERÍA -->
<div class="modal fade" id="modalNuevaGaleria" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="guardar_galeria">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-images text-warning me-2"></i>Crear Galería de Fotos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Título de la Galería</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Ej: Jornada Formativa Maracay 2026" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción o Resumen</label>
                    <textarea name="descripcion" class="form-control" rows="2" placeholder="Breve reseña del evento..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold"><i class="bi bi-upload me-1"></i> Seleccionar Fotos (Ilimitadas)</label>
                    <input type="file" name="fotos[]" class="form-control" accept="image/*" multiple required>
                    <small class="text-muted">Puedes seleccionar múltiples archivos manteniendo presionada la tecla Ctrl o Shift.</small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Guardar y Publicar</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR GALERÍA CON VISOR Y ELIMINACIÓN DE FOTOS -->
<div class="modal fade" id="modalEditarGaleria" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="editar_galeria">
            <input type="hidden" name="id" id="editGaleriaId">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Galería y Gestionar Fotos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Título de la Galería</label>
                    <input type="text" name="titulo" id="editGaleriaTitulo" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea name="descripcion" id="editGaleriaDescripcion" class="form-control" rows="2"></textarea>
                </div>
                
                <!-- AGREGAR MÁS FOTOS -->
                <div class="mb-4">
                    <label class="form-label fw-semibold"><i class="bi bi-plus-circle-fill text-success me-1"></i> Agregar más Fotos a esta Galería</label>
                    <input type="file" name="fotos_nuevas[]" class="form-control" accept="image/*" multiple>
                </div>

                <hr>

                <!-- VISTA PREVIA Y ELIMINACIÓN INDIVIDUAL DE FOTOS -->
                <div class="mb-2">
                    <label class="form-label fw-bold text-dark"><i class="bi bi-grid-3x3-gap-fill text-warning me-1"></i> Fotos de la Galería (<span id="contadorFotosModal">0</span>)</label>
                    <p class="text-muted small mb-3">Haz clic en el botón rojo de cada foto para eliminarla individualmente.</p>
                    <div class="row g-3" id="contenedorFotosGaleria">
                        <!-- Se puebla dinámicamente con JS -->
                    </div>
                </div>

            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Actualizar Galería</button>
            </div>
        </form>
    </div>
</div>

<script>
function editarGaleria(g) {
    document.getElementById('editGaleriaId').value = g.id;
    document.getElementById('editGaleriaTitulo').value = g.titulo;
    document.getElementById('editGaleriaDescripcion').value = g.descripcion || '';

    // Cargar fotos vía AJAX
    cargarFotosGaleriaModal(g.id);

    var modalEl = document.getElementById('modalEditarGaleria');
    var modalInst = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
    modalInst.show();
}

function cargarFotosGaleriaModal(galeriaId) {
    const contenedor = document.getElementById('contenedorFotosGaleria');
    const contador = document.getElementById('contadorFotosModal');
    
    contenedor.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-warning"></div><p class="mt-2 text-muted small">Cargando fotografías...</p></div>';

    fetch(`api_admin.php?action=obtener_fotos_galeria&galeria_id=${galeriaId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.fotos.length > 0) {
                contador.innerText = data.fotos.length;
                let html = '';
                data.fotos.forEach(f => {
                    html += `
                        <div class="col-6 col-md-3 position-relative" id="box_foto_${f.id}">
                            <div class="card border-0 shadow-sm overflow-hidden rounded-3" style="height: 110px;">
                                <img src="../public/uploads/galerias/${f.archivo}" class="w-100 h-100" style="object-fit: cover;">
                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-1" 
                                        style="line-height: 1; width: 26px; height: 26px;" 
                                        title="Eliminar esta foto" onclick="borrarFotoIndividual(${f.id})">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
                contenedor.innerHTML = html;
            } else {
                contador.innerText = '0';
                contenedor.innerHTML = '<div class="col-12 text-center text-muted py-3"><i class="bi bi-image fs-3 d-block mb-1"></i>Esta galería no posee fotos activas.</div>';
            }
        })
        .catch(() => {
            contenedor.innerHTML = '<div class="col-12 alert alert-danger">Error al cargar las fotografías.</div>';
        });
}

function borrarFotoIndividual(fotoId) {
    if (!confirm('¿Deseas eliminar esta foto de la galería?')) return;

    fetch('api_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=eliminar_foto_galeria&foto_id=${fotoId}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const box = document.getElementById(`box_foto_${fotoId}`);
            if (box) box.remove();

            // Actualizar contador
            const contador = document.getElementById('contadorFotosModal');
            let total = parseInt(contador.innerText) - 1;
            contador.innerText = total > 0 ? total : 0;

            if (total <= 0) {
                document.getElementById('contenedorFotosGaleria').innerHTML = '<div class="col-12 text-center text-muted py-3"><i class="bi bi-image fs-3 d-block mb-1"></i>Esta galería no posee fotos activas.</div>';
            }
        } else {
            alert('No se pudo eliminar la foto.');
        }
    })
    .catch(() => {
        alert('Error de conexión al eliminar la foto.');
    });
}
</script>