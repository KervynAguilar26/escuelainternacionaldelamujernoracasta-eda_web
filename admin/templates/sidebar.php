<?php
/**
 * BARRA LATERAL CON NAVEGACIÓN Y PERFIL DE USUARIO
 */
$tabActiva = $_GET['tab'] ?? 'resumen';
?>
<div class="sidebar d-flex flex-column p-3">
    <div class="d-flex align-items-center mb-4 px-2">
        <img src="../public/assets/img/LogoEscuelaNora_NUEVO.png" width="40" height="40" class="me-2 rounded-circle border border-warning">
        <div>
            <span class="d-block text-white fw-bold small">Escuela Nora Castañeda</span>
            <span class="d-block text-warning extra-small" style="font-size: 11px;"><?= strtoupper(htmlspecialchars($rolUsuarioActual)) ?></span>
        </div>
    </div>

    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="dashboard.php?tab=resumen" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'resumen' ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Resumen
            </a>
        </li>

        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Control de Estudio'], true)): ?>
        <li class="nav-item">
            <a href="dashboard.php?tab=cursos" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'cursos' ? 'active' : '' ?>">
                <i class="bi bi-journal-bookmark-fill me-2"></i> Cursos
            </a>
        </li>
        <?php endif; ?>

        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Profesor'], true)): ?>
        <li class="nav-item">
            <a href="dashboard.php?tab=foros" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'foros' ? 'active' : '' ?>">
                <i class="bi bi-chat-square-quote-fill me-2"></i> Foros (PDF)
            </a>
        </li>
        <?php endif; ?>

        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Prensa', 'Viceministerio'], true)): ?>
        <li class="nav-item">
            <a href="dashboard.php?tab=comunicacion" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'comunicacion' ? 'active' : '' ?>">
                <i class="bi bi-newspaper me-2"></i> Comunicación / Prensa
            </a>
        </li>
        <li class="nav-item">
            <a href="dashboard.php?tab=galerias" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'galerias' ? 'active' : '' ?>">
                <i class="bi bi-images me-2"></i> Galerías de Fotos
            </a>
        </li>
        <?php endif; ?>

        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Profesor'], true)): ?>
        <li class="nav-item">
            <a href="dashboard.php?tab=recursos" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'recursos' ? 'active' : '' ?>">
                <i class="bi bi-folder-symlink-fill me-2"></i> Recursos Digitales
            </a>
        </li>
        <?php endif; ?>

        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Control de Estudio'], true)): ?>
        <li class="nav-item">
            <a href="dashboard.php?tab=inscritos" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'inscritos' ? 'active' : '' ?>">
                <i class="bi bi-people-fill me-2"></i> Inscritos
            </a>
        </li>
        <?php endif; ?>

        <?php if ($rolUsuarioActual === 'Superadministrador'): ?>
        <li class="nav-item">
            <a href="dashboard.php?tab=usuarios" class="nav-link nav-link-admin w-100 text-start <?= $tabActiva === 'usuarios' ? 'active' : '' ?>">
                <i class="bi bi-shield-lock-fill me-2 text-warning"></i> Usuarios Sistema
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <div class="pt-3 border-top border-secondary">
        <!-- Nombre del usuario activo -->
        <button type="button" class="btn btn-sm btn-outline-warning w-100 text-start mb-2 rounded-pill fw-bold" data-bs-toggle="modal" data-bs-target="#modalMiPerfil">
            <i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($nombreUsuarioActual) ?>
        </button>
        <a href="../public/index.php" target="_blank" class="btn btn-sm btn-outline-light w-100 rounded-pill mb-2"><i class="bi bi-globe me-1"></i> Sitio Web</a>
        <a href="logout.php" class="btn btn-sm btn-danger w-100 rounded-pill"><i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión</a>
    </div>
</div>

<!-- MODAL AUTOGESTIÓN DE PERFIL (Cualquier usuario) -->
<div class="modal fade" id="modalMiPerfil" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="dashboard.php" class="modal-content border-0 rounded-4 shadow-lg text-dark">
            <input type="hidden" name="action" value="actualizar_mi_perfil">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-gear text-warning me-2"></i>Mi Perfil / Cambiar Clave</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre de Usuario</label>
                    <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($_SESSION['admin_usuario'] ?? '') ?>" required>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nueva Contraseña (Opcional)</label>
                    <input type="password" name="nueva_password" class="form-control" placeholder="Dejar en blanco para mantener la actual">
                    <small class="text-muted">Si desea cambiar su clave, ingrese al menos 6 caracteres.</small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-vinotinto rounded-pill px-4 fw-bold">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>