document.addEventListener('DOMContentLoaded', () => {

    // Manejador genérico para envíos mediante AJAX (Fetch API)
    const procesarFormularioAjax = (formId, actionName, modalId) => {
        const formElement = document.getElementById(formId);
        if (!formElement) return;

        formElement.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(formElement);
            formData.append('action', actionName);

            fetch('api_admin.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const toastAlert = document.getElementById('ajaxAlert');
                toastAlert.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show rounded-4 shadow-sm`;
                document.getElementById('ajaxAlertText').innerText = data.message;
                toastAlert.classList.remove('d-none');

                if (data.success) {
                    // Cerrar modal
                    const modalInst = bootstrap.Modal.getInstance(document.getElementById(modalId));
                    if (modalInst) modalInst.hide();
                    formElement.reset();
                    
                    // Recargar datos suavemente en 1 segundo
                    setTimeout(() => { location.reload(); }, 1200);
                }
            })
            .catch(() => {
                alert('Ocurrió un error al procesar la solicitud AJAX.');
            });
        });
    };

    // Registrar envíos AJAX de cada modal
    procesarFormularioAjax('formNoticia', 'guardar_noticia', 'modalNuevaNoticia');
    procesarFormularioAjax('formCurso', 'guardar_curso', 'modalNuevoCurso');
    procesarFormularioAjax('formForo', 'guardar_foro', 'modalNuevoForo');
    procesarFormularioAjax('formRecurso', 'guardar_recurso', 'modalNuevoRecurso');
    procesarFormularioAjax('formCurso', 'guardar_curso', 'modalNuevoCurso');
procesarFormularioAjax('formEditarCurso', 'editar_curso', 'modalEditarCurso');

function toggleEstadoCurso(id, estadoActual) {
    const formData = new FormData();
    formData.append('action', 'toggle_publicado_curso');
    formData.append('id', id);
    formData.append('estado', estadoActual);

    fetch('api_admin.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => { if (data.success) location.reload(); });
}

function eliminarCurso(id) {
    if (confirm('¿Está seguro de eliminar este curso?')) {
        const formData = new FormData();
        formData.append('action', 'eliminar_curso');
        formData.append('id', id);

        fetch('api_admin.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
    }
}

function adaptarAlcanceModalidad() {
    const modalidad = document.getElementById('cursoModalidad').value;
    const selectAlcance = document.getElementById('cursoAlcance');
    const inputLugar = document.getElementById('cursoLugar');

    if (modalidad === 'Virtual') {
        selectAlcance.value = 'Nacional / Internacional (Abierto a todo el público)';
        inputLugar.value = 'Plataforma Virtual (Moodle / Aula Virtual)';
    } else {
        selectAlcance.value = 'Solo para residentes de Maracay (Edo. Aragua)';
        inputLugar.value = 'Sede Principal Bello Monte, Maracay';
    }
}
});