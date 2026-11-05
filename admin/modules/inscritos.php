<?php
/**
 * ==============================================================================
 * MÓDULO DE GESTIÓN DE INSCRITOS
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * ==============================================================================
 */

if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }

$inscritosLista = [];
$estadisticasCursos = [];

if (isset($pdo) && $pdo !== null) {
    try {
        $stmtI = $pdo->query("SELECT * FROM public.inscripciones ORDER BY id DESC");
        if ($stmtI) $inscritosLista = $stmtI->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {}

    // Desglose de totales por curso
    foreach ($inscritosLista as $ins) {
        $cursoNombre = trim($ins['cursos_seleccionados'] ?? 'Sin Especificar');
        if (!isset($estadisticasCursos[$cursoNombre])) {
            $estadisticasCursos[$cursoNombre] = 0;
        }
        $estadisticasCursos[$cursoNombre]++;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0" style="color: var(--color-vinotinto);">Estudiantes Registradas</h3>
    <a href="exportar_excel.php" class="btn btn-success rounded-pill fw-bold px-4">
        <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
    </a>
</div>

<!-- MÉTRICAS Y DASHBOARD ESTADÍSTICO -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="p-3 bg-white rounded-4 shadow-sm border-start border-4 border-success">
            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Total Inscritas</span>
            <h3 class="fw-bold text-success mb-0" id="counterVisibles"><?= count($inscritosLista) ?></h3>
        </div>
    </div>
    <div class="col-md-8">
        <div class="p-3 bg-white rounded-4 shadow-sm border-start border-4 border-vinotinto">
            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Cursos con Registros</span>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($estadisticasCursos as $curso => $cant): ?>
                    <span class="badge bg-light text-dark border fw-bold p-2">
                        <?= htmlspecialchars($curso) ?>: <span class="badge bg-vinotinto ms-1"><?= $cant ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- FILTROS EN TIEMPO REAL -->
<div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-3">
    <div class="row g-2">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" id="inputBuscarInscritos" class="form-control border-0 bg-light" placeholder="Buscar por Cédula, Nombre o Teléfono..." onkeyup="filtrarTablaInscritos()">
            </div>
        </div>
        <div class="col-md-6">
            <select id="selectFiltroCurso" class="form-select border-0 bg-light fw-bold" onchange="filtrarTablaInscritos()">
                <option value="">Todos los Cursos / Talleres</option>
                <?php foreach ($estadisticasCursos as $curso => $cant): ?>
                    <option value="<?= htmlspecialchars($curso) ?>"><?= htmlspecialchars($curso) ?> (<?= $cant ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<!-- TABLA DE INSCRITAS -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tablaInscritos">
                <thead class="bg-light text-uppercase small fw-bold">
                    <tr>
                        <th class="ps-4">Cédula</th>
                        <th>Nombre y Apellido</th>
                        <th>Teléfono</th>
                        <th>Curso Seleccionado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($inscritosLista)): ?>
                        <?php foreach ($inscritosLista as $i): ?>
                            <tr class="fila-inscrita" data-curso="<?= htmlspecialchars($i['cursos_seleccionados']) ?>">
                                <td class="ps-4 fw-bold col-cedula"><?= $i['nacionalidad'] ?>-<?= $i['cedula'] ?></td>
                                <td class="col-nombre"><?= htmlspecialchars($i['nombre_apellido']) ?></td>
                                <td class="col-telefono"><?= htmlspecialchars($i['telefono']) ?></td>
                                <td class="col-curso"><span class="badge bg-light text-primary border fw-bold px-3 py-2"><?= htmlspecialchars($i['cursos_seleccionados']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">No existen estudiantes registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function filtrarTablaInscritos() {
        const texto = document.getElementById('inputBuscarInscritos').value.toLowerCase().trim();
        const cursoSeleccionado = document.getElementById('selectFiltroCurso').value.toLowerCase().trim();
        const filas = document.querySelectorAll('.fila-inscrita');
        let contadorVisibles = 0;

        filas.forEach(fila => {
            const cedula = fila.querySelector('.col-cedula').textContent.toLowerCase();
            const nombre = fila.querySelector('.col-nombre').textContent.toLowerCase();
            const telefono = fila.querySelector('.col-telefono').textContent.toLowerCase();
            const curso = fila.getAttribute('data-curso').toLowerCase();

            const coincideTexto = cedula.includes(texto) || nombre.includes(texto) || telefono.includes(texto);
            const coincideCurso = (cursoSeleccionado === '') || (curso === cursoSeleccionado);

            if (coincideTexto && coincideCurso) {
                fila.style.display = '';
                contadorVisibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        document.getElementById('counterVisibles').innerText = contadorVisibles;
    }
</script>