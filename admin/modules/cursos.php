<?php
/**
 * MÓDULO DE GESTIÓN DE CURSOS (VISTA + MODALES)
 */
if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0" style="color: var(--color-vinotinto);">Gestión de Cursos y Oferta Académica</h3>
    <button class="btn btn-vinotinto rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoCurso"><i class="bi bi-plus-circle me-1"></i> Aperturar Curso</button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-uppercase small fw-bold">
                <tr>
                    <th class="ps-4">Nombre del Curso</th>
                    <th>Modalidad</th>
                    <th>Horario</th>
                    <th>Cupos</th>
                    <th>Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($cursosLista)): ?>
                    <?php foreach ($cursosLista as $c): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($c['nombre']) ?></td>
                            <td><span class="badge bg-dark"><?= htmlspecialchars($c['modalidad']) ?></span></td>
                            <td><?= htmlspecialchars($c['hora_inicio'] ?? '08:00') ?> - <?= htmlspecialchars($c['hora_fin'] ?? '12:00') ?></td>
                            <td><?= intval($c['cupos']) ?></td>
                            <td>
                                <form method="POST" action="dashboard.php" class="d-inline">
                                    <input type="hidden" name="action" value="toggle_curso">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm rounded-pill border <?= ($c['publicado'] ?? true) ? 'btn-success' : 'btn-secondary' ?>">
                                        <?= ($c['publicado'] ?? true) ? 'Publicado' : 'Oculto' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Editar Curso" onclick='modalEditarCurso(<?= json_encode($c) ?>)'>
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio'], true)): ?>
                                <form method="POST" action="dashboard.php" class="d-inline" onsubmit="return confirm('¿Eliminar este curso?')">
                                    <input type="hidden" name="action" value="eliminar_curso">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Eliminar">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4 text-muted">No existen cursos cargados.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL APERTURAR CURSO -->
<div class="modal fade" id="modalNuevoCurso" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="guardar_curso">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-journal-plus text-warning me-2"></i>Aperturar Nuevo Curso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nombre del Curso</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej: Liderazgo Comunitario" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Modalidad</label>
                        <select name="modalidad" class="form-select">
                            <option value="Presencial">Presencial</option>
                            <option value="Virtual">Virtual</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Alcance</label>
                        <input type="text" name="alcance" class="form-control" value="Solo para residentes de Maracay" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Cupos</label>
                        <input type="number" name="cupos" class="form-control" value="30" required>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Hora Inicio</label><input type="time" name="hora_inicio" class="form-control" value="08:00" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Hora Fin</label><input type="time" name="hora_fin" class="form-control" value="12:00" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Fecha Inicio Inscripciones</label><input type="date" name="fecha_inicio" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Fecha Cierre Inscripciones</label><input type="date" name="fecha_cierre" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required></div>
                    <div class="col-12"><label class="form-label fw-semibold">Lugar / Sede</label><input type="text" name="lugar" class="form-control" value="Sede Principal Bello Monte, Maracay" required></div>
                    <div class="col-12"><label class="form-label fw-semibold">Flyer Imagen Promocional</label><input type="file" name="flyer_imagen" class="form-control" accept="image/*"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Descripción o Reseña</label><textarea name="descripcion" class="form-control" rows="3" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Guardar Curso</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR CURSO COMPLETO -->
<div class="modal fade" id="modalEditarCurso" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" enctype="multipart/form-data" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="editar_curso">
            <input type="hidden" name="id" id="editCursoId">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Curso</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label fw-semibold">Nombre del Curso</label><input type="text" name="nombre" id="editCursoNombre" class="form-control" required></div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Modalidad</label>
                        <select name="modalidad" id="editCursoModalidad" class="form-select">
                            <option value="Presencial">Presencial</option>
                            <option value="Virtual">Virtual</option>
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Alcance</label><input type="text" name="alcance" id="editCursoAlcance" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Cupos</label><input type="number" name="cupos" id="editCursoCupos" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Hora Inicio</label><input type="time" name="hora_inicio" id="editCursoHoraInicio" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Hora Fin</label><input type="time" name="hora_fin" id="editCursoHoraFin" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Fecha Inicio</label><input type="date" name="fecha_inicio" id="editCursoFechaInicio" class="form-control"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Fecha Cierre</label><input type="date" name="fecha_cierre" id="editCursoFechaCierre" class="form-control"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Lugar</label><input type="text" name="lugar" id="editCursoLugar" class="form-control" required></div>
                    <div class="col-12">
                        <label class="form-label fw-semibold"><i class="bi bi-image text-primary me-1"></i> Reemplazar Flyer / Imagen Promocional (Opcional)</label>
                        <input type="file" name="flyer_imagen" class="form-control" accept="image/*">
                        <small class="text-muted d-block mt-1" id="labelFlyerActual"></small>
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Descripción o Reseña</label><textarea name="descripcion" id="editCursoDescripcion" class="form-control" rows="3" required></textarea></div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Actualizar Curso</button>
            </div>
        </form>
    </div>
</div>

<script>
    function modalEditarCurso(curso) {
        document.getElementById('editCursoId').value = curso.id;
        document.getElementById('editCursoNombre').value = curso.nombre;
        document.getElementById('editCursoModalidad').value = curso.modalidad || 'Presencial';
        document.getElementById('editCursoAlcance').value = curso.alcance || '';
        document.getElementById('editCursoCupos').value = curso.cupos || 30;
        document.getElementById('editCursoHoraInicio').value = curso.hora_inicio || '08:00';
        document.getElementById('editCursoHoraFin').value = curso.hora_fin || '12:00';
        document.getElementById('editCursoFechaInicio').value = curso.fecha_inicio || '';
        document.getElementById('editCursoFechaCierre').value = curso.fecha_cierre || '';
        document.getElementById('editCursoLugar').value = curso.lugar || '';
        document.getElementById('editCursoDescripcion').value = curso.descripcion || curso.resena || '';

        const labelFlyer = document.getElementById('labelFlyerActual');
        if (curso.flyer_imagen || curso.flyer_path) {
            labelFlyer.innerText = 'Flyer cargado actual: ' + (curso.flyer_imagen || curso.flyer_path);
        } else {
            labelFlyer.innerText = '';
        }

        const m = obtenerOInstanciarModal('modalEditarCurso');
        if (m) m.show();
    }
</script>