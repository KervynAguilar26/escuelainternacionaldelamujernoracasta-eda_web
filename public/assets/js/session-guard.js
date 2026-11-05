// Envía una solicitud beacon cuando el usuario cierra la pestaña/navegador
window.addEventListener('beforeunload', function (event) {
    navigator.sendBeacon('logout_beacon.php');
});