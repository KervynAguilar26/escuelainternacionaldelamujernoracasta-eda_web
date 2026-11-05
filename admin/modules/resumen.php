<?php
/**
 * MÓDULO DE RESUMEN Y MÉTRICAS GENERALES
 */
if (!defined('ACCESO_PERMITIDO')) { exit('Acceso directo no permitido'); }
?>

<h3 class="fw-bold mb-4" style="color: var(--color-vinotinto);">Gestión Global Institucional</h3>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="text-muted text-uppercase fw-bold small">Cursos Creados</h6>
            <h2 class="fw-bold text-dark mb-0"><?= count($cursosLista) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="text-muted text-uppercase fw-bold small">Foros Digitales</h6>
            <h2 class="fw-bold text-primary mb-0"><?= count($forosLista) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="text-muted text-uppercase fw-bold small">Noticias / Prensa</h6>
            <h2 class="fw-bold text-warning mb-0"><?= count($noticiasLista) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="text-muted text-uppercase fw-bold small">Recursos Educativos</h6>
            <h2 class="fw-bold text-info mb-0"><?= count($recursosLista) ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <h6 class="text-muted text-uppercase fw-bold small">Total Registrados</h6>
            <h2 class="fw-bold text-success mb-0"><?= count($inscritosLista) ?></h2>
        </div>
    </div>
</div>