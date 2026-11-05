<!-- Carga de Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JS Helper para Modales y AJAX -->
    <script>
        function obtenerOInstanciarModal(elementId) {
            const el = document.getElementById(elementId);
            if (!el) return null;
            let inst = bootstrap.Modal.getInstance(el);
            if (!inst) {
                inst = new bootstrap.Modal(el);
            }
            return inst;
        }
    </script>
    <script src="admin-ajax.js"></script>
</body>
</html>