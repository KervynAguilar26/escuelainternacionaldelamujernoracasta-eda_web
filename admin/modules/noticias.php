<?php
/**
 * MÓDULO DE GESTIÓN DE NOTICIAS Y PRENSA
 */
if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--color-vinotinto);"><i class="bi bi-newspaper me-2 text-warning"></i>Noticias y Comunicación</h3>
        <p class="text-muted small mb-0">Administra artículos y boletines informativos del sitio.</p>
    </div>
    <button class="btn btn-vinotinto rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevaNoticia">
        <i class="bi bi-plus-circle-fill me-1"></i> Publicar Noticia
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Título de Noticia</th>
                        <th>Fuente</th>
                        <th>Fecha</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($noticiasLista)): ?>
                        <?php foreach ($noticiasLista as $n): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $n['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($n['titulo']) ?></div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;"><?= htmlspecialchars($n['resumen']) ?></small>
                                </td>
                                <td><span class="badge bg-warning text-dark rounded-pill"><?= htmlspecialchars($n['periodista']) ?></span></td>
                                <td><small class="text-muted"><?= htmlspecialchars($n['fecha_publicacion']) ?></small></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Editar Noticia" onclick='modalEditarNoticia(<?= json_encode($n) ?>)'>
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('¿Eliminar esta noticia?')">
                                        <input type="hidden" name="action" value="eliminar_noticia">
                                        <input type="hidden" name="id" value="<?= $n['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No existen noticias en la base de datos.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL NUEVA NOTICIA -->
<div class="modal fade" id="modalNuevaNoticia" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="guardar_noticia">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-newspaper text-warning me-2"></i>Redactar Noticia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label fw-semibold">Título</label><input type="text" name="titulo" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Periodista/Fuente</label><input type="text" name="periodista" class="form-control" value="Prensa MinMujer" required></div>
                    <div class="col-12"><label class="form-label fw-semibold">Imagen Portada</label><input type="file" name="imagen_portada" class="form-control" accept="image/*"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Resumen</label><textarea name="resumen" class="form-control" rows="2" required></textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Contenido Completo</label><textarea name="contenido_completo" class="form-control" rows="4" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Publicar Noticia</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR NOTICIA -->
<div class="modal fade" id="modalEditarNoticia" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="editar_noticia">
            <input type="hidden" name="id" id="editNoticiaId">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Noticia</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label fw-semibold">Título</label><input type="text" name="titulo" id="editNoticiaTitulo" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label fw-semibold">Periodista/Fuente</label><input type="text" name="periodista" id="editNoticiaPeriodista" class="form-control" required></div>
                    <div class="col-12"><label class="form-label fw-semibold">Reemplazar Imagen Portada (Opcional)</label><input type="file" name="imagen_portada" class="form-control" accept="image/*"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Resumen</label><textarea name="resumen" id="editNoticiaResumen" class="form-control" rows="2" required></textarea></div>
                    <div class="col-12"><label class="form-label fw-semibold">Contenido Completo</label><textarea name="contenido_completo" id="editNoticiaContenido" class="form-control" rows="4" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Actualizar Noticia</button>
            </div>
        </form>
    </div>
</div>

<script>
function modalEditarNoticia(noticia) {
    document.getElementById('editNoticiaId').value = noticia.id;
    document.getElementById('editNoticiaTitulo').value = noticia.titulo;
    document.getElementById('editNoticiaPeriodista').value = noticia.periodista || 'Prensa MinMujer';
    document.getElementById('editNoticiaResumen').value = noticia.resumen;
    document.getElementById('editNoticiaContenido').value = noticia.contenido_completo;
    
    const m = obtenerOInstanciarModal('modalEditarNoticia');
    if (m) m.show();
}
</script>