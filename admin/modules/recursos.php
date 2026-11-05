<?php
/**
 * MÓDULO DE RECURSOS DIGITALES
 */
if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--color-vinotinto);"><i class="bi bi-folder-symlink-fill me-2 text-warning"></i>Recursos Digitales</h3>
        <p class="text-muted small mb-0">Gestión de guías formativas y manuales institucionales.</p>
    </div>
    <button class="btn btn-vinotinto rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoRecurso">
        <i class="bi bi-plus-circle-fill me-1"></i> Subir Recurso
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Título Recurso</th>
                        <th>Tipo</th>
                        <th>Archivo</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($recursosLista)): ?>
                        <?php foreach ($recursosLista as $r): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= $r['id'] ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['titulo']) ?></div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 300px;"><?= htmlspecialchars($r['descripcion']) ?></small>
                                </td>
                                <td><span class="badge bg-primary rounded-pill"><?= htmlspecialchars($r['tipo_recurso']) ?></span></td>
                                <td>
                                    <?php if (!empty($r['archivo'])): ?>
                                        <a href="../public/uploads/<?= htmlspecialchars($r['archivo']) ?>" target="_blank" class="btn btn-sm btn-outline-dark rounded-pill fw-bold">
                                            <i class="bi bi-download me-1"></i> Descargar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-4">
                                    <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio'], true)): ?>
                                    <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('¿Eliminar recurso?')">
                                        <input type="hidden" name="action" value="eliminar_recurso">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No existen recursos digitales cargados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL NUEVO RECURSO DIGITAL -->
<div class="modal fade" id="modalNuevoRecurso" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="guardar_recurso">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-folder-plus text-warning me-2"></i>Subir Recurso Digital</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label fw-semibold">Título del Recurso</label><input type="text" name="titulo" class="form-control" required></div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tipo</label>
                        <select name="tipo_recurso" class="form-select fw-bold">
                            <option value="Guía Formativa">Guía Formativa</option>
                            <option value="Infografía">Infografía</option>
                            <option value="Documento Legal">Documento Legal</option>
                        </select>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Archivo Adjunto</label><input type="file" name="archivo_recurso" class="form-control" required></div>
                    <div class="col-12"><label class="form-label fw-semibold">Descripción</label><textarea name="descripcion" class="form-control" rows="3" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Guardar Recurso</button>
            </div>
        </form>
    </div>
</div>