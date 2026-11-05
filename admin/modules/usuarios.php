<?php
/**
 * ==============================================================================
 * MÓDULO DE GESTIÓN DE USUARIOS DEL SISTEMA
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * ==============================================================================
 */

if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }

if (($rolUsuarioActual ?? '') !== 'Superadministrador') {
    echo '<div class="alert alert-danger fw-bold"><i class="bi bi-shield-lock-fill me-2"></i> Acceso denegado. No posee permisos para administrar usuarios.</div>';
    return;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1" style="color: var(--color-vinotinto);"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>Usuarios del Sistema</h3>
        <p class="text-muted small mb-0">Administra privilegios de acceso, contraseñas y asignación de roles institucionales.</p>
    </div>
    <button type="button" class="btn btn-vinotinto rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuario">
        <i class="bi bi-person-plus-fill me-1"></i> Crear Nuevo Usuario
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Cédula / ID</th>
                        <th>Nombre Completo</th>
                        <th>Usuario / Correo</th>
                        <th>Rol Asignado</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($usuariosLista)): ?>
                        <?php foreach ($usuariosLista as $u): ?>
                            <tr>
                                <td class="ps-4 fw-bold">
                                    <span class="badge bg-light text-dark border me-1"><?= htmlspecialchars($u['cedula'] ?? 'N/A') ?></span>
                                    <small class="text-muted">#<?= intval($u['id']) ?></small>
                                </td>
                                <td><strong class="text-dark"><?= htmlspecialchars($u['nombre']) ?></strong></td>
                                <td>
                                    <div class="small fw-semibold text-primary"><?= htmlspecialchars($u['usuario']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        $badgeColor = 'bg-secondary';
                                        switch ($u['rol']) {
                                            case 'Superadministrador': $badgeColor = 'bg-danger'; break;
                                            case 'Viceministerio': $badgeColor = 'bg-dark'; break;
                                            case 'Prensa': $badgeColor = 'bg-warning text-dark'; break;
                                            case 'Control de Estudio': $badgeColor = 'bg-primary'; break;
                                            case 'Profesor': $badgeColor = 'bg-info text-dark'; break;
                                        }
                                    ?>
                                    <span class="badge <?= $badgeColor ?> rounded-pill px-3 py-2">
                                        <?= htmlspecialchars($u['rol']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?= ($u['activo'] ?? true) ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                        <?= ($u['activo'] ?? true) ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary rounded-circle me-1" title="Editar Usuario" onclick='abrirModalEditarUsuario(<?= json_encode($u) ?>)'>
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning rounded-circle me-1" title="Cambiar Contraseña" onclick='abrirModalResetClave(<?= json_encode($u) ?>)'>
                                        <i class="bi bi-key-fill"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL CREAR NUEVO USUARIO -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="guardar_usuario">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill text-warning me-2"></i>Registrar Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cédula</label>
                        <input type="text" name="cedula" class="form-control" placeholder="Ej: V-12345678" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nombre y Apellido</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre de Usuario</label>
                        <input type="text" name="usuario" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Correo Electrónico</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contraseña Inicial</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Rol Asignado</label>
                        <select name="rol" class="form-select fw-bold">
                            <option value="Superadministrador">Superadministrador</option>
                            <option value="Viceministerio">Viceministerio</option>
                            <option value="Prensa">Prensa</option>
                            <option value="Control de Estudio">Control de Estudio</option>
                            <option value="Profesor">Profesor</option>
                        </select>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold text-vinotinto mb-0"><i class="bi bi-key-fill me-1 text-warning"></i> Preguntas de Seguridad</h6>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Primera Pregunta</label>
                        <input type="text" name="pregunta_1" class="form-control" placeholder="Ej: ¿Ciudad de nacimiento?" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Respuesta 1</label>
                        <input type="text" name="respuesta_1" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Segunda Pregunta</label>
                        <input type="text" name="pregunta_2" class="form-control" placeholder="Ej: ¿Nombre de tu mascota?" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Respuesta 2</label>
                        <input type="text" name="respuesta_2" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Crear Usuario</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDITAR USUARIO -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="dashboard.php" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="editar_usuario">
            <input type="hidden" name="id" id="editUserId">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Editar Datos del Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Cédula</label>
                        <input type="text" name="cedula" id="editUserCedula" class="form-control" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nombre y Apellido</label>
                        <input type="text" name="nombre" id="editUserNombre" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nombre de Usuario</label>
                        <input type="text" name="usuario" id="editUserUsuario" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Correo Electrónico</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Rol Asignado</label>
                        <select name="rol" id="editUserRol" class="form-select fw-bold">
                            <option value="Superadministrador">Superadministrador</option>
                            <option value="Viceministerio">Viceministerio</option>
                            <option value="Prensa">Prensa</option>
                            <option value="Control de Estudio">Control de Estudio</option>
                            <option value="Profesor">Profesor</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Actualizar Datos</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CAMBIAR CLAVE DE USUARIO (ADMIN) -->
<div class="modal fade" id="modalResetClave" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="dashboard.php" class="modal-content border-0 rounded-4 shadow-lg">
            <input type="hidden" name="action" value="admin_reset_password">
            <input type="hidden" name="id" id="resetUserId">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-key-fill text-warning me-2"></i>Restablecer Contraseña</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small">Escriba la nueva contraseña para el usuario <strong id="resetUserNombre" class="text-dark"></strong>:</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nueva Contraseña</label>
                    <input type="password" name="nueva_password" class="form-control" minlength="6" required placeholder="Mínimo 6 caracteres">
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold">Cambiar Clave</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalEditarUsuario(u) {
    document.getElementById('editUserId').value = u.id;
    document.getElementById('editUserCedula').value = u.cedula || '';
    document.getElementById('editUserNombre').value = u.nombre || '';
    document.getElementById('editUserUsuario').value = u.usuario || '';
    document.getElementById('editUserEmail').value = u.email || '';
    document.getElementById('editUserRol').value = u.rol || 'Control de Estudio';
    
    var modalElement = document.getElementById('modalEditarUsuario');
    var modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modalInstance.show();
}

function abrirModalResetClave(u) {
    document.getElementById('resetUserId').value = u.id;
    document.getElementById('resetUserNombre').innerText = u.nombre + ' (' + u.usuario + ')';
    
    var modalElement = document.getElementById('modalResetClave');
    var modalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
    modalInstance.show();
}
</script>