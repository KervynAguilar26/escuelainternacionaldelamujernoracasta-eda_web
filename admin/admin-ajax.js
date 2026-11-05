/**
 * HELPER PARA MODALES BOOTSTRAP 5 Y LLAMADAS JS
 */
function obtenerOInstanciarModal(modalId) {
    const el = document.getElementById(modalId);
    if (!el) return null;
    return bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
}

document.addEventListener('DOMContentLoaded', function () {
    console.log('Script de administración cargado correctamente.');
});