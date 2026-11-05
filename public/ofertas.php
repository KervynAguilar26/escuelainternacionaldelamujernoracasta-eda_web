<?php
/**
 * ==============================================================================
 * MÓDULO DE OFERTA ACADÉMICA Y FORMULARIO MODAL DE INSCRIPCIÓN
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Despliega la lista de cursos vigentes, contabiliza cupos tomados,
 *              bloquea convocatorias vencidas e integra un modal JS interactivo
 *              que consulta el padrón vía AJAX (`api_inscripcion.php`).
 * Auditoría:
 *  - Carga de catálogos auxiliares (Etnia y Discapacidad).
 *  - Lógica de vencimiento por fecha y agotamiento de cupos.
 *  - Script cliente en JS con consulta asíncrona mediante tecla Enter.
 * ==============================================================================
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    @include_once $database_path;
}

// Carga de catálogos auxiliares
$listaEtnias        = [];
$listaDiscapacidades = [];

if (isset($pdo) && $pdo !== null) {
    try {
        $stmtE = $pdo->query("SELECT id, nombre FROM public.etnia WHERE activo = 1 ORDER BY nombre ASC");
        if ($stmtE) $listaEtnias = $stmtE->fetchAll(PDO::FETCH_ASSOC);

        $stmtD = $pdo->query("SELECT id, nombre FROM public.discapacidad WHERE activo = 1 ORDER BY nombre ASC");
        if ($stmtD) $listaDiscapacidades = $stmtD->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}
}

// Lectura de cursos y cálculo de inscritos en tiempo real
$cursosVigentes = [];

if (isset($pdo) && $pdo !== null) {
    try {
        $stmt = $pdo->query("SELECT * FROM public.cursos WHERE publicado = TRUE ORDER BY id DESC");
        if ($stmt) {
            $cursosVigentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cursosVigentes as &$c) {
                $nombreCurso = $c['nombre'] ?? '';
                $inscritos   = 0;
                try {
                    $stmtIns = $pdo->prepare("SELECT COUNT(*) FROM public.inscripciones WHERE cursos_seleccionados LIKE :nom");
                    $stmtIns->execute([':nom' => '%' . $nombreCurso . '%']);
                    $inscritos = intval($stmtIns->fetchColumn());
                } catch (Exception $e) {}
                $c['inscritos'] = $inscritos; // Asignación de cupos consumidos
            }
        }
    } catch (Exception $e) {}
}

$pageTitle = "Oferta Académica | Escuela Nora Castañeda";
require_once __DIR__ . '/includes/header.php';
?>

    <div class="container py-5 flex-grow-1">
        <div class="text-center mb-5">
            <h2 class="fw-extrabold" style="color: var(--color-vinotinto);">Cursos y Talleres Disponibles</h2>
            <p class="text-muted">Inscríbete en nuestros programas formativos de liderazgo y emprendimiento</p>
        </div>

        <div class="row g-4 justify-content-center">
            <?php if (!empty($cursosVigentes)): ?>
                <?php foreach ($cursosVigentes as $curso): ?>
                    <?php 
                        $hoy          = date('Y-m-d');
                        $fechaCierre  = !empty($curso['fecha_cierre']) ? $curso['fecha_cierre'] : ($curso['fecha_fin'] ?? null);
                        $inscritos    = intval($curso['inscritos'] ?? 0);
                        $cuposTotales = !empty($curso['cupos']) ? intval($curso['cupos']) : 50;
                        
                        // Flags de validación de disponibilidad
                        $estaVencido = ($fechaCierre && $fechaCierre !== '0000-00-00' && $hoy > $fechaCierre);
                        $estaLleno   = ($inscritos >= $cuposTotales);
                        
                        $nombreFoto = !empty($curso['flyer_imagen']) ? $curso['flyer_imagen'] : ($curso['flyer_path'] ?? '');
                        $fotoFlyer  = (!empty($nombreFoto) && file_exists(__DIR__ . '/uploads/' . $nombreFoto)) 
                                     ? 'uploads/' . $nombreFoto 
                                     : 'assets/img/emprendimiento.png';
                    ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 rounded-4 overflow-hidden bg-white shadow-sm h-100">
                            <div class="position-relative">
                                <img src="<?= $fotoFlyer ?>" class="w-100" style="height: 220px; object-fit: cover;" alt="Flyer Curso">
                                <div class="position-absolute top-0 start-0 p-3">
                                    <span class="badge bg-dark me-1"><?= htmlspecialchars($curso['modalidad'] ?? 'Presencial') ?></span>
                                    <?php if ($estaVencido): ?>
                                        <span class="badge bg-danger">CONVOCATORIA CERRADA</span>
                                    <?php elseif ($estaLleno): ?>
                                        <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i> CUPOS AGOTADOS</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">INSCRIPCIONES ABIERTAS</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card-body p-4 d-flex flex-column">
                                <h5 class="fw-bold mb-2" style="color: var(--color-vinotinto);"><?= htmlspecialchars($curso['nombre']) ?></h5>
                                <p class="text-muted small mb-3"><?= htmlspecialchars(substr($curso['descripcion'] ?? $curso['resena'] ?? '', 0, 110)) ?>...</p>
                                
                                <ul class="list-unstyled small text-secondary mb-4 flex-grow-1">
                                    <li class="mb-1"><i class="bi bi-geo-alt-fill text-danger me-2"></i><strong>Alcance:</strong> <?= htmlspecialchars($curso['alcance'] ?? 'Nacional') ?></li>
                                    <li class="mb-1"><i class="bi bi-calendar-check text-primary me-2"></i><strong>Días:</strong> <?= htmlspecialchars($curso['dias'] ?? 'Por definir') ?></li>
                                    <li class="mb-1"><i class="bi bi-clock text-warning me-2"></i><strong>Horario:</strong> <?= htmlspecialchars($curso['hora_inicio'] ?? '08:00 AM') ?> a <?= htmlspecialchars($curso['hora_fin'] ?? '12:00 PM') ?></li>
                                    <li class="mb-1"><i class="bi bi-pin-map-fill text-success me-2"></i><strong>Lugar:</strong> <?= htmlspecialchars($curso['lugar'] ?? 'Sede Principal') ?></li>
                                    <li><i class="bi bi-people-fill text-info me-2"></i><strong>Cupos:</strong> <?= $inscritos ?> / <?= $cuposTotales ?> inscritos</li>
                                </ul>

                                <!-- Botón condicional según vigencia/cupo -->
                                <?php if (!$estaVencido && !$estaLleno): ?>
                                    <button onclick="abrirFormularioInscripcion('<?= htmlspecialchars(addslashes($curso['nombre'])) ?>')" class="btn btn-naranja w-100 py-2">
                                        <i class="bi bi-pencil-square me-1"></i> Inscribirme Ahora
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary w-100 rounded-pill fw-bold" disabled>
                                        <?= $estaVencido ? 'Convocatoria Finalizada' : 'Cupos Agotados' ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-md-6 text-center py-5">
                    <div class="p-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-calendar-x fs-1 text-muted"></i>
                        <h4 class="fw-bold mt-3 text-dark">No hay ofertas académicas vigentes</h4>
                        <p class="text-muted">Próximamente estaremos publicando nuevos cursos y talleres.</p>
                        <a href="index.php" class="btn btn-naranja px-4 py-2 mt-2">Regresar al Inicio</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL POPUP: FORMULARIO DE INSCRIPCIÓN -->
    <div class="modal fade" id="modalInscripcion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header text-white" style="background-color: var(--color-vinotinto);">
                    <h5 class="modal-header-title fw-bold mb-0"><i class="bi bi-file-earmark-text me-2"></i> Formulario de Inscripción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formInscripcion" onsubmit="guardarRegistroAspirante(event)">
                    <div class="modal-body p-4">
                        
                        <!-- Panel de estado de consulta de Padrón -->
                        <div id="alertaEstadoConsultar" class="alert alert-info py-2 small fw-semibold mb-3">
                            <i class="bi bi-info-circle me-1"></i> Ingrese su cédula o pasaporte y presione <strong>Enter</strong> para consultar sus datos.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Nacionalidad</label>
                                <select id="regNacionalidad" class="form-select" onchange="evaluarNacionalidad()">
                                    <option value="V">Venezolano (V)</option>
                                    <option value="E">Extranjero (E)</option>
                                    <option value="P">Pasaporte (P)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">N° Cédula / Pasaporte</label>
                                <div class="position-relative">
                                    <input type="text" id="regCedula" class="form-control pe-5" placeholder="Ej: 12618989" onkeydown="detectarEnterCedula(event)" required>
                                    <span id="spinnerCargando" class="spinner-border spinner-border-sm text-primary position-absolute end-0 top-50 translate-middle-y me-3 d-none" role="status"></span>
                                </div>
                            </div>
                            
                            <div class="col-md-3" id="boxPaisOrigen" style="display: none;">
                                <label class="form-label fw-semibold">País de Origen</label>
                                <input type="text" id="regPais" class="form-control" value="Venezuela" disabled required>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nombres y Apellidos</label>
                                <input type="text" id="regNombre" class="form-control" placeholder="Complete con sus nombres" disabled required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Sexo</label>
                                <select id="regSexo" class="form-select" disabled required>
                                    <option value="">Seleccione...</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Masculino">Masculino</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Etnia / Pueblo Indígena</label>
                                <select id="regEtnia" class="form-select" disabled>
                                    <option value="">Ninguno / No pertenece</option>
                                    <?php foreach ($listaEtnias as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Discapacidad</label>
                                <select id="regDiscapacidad" class="form-select" disabled>
                                    <option value="">Ninguna</option>
                                    <?php foreach ($listaDiscapacidades as $d): ?>
                                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Fecha de Nacimiento</label>
                                <input type="date" id="regFechaNac" class="form-control" onchange="calcularEdadAuto()" disabled required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Edad Calculada</label>
                                <input type="number" id="regEdad" class="form-control bg-light" readonly placeholder="0">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Correo Electrónico Personal</label>
                                <input type="email" id="regCorreo" class="form-control" placeholder="ejemplo@correo.com" disabled required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Número de Teléfono</label>
                                <input type="tel" id="regTelefono" class="form-control" placeholder="0412-1234567" disabled required>
                            </div>

                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold">Curso Seleccionado</label>
                                <input type="text" id="regCursoNombre" class="form-control bg-light fw-bold text-primary" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" id="btnConfirmarInscripcion" class="btn btn-naranja px-4" disabled>
                            Confirmar Inscripción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
    <footer class="py-4 text-center footer-naranja mt-auto">
        <div class="container">
            <p class="mb-0 small opacity-90 fw-bold">&copy; 2026 Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda". Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Abrir y reiniciar el Modal de Inscripción
        function abrirFormularioInscripcion(nombreCurso) {
            document.getElementById('formInscripcion').reset();
            document.getElementById('regCursoNombre').value = nombreCurso;
            
            bloquearCamposFormulario(true); // Bloquea hasta que valide la cédula
            
            const alerta = document.getElementById('alertaEstadoConsultar');
            alerta.className = 'alert alert-info py-2 small fw-semibold mb-3';
            alerta.innerHTML = '<i class="bi bi-info-circle me-1"></i> Ingrese su cédula o pasaporte y presione <strong>Enter</strong> para consultar sus datos.';
            
            new bootstrap.Modal(document.getElementById('modalInscripcion')).show();
        }

        // Habilitar/Bloquear elementos del DOM
        function bloquearCamposFormulario(bloquear) {
            const IDs = ['regNombre', 'regSexo', 'regEtnia', 'regDiscapacidad', 'regFechaNac', 'regCorreo', 'regTelefono', 'regPais', 'btnConfirmarInscripcion'];
            IDs.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.disabled = bloquear;
            });
        }

        // Desplegar campo de País según nacionalidad
        function evaluarNacionalidad() {
            const nac = document.getElementById('regNacionalidad').value;
            const boxPais = document.getElementById('boxPaisOrigen');
            if (nac === 'E' || nac === 'P') {
                boxPais.style.display = 'block';
                if (document.getElementById('regPais').value === 'Venezuela') {
                    document.getElementById('regPais').value = '';
                }
            } else {
                boxPais.style.display = 'none';
                document.getElementById('regPais').value = 'Venezuela';
            }
        }

        // Captura del evento Tecla Enter
        function detectarEnterCedula(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                consultarCedulaSQLSilencioso();
            }
        }

        // Petición AJAX asíncrona hacia la API de Inscripciones
        function consultarCedulaSQLSilencioso() {
            const nac = document.getElementById('regNacionalidad').value;
            const ced = document.getElementById('regCedula').value.trim();
            const alerta = document.getElementById('alertaEstadoConsultar');
            const spinner = document.getElementById('spinnerCargando');

            if (!ced) return;

            spinner.classList.remove('d-none');
            alerta.className = 'alert alert-secondary py-2 small fw-semibold mb-3';
            alerta.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Consultando padrón nacional...';

            const formData = new FormData();
            formData.append('nacionalidad', nac);
            formData.append('cedula', ced);

            fetch('api_inscripcion.php?action=consultar_cedula', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                spinner.classList.add('d-none');
                bloquearCamposFormulario(false); // Habilitar formulario para ingresar datos de contacto

                if (data.success && data.encontrado) {
                    document.getElementById('regNombre').value   = data.datos.nombre_apellido || '';
                    document.getElementById('regFechaNac').value = data.datos.fecha_nacimiento || '';
                    document.getElementById('regEdad').value     = data.datos.edad || 0;
                    if (data.datos.sexo) document.getElementById('regSexo').value = data.datos.sexo;

                    alerta.className = 'alert alert-success py-2 small fw-semibold mb-3';
                    alerta.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Datos básicos cargados del padrón. Por favor ingrese su correo y teléfono.';
                } else {
                    alerta.className = 'alert alert-warning py-2 small fw-semibold mb-3';
                    alerta.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Documento no encontrado en el padrón. Por favor complete los datos manualmente.';
                }
            }).catch(() => {
                spinner.classList.add('d-none');
                bloquearCamposFormulario(false);
                alerta.className = 'alert alert-warning py-2 small fw-semibold mb-3';
                alerta.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> No se pudo conectar al padrón. Complete el formulario manualmente.';
            });
        }

        // Cálculo dinámico de edad en JS
        function calcularEdadAuto() {
            const fechaVal = document.getElementById('regFechaNac').value;
            if (!fechaVal) return;
            const nac = new Date(fechaVal);
            const hoy = new Date();
            let edad  = hoy.getFullYear() - nac.getFullYear();
            const m   = hoy.getMonth() - nac.getMonth();
            if (m < 0 || (m === 0 && hoy.getDate() < nac.getDate())) {
                edad--;
            }
            document.getElementById('regEdad').value = edad >= 0 ? edad : 0;
        }

        // Envío final del formulario hacia la API
        function guardarRegistroAspirante(e) {
            e.preventDefault();
            const form = new FormData();
            form.append('nacionalidad', document.getElementById('regNacionalidad').value);
            form.append('cedula', document.getElementById('regCedula').value);
            form.append('nombre_apellido', document.getElementById('regNombre').value);
            form.append('fecha_nacimiento', document.getElementById('regFechaNac').value);
            form.append('edad', document.getElementById('regEdad').value);
            form.append('sexo', document.getElementById('regSexo').value);
            form.append('pais_origen', document.getElementById('regPais').value);
            form.append('correo', document.getElementById('regCorreo').value);
            form.append('telefono', document.getElementById('regTelefono').value);
            form.append('cursos[]', document.getElementById('regCursoNombre').value);
            form.append('etnia_id', document.getElementById('regEtnia').value);
            form.append('discapacidad_id', document.getElementById('regDiscapacidad').value);

            fetch('api_inscripcion.php?action=guardar_registro', { method: 'POST', body: form })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    bootstrap.Modal.getInstance(document.getElementById('modalInscripcion')).hide();
                    document.getElementById('formInscripcion').reset();
                    location.reload();
                }
            });
        }
    </script>
</body>
</html>