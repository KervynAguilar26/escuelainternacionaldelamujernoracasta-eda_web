document.addEventListener('DOMContentLoaded', () => {

    // Validar tipo de archivo en inputs tipo file de imágenes
    const inputsImagen = document.querySelectorAll('input[type="file"][accept="image/*"]');
    inputsImagen.forEach(input => {
        input.addEventListener('change', (e) => {
            const archivo = e.target.files[0];
            if (archivo) {
                const tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];
                if (!tiposPermitidos.includes(archivo.type)) {
                    alert('Error: Solo se permiten archivos de imagen (.jpg, .png, .webp).');
                    e.target.value = '';
                }
            }
        });
    });

    const procesarFormularioAjax = (formId, actionName, modalId) => {
        const formElement = document.getElementById(formId);
        if (!formElement) return;

        formElement.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const formData = new FormData(formElement);
            if (!formData.has('action')) {
                formData.append('action', actionName);
            }

            fetch('api_admin.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                const toastAlert = document.getElementById('ajaxAlert');
                if (toastAlert) {
                    toastAlert.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show rounded-4 shadow-sm mb-4`;
                    document.getElementById('ajaxAlertText').innerText = data.message;
                    toastAlert.classList.remove('d-none');
                } else {
                    alert(data.message);
                }

                if (data.success) {
                    const modalEl = document.getElementById(modalId);
                    if (modalEl) {
                        const modalInst = bootstrap.Modal.getInstance(modalEl);
                        if (modalInst) modalInst.hide();
                    }
                    formElement.reset();
                    setTimeout(() => { location.reload(); }, 1000);
                }
            })
            .catch((err) => { alert('Ocurrió un error en la solicitud: ' + err); });
        });
    };

    procesarFormularioAjax('formNoticia', 'guardar_noticia', 'modalNuevaNoticia');
    procesarFormularioAjax('formEditarNoticia', 'editar_noticia', 'modalEditarNoticia');
});

function toggleEstadoNoticia(id, estadoActual) {
    const formData = new FormData();
    formData.append('action', 'toggle_publicado');
    formData.append('id', id);
    formData.append('estado', estadoActual);

    fetch('api_admin.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => { if (data.success) location.reload(); });
}

function abrirModalEditarNoticia(noticia) {
    document.getElementById('editNoticiaId').value = noticia.id;
    document.getElementById('editNoticiaTitulo').value = noticia.titulo;
    document.getElementById('editNoticiaPeriodista').value = noticia.periodista;
    document.getElementById('editNoticiaResumen').value = noticia.resumen;
    document.getElementById('editNoticiaContenido').value = noticia.contenido_completo;

    // Mostrar nombre del archivo e imagen de previsualización
    const nombreLabel = document.getElementById('editNoticiaImagenNombre');
    const previewImg = document.getElementById('editNoticiaImagenPreview');

    if (noticia.imagen_portada) {
        nombreLabel.innerText = "Archivo actual: " + noticia.imagen_portada;
        previewImg.src = "../public/uploads/" + noticia.imagen_portada;
        previewImg.style.display = "block";
    } else {
        nombreLabel.innerText = "Sin portada asignada";
        previewImg.style.display = "none";
    }

    new bootstrap.Modal(document.getElementById('modalEditarNoticia')).show();
}

function eliminarNoticia(id) {
    if (confirm('¿Está seguro de eliminar esta noticia?')) {
        const formData = new FormData();
        formData.append('action', 'eliminar_noticia');
        formData.append('id', id);

        fetch('api_admin.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
    }
}