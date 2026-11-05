<?php
/**
 * MÓDULO DE GESTIÓN DE FOROS
 */
if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--color-vinotinto);"><i class="bi bi-chat-square-quote-fill me-2 text-warning"></i>Gestión de Foros Digitales</h3>
        <p class="text-muted small mb-0">Administra los temas de discusión y consulta los archivos PDF subidos.</p>
    </div>
    <button class="btn btn-vinotinto rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoForo">
        <i class="bi bi-plus-circle-fill me-1"></i> Crear Nuevo Foro
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Título del Foro</th>
                        <th>Categoría</th>
                        <th>Archivo PDF</th>
                        <th class="text-center">Comentarios</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($forosLista)): ?>
                        <?php foreach ($forosLista as$f): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $f['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($f['titulo']) ?></div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 260px;"><?= htmlspecialchars($f['descripcion']) ?></small>
                                </td>
                                <td><span class="badge bg-secondary rounded-pill"><?= htmlspecialchars($f['categoria'] ?? 'General') ?></span></td>
                                <td>
                                    <?php if (!empty($f['pdf_archivo'])): ?>
                                        <a href="../public/uploads/<?= htmlspecialchars($f['pdf_archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-danger rounded-pill fw-bold">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-light border rounded-pill px-3 fw-bold" onclick="verComentariosForo(<?= $f['id'] ?>, '<?= htmlspecialchars(addslashes($f['titulo'])) ?>')">
                                        <i class="bi bi-chat-left-dots-fill text-primary me-1"></i> <?= $f['total_comentarios'] ?>
                                    </button>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Editar Foro" onclick='modalEditarForo(<?= json_encode($f) ?>)'>
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio'], true)): ?>
                                    <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('¿Eliminar este foro?')">
                                        <input type="hidden" name="action" value="eliminar_foro">
                                        <input type="hidden" name="id" value="<?= $f['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar Foro">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted">No existen foros registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CREAR FORO -->
<div class="modal fade" id="modalNuevoForo" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="guardar_foro">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill text-warning me-2"></i>Crear Nuevo Foro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Título del Foro</label>
                        <input type="text" name="titulo" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Categoría</label>
                        <select name="categoria" class="form-select fw-bold">
                            <option value="Formación General">Formación General</option>
                            <option value="Emprendimiento">Emprendimiento</option>
                            <option value="Derechos Humanos">Derechos Humanos</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Adjuntar PDF (Opcional)</label>
                        <input type="file" name="pdf_archivo" class="form-control" accept="application/pdf">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Planteamiento o Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Publicar Foro</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR FORO -->
<div class="modal fade" id="modalEditarForo" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="editar_foro">
            <input type="hidden" name="id" id="editForoId">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Foro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Título del Foro</label>
                        <input type="text" name="titulo" id="editForoTitulo" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Categoría</label>
                        <select name="categoria" id="editForoCategoria" class="form-select fw-bold">
                            <option value="Formación General">Formación General</option>
                            <option value="Emprendimiento">Emprendimiento</option>
                            <option value="Derechos Humanos">Derechos Humanos</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold"><i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Adjuntar o Reemplazar PDF</label>
                        <input type="file" name="pdf_archivo" class="form-control" accept="application/pdf">
                        <div class="mt-2" id="boxPdfActual"></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Descripción o Planteamiento</label>
                        <textarea name="descripcion" id="editForoDescripcion" class="form-control" rows="4" required></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL VER COMENTARIOS -->
<div class="modal fade" id="modalVerComentarios" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="tituloForoComentarios"><i class="bi bi-chat-left-text-fill me-2"></i>Comentarios del Foro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 bg-light" id="boxListaComentarios"></div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function modalEditarForo(foro) {
    document.getElementById('editForoId').value = foro.id;
    document.getElementById('editForoTitulo').value = foro.titulo;
    document.getElementById('editForoCategoria').value = foro.categoria || 'Formación General';
    document.getElementById('editForoDescripcion').value = foro.descripcion;
    
    const boxPdf = document.getElementById('boxPdfActual');
    if (foro.pdf_archivo) {
        boxPdf.innerHTML = `<span class="badge bg-light text-danger border p-2"><i class="bi bi-file-earmark-pdf-fill me-1"></i> Archivo actual: ${foro.pdf_archivo}</span>`;
    } else {
        boxPdf.innerHTML = '<span class="text-muted small">Actualmente sin archivo PDF adjunto.</span>';
    }

    const m = obtenerOInstanciarModal('modalEditarForo');
    if (m) m.show();
}

function verComentariosForo(foroId, tituloForo) {
    document.getElementById('tituloForoComentarios').innerHTML = `<i class="bi bi-chat-left-text-fill me-2"></i> Comentarios: ${tituloForo}`;
    const boxComentarios = document.getElementById('boxListaComentarios');
    boxComentarios.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Cargando comentarios...</p></div>';

    const m = obtenerOInstanciarModal('modalVerComentarios');
    if (m) m.show();

    fetch(`api_admin.php?action=obtener_comentarios&foro_id=${foroId}`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.comentarios.length > 0) {
                let html = '';
                data.comentarios.forEach(c => {
                    html += `
                        <div class="card border-0 shadow-sm rounded-3 mb-3 p-3 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-dark"><i class="bi bi-person-circle text-primary me-1"></i> ${c.autor}</strong>
                                <small class="text-muted">${c.fecha}</small>
                            </div>
                            <p class="text-secondary mb-0 small">${c.comentario}</p>
                        </div>
                    `;
                });
                boxComentarios.innerHTML = html;
            } else {
                boxComentarios.innerHTML = '<div class="text-center text-muted py-4"><i class="bi bi-chat-square-dots fs-2 d-block mb-2"></i>Este foro aún no posee comentarios.</div>';
            }
        })
        .catch(() => {
            boxComentarios.innerHTML = '<div class="alert alert-danger mb-0">Error al obtener los comentarios.</div>';
        });
}
</script>