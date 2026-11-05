<?php
/**
 * ==============================================================================
 * DASHBOARD Y PANEL DE CONTROL ADMINISTRATIVO
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * ==============================================================================
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ACCESO_PERMITIDO', true);

require_once __DIR__ . '/auth.php'; // Protegido por sesión
require_once __DIR__ . '/../config/database.php';

$rolUsuarioActual = $_SESSION['admin_rol'] ?? 'Control de Estudio';
$nombreUsuarioActual = $_SESSION['admin_nombre'] ?? 'Usuario Sistema';
$tabActiva = $_GET['tab'] ?? 'resumen';

// ==============================================================================
// 1. PROCESAMIENTO DE PETICIONES POST (FORMULARIOS)
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($pdo)) {
    $action = $_POST['action'];

    // --------------------------------------------------------------------------
    // A. MÓDULO CURSOS
    // --------------------------------------------------------------------------
    if ($action === 'guardar_curso') {
        $nombre      = trim($_POST['nombre'] ?? '');
        $modalidad   = $_POST['modalidad'] ?? 'Presencial';
        $alcance     = trim($_POST['alcance'] ?? '');
        $cupos       = intval($_POST['cupos'] ?? 30);
        $hora_ini    = $_POST['hora_inicio'] ?? '08:00';
        $hora_fin    = $_POST['hora_fin'] ?? '12:00';
        $f_inicio    = $_POST['fecha_inicio'] ?? date('Y-m-d');
        $f_cierre    = $_POST['fecha_cierre'] ?? date('Y-m-d');
        $lugar       = trim($_POST['lugar'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $flyer = '';
        if (isset($_FILES['flyer_imagen']) && $_FILES['flyer_imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['flyer_imagen']['name'], PATHINFO_EXTENSION);
            $flyer = 'flyer_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['flyer_imagen']['tmp_name'], __DIR__ . '/../public/uploads/' . $flyer);
        }

        $stmt = $pdo->prepare("INSERT INTO cursos (nombre, modalidad, alcance, cupos, hora_inicio, hora_fin, fecha_inicio, fecha_cierre, lugar, descripcion, flyer_imagen) 
                               VALUES (:nom, :mod, :alc, :cup, :hini, :hfin, :fini, :fcie, :lug, :desc, :fly)");
        $stmt->execute([
            'nom' => $nombre, 'mod' => $modalidad, 'alc' => $alcance, 'cup' => $cupos,
            'hini' => $hora_ini, 'hfin' => $hora_fin, 'fini' => $f_inicio, 'fcie' => $f_cierre,
            'lug' => $lugar, 'desc' => $descripcion, 'fly' => $flyer
        ]);
        header("Location: dashboard.php?tab=cursos");
        exit();
    }

    if ($action === 'editar_curso') {
        $id          = intval($_POST['id'] ?? 0);
        $nombre      = trim($_POST['nombre'] ?? '');
        $modalidad   = $_POST['modalidad'] ?? 'Presencial';
        $alcance     = trim($_POST['alcance'] ?? '');
        $cupos       = intval($_POST['cupos'] ?? 30);
        $hora_ini    = $_POST['hora_inicio'] ?? '08:00';
        $hora_fin    = $_POST['hora_fin'] ?? '12:00';
        $f_inicio    = $_POST['fecha_inicio'] ?? date('Y-m-d');
        $f_cierre    = $_POST['fecha_cierre'] ?? date('Y-m-d');
        $lugar       = trim($_POST['lugar'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (isset($_FILES['flyer_imagen']) && $_FILES['flyer_imagen']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['flyer_imagen']['name'], PATHINFO_EXTENSION);
            $flyer = 'flyer_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['flyer_imagen']['tmp_name'], __DIR__ . '/../public/uploads/' . $flyer);

            $stmt = $pdo->prepare("UPDATE cursos SET nombre=:nom, modalidad=:mod, alcance=:alc, cupos=:cup, hora_inicio=:hini, hora_fin=:hfin, fecha_inicio=:fini, fecha_cierre=:fcie, lugar=:lug, descripcion=:desc, flyer_imagen=:fly WHERE id=:id");
            $stmt->execute([
                'nom' => $nombre, 'mod' => $modalidad, 'alc' => $alcance, 'cup' => $cupos,
                'hini' => $hora_ini, 'hfin' => $hora_fin, 'fini' => $f_inicio, 'fcie' => $f_cierre,
                'lug' => $lugar, 'desc' => $descripcion, 'fly' => $flyer, 'id' => $id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE cursos SET nombre=:nom, modalidad=:mod, alcance=:alc, cupos=:cup, hora_inicio=:hini, hora_fin=:hfin, fecha_inicio=:fini, fecha_cierre=:fcie, lugar=:lug, descripcion=:desc WHERE id=:id");
            $stmt->execute([
                'nom' => $nombre, 'mod' => $modalidad, 'alc' => $alcance, 'cup' => $cupos,
                'hini' => $hora_ini, 'hfin' => $hora_fin, 'fini' => $f_inicio, 'fcie' => $f_cierre,
                'lug' => $lugar, 'desc' => $descripcion, 'id' => $id
            ]);
        }
        header("Location: dashboard.php?tab=cursos");
        exit();
    }

    if ($action === 'eliminar_curso') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard.php?tab=cursos");
        exit();
    }

    if ($action === 'toggle_curso') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE cursos SET publicado = NOT publicado WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard.php?tab=cursos");
        exit();
    }

    // --------------------------------------------------------------------------
    // B. MÓDULO FOROS
    // --------------------------------------------------------------------------
    if ($action === 'guardar_foro') {
        $titulo      = trim($_POST['titulo'] ?? '');
        $categoria   = $_POST['categoria'] ?? 'Formación General';
        $descripcion = trim($_POST['descripcion'] ?? '');

        $pdf = '';
        if (isset($_FILES['pdf_archivo']) && $_FILES['pdf_archivo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['pdf_archivo']['name'], PATHINFO_EXTENSION);
            $pdf = 'foro_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['pdf_archivo']['tmp_name'], __DIR__ . '/../public/uploads/' . $pdf);
        }

        $stmt = $pdo->prepare("INSERT INTO foros (titulo, categoria, descripcion, pdf_archivo) VALUES (:t, :c, :d, :p)");
        $stmt->execute(['t' => $titulo, 'c' => $categoria, 'd' => $descripcion, 'p' => $pdf]);
        header("Location: dashboard.php?tab=foros");
        exit();
    }

    if ($action === 'editar_foro') {
        $id          = intval($_POST['id'] ?? 0);
        $titulo      = trim($_POST['titulo'] ?? '');
        $categoria   = $_POST['categoria'] ?? 'Formación General';
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (isset($_FILES['pdf_archivo']) && $_FILES['pdf_archivo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['pdf_archivo']['name'], PATHINFO_EXTENSION);
            $pdf = 'foro_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['pdf_archivo']['tmp_name'], __DIR__ . '/../public/uploads/' . $pdf);

            $stmt = $pdo->prepare("UPDATE foros SET titulo=:t, categoria=:c, descripcion=:d, pdf_archivo=:p WHERE id=:id");
            $stmt->execute(['t' => $titulo, 'c' => $categoria, 'd' => $descripcion, 'p' => $pdf, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE foros SET titulo=:t, categoria=:c, descripcion=:d WHERE id=:id");
            $stmt->execute(['t' => $titulo, 'c' => $categoria, 'd' => $descripcion, 'id' => $id]);
        }
        header("Location: dashboard.php?tab=foros");
        exit();
    }

    if ($action === 'eliminar_foro') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM foros WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard.php?tab=foros");
        exit();
    }

    // --------------------------------------------------------------------------
    // C. MÓDULO NOTICIAS / PRENSA
    // --------------------------------------------------------------------------
    if ($action === 'guardar_noticia') {
        $titulo      = trim($_POST['titulo'] ?? '');
        $periodista  = trim($_POST['periodista'] ?? 'Prensa MinMujer');
        $resumen     = trim($_POST['resumen'] ?? '');
        $contenido   = trim($_POST['contenido_completo'] ?? '');

        $img = '';
        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION);
            $img = 'noticia_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['imagen_portada']['tmp_name'], __DIR__ . '/../public/uploads/' . $img);
        }

        $stmt = $pdo->prepare("INSERT INTO portal_noticias (titulo, periodista, resumen, contenido_completo, imagen_portada) VALUES (:t, :p, :r, :c, :i)");
        $stmt->execute(['t' => $titulo, 'p' => $periodista, 'r' => $resumen, 'c' => $contenido, 'i' => $img]);
        header("Location: dashboard.php?tab=comunicacion");
        exit();
    }

    if ($action === 'editar_noticia') {
        $id         = intval($_POST['id'] ?? 0);
        $titulo     = trim($_POST['titulo'] ?? '');
        $periodista = trim($_POST['periodista'] ?? 'Prensa MinMujer');
        $resumen    = trim($_POST['resumen'] ?? '');
        $contenido  = trim($_POST['contenido_completo'] ?? '');

        if (isset($_FILES['imagen_portada']) && $_FILES['imagen_portada']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['imagen_portada']['name'], PATHINFO_EXTENSION);
            $img = 'noticia_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['imagen_portada']['tmp_name'], __DIR__ . '/../public/uploads/' . $img);

            $stmt = $pdo->prepare("UPDATE portal_noticias SET titulo=:t, periodista=:p, resumen=:r, contenido_completo=:c, imagen_portada=:i WHERE id=:id");
            $stmt->execute(['t' => $titulo, 'p' => $periodista, 'r' => $resumen, 'c' => $contenido, 'i' => $img, 'id' => $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE portal_noticias SET titulo=:t, periodista=:p, resumen=:r, contenido_completo=:c WHERE id=:id");
            $stmt->execute(['t' => $titulo, 'p' => $periodista, 'r' => $resumen, 'c' => $contenido, 'id' => $id]);
        }
        header("Location: dashboard.php?tab=comunicacion");
        exit();
    }

    if ($action === 'eliminar_noticia') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM portal_noticias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard.php?tab=comunicacion");
        exit();
    }

    // --------------------------------------------------------------------------
    // D. MÓDULO GALERÍAS FOTOGRÁFICAS
    // --------------------------------------------------------------------------
    if ($action === 'guardar_galeria') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        if (!empty($titulo)) {
            $stmt = $pdo->prepare("INSERT INTO galerias (titulo, descripcion) VALUES (:t, :d)");
            $stmt->execute(['t' => $titulo, 'd' => $descripcion]);
            $galeriaId = $pdo->lastInsertId();

            if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $dirUploads = __DIR__ . '/../public/uploads/galerias/';
                if (!file_exists($dirUploads)) @mkdir($dirUploads, 0777, true);

                foreach ($_FILES['fotos']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['fotos']['error'][$key] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['fotos']['name'][$key], PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                            $nombreArchivo = 'foto_' . uniqid() . '_' . time() . '.' . $ext;
                            if (move_uploaded_file($tmpName, $dirUploads . $nombreArchivo)) {
                                $stmtFoto = $pdo->prepare("INSERT INTO galeria_fotos (galeria_id, archivo) VALUES (:gid, :arc)");
                                $stmtFoto->execute(['gid' => $galeriaId, 'arc' => $nombreArchivo]);
                            }
                        }
                    }
                }
            }
        }
        header("Location: dashboard.php?tab=galerias");
        exit();
    }

    if ($action === 'editar_galeria') {
        $id = intval($_POST['id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');

        $stmt = $pdo->prepare("UPDATE galerias SET titulo = :t, descripcion = :d WHERE id = :id");
        $stmt->execute(['t' => $titulo, 'd' => $descripcion, 'id' => $id]);

        if (isset($_FILES['fotos_nuevas']) && !empty($_FILES['fotos_nuevas']['name'][0])) {
            $dirUploads = __DIR__ . '/../public/uploads/galerias/';
            if (!file_exists($dirUploads)) @mkdir($dirUploads, 0777, true);

            foreach ($_FILES['fotos_nuevas']['tmp_name'] as $key => $tmpName) {
                if ($_FILES['fotos_nuevas']['error'][$key] === UPLOAD_ERR_OK) {
                    $ext = strtolower(pathinfo($_FILES['fotos_nuevas']['name'][$key], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                        $nombreArchivo = 'foto_' . uniqid() . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($tmpName, $dirUploads . $nombreArchivo)) {
                            $stmtFoto = $pdo->prepare("INSERT INTO galeria_fotos (galeria_id, archivo) VALUES (:gid, :arc)");
                            $stmtFoto->execute(['gid' => $id, 'arc' => $nombreArchivo]);
                        }
                    }
                }
            }
        }
        header("Location: dashboard.php?tab=galerias");
        exit();
    }

    if ($action === 'eliminar_galeria') {
        $id = intval($_POST['id'] ?? 0);

        $stmtFotos = $pdo->prepare("SELECT archivo FROM galeria_fotos WHERE galeria_id = :gid");
        $stmtFotos->execute(['gid' => $id]);
        while ($f = $stmtFotos->fetch(PDO::FETCH_ASSOC)) {
            $path = __DIR__ . '/../public/uploads/galerias/' . $f['archivo'];
            if (file_exists($path)) @unlink($path);
        }

        $stmt = $pdo->prepare("DELETE FROM galerias WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard.php?tab=galerias");
        exit();
    }

    // --------------------------------------------------------------------------
    // E. MÓDULO RECURSOS DIGITALES
    // --------------------------------------------------------------------------
    if ($action === 'guardar_recurso') {
        $titulo      = trim($_POST['titulo'] ?? '');
        $tipo        = $_POST['tipo_recurso'] ?? 'Guía Formativa';
        $descripcion = trim($_POST['descripcion'] ?? '');

        $arc = '';
        if (isset($_FILES['archivo_recurso']) && $_FILES['archivo_recurso']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['archivo_recurso']['name'], PATHINFO_EXTENSION);
            $arc = 'recurso_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['archivo_recurso']['tmp_name'], __DIR__ . '/../public/uploads/' . $arc);
        }

        $stmt = $pdo->prepare("INSERT INTO recursos_digitales (titulo, tipo_recurso, descripcion, archivo) VALUES (:t, :tr, :d, :a)");
        $stmt->execute(['t' => $titulo, 'tr' => $tipo, 'd' => $descripcion, 'a' => $arc]);
        header("Location: dashboard.php?tab=recursos");
        exit();
    }

    if ($action === 'eliminar_recurso') {
        $id = intval($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM recursos_digitales WHERE id = :id");
        $stmt->execute(['id' => $id]);
        header("Location: dashboard.php?tab=recursos");
        exit();
    }

    // --------------------------------------------------------------------------
    // F. MÓDULO USUARIOS ADMIN
    // --------------------------------------------------------------------------
    if ($action === 'guardar_usuario' && $rolUsuarioActual === 'Superadministrador') {
        $cedula  = trim($_POST['cedula'] ?? '');
        $nombre  = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $pass    = $_POST['password'] ?? '';
        $rol     = $_POST['rol'] ?? 'Control de Estudio';
        $p1      = trim($_POST['pregunta_1'] ?? '');
        $r1      = password_hash(strtolower(trim($_POST['respuesta_1'] ?? '')), PASSWORD_BCRYPT);
        $p2      = trim($_POST['pregunta_2'] ?? '');
        $r2      = password_hash(strtolower(trim($_POST['respuesta_2'] ?? '')), PASSWORD_BCRYPT);

        $passHash = password_hash($pass, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios_admin (cedula, nombre, usuario, email, password_hash, rol, pregunta_seguridad_1, respuesta_seguridad_1, pregunta_seguridad_2, respuesta_seguridad_2) 
                                   VALUES (:c, :n, :u, :e, :p, :r, :p1, :r1, :p2, :r2)");
            $stmt->execute(['c' => $cedula, 'n' => $nombre, 'u' => $usuario, 'e' => $email, 'p' => $passHash, 'r' => $rol, 'p1' => $p1, 'r1' => $r1, 'p2' => $p2, 'r2' => $r2]);
            header("Location: dashboard.php?tab=usuarios");
            exit();
        } catch (Exception $e) { 
            header("Location: dashboard.php?tab=usuarios&err=1");
            exit(); 
        }
    }

    if ($action === 'editar_usuario' && $rolUsuarioActual === 'Superadministrador') {
        $id      = intval($_POST['id'] ?? 0);
        $cedula  = trim($_POST['cedula'] ?? '');
        $nombre  = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $rol     = $_POST['rol'] ?? 'Control de Estudio';

        try {
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET cedula = :c, nombre = :n, usuario = :u, email = :e, rol = :r WHERE id = :id");
            $stmt->execute(['c' => $cedula, 'n' => $nombre, 'u' => $usuario, 'e' => $email, 'r' => $rol, 'id' => $id]);
            header("Location: dashboard.php?tab=usuarios");
            exit();
        } catch (Exception $e) {
            header("Location: dashboard.php?tab=usuarios&err=1");
            exit();
        }
    }

    if ($action === 'admin_reset_password' && $rolUsuarioActual === 'Superadministrador') {
        $id        = intval($_POST['id'] ?? 0);
        $nuevaPass = $_POST['nueva_password'] ?? '';

        if (strlen($nuevaPass) >= 6) {
            $passHash = password_hash($nuevaPass, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE usuarios_admin SET password_hash = :p WHERE id = :id");
            $stmt->execute(['p' => $passHash, 'id' => $id]);
        }
        header("Location: dashboard.php?tab=usuarios");
        exit();
    }

    if ($action === 'actualizar_mi_perfil') {
        $myId         = $_SESSION['admin_id'] ?? 0;
        $nuevoUsuario = trim($_POST['usuario'] ?? '');
        $nuevaPass    = $_POST['nueva_password'] ?? '';

        if ($myId > 0 && !empty($nuevoUsuario)) {
            if (!empty($nuevaPass) && strlen($nuevaPass) >= 6) {
                $passHash = password_hash($nuevaPass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = :u, password_hash = :p WHERE id = :id");
                $stmt->execute(['u' => $nuevoUsuario, 'p' => $passHash, 'id' => $myId]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios_admin SET usuario = :u WHERE id = :id");
                $stmt->execute(['u' => $nuevoUsuario, 'id' => $myId]);
            }
            $_SESSION['admin_usuario'] = $nuevoUsuario;
        }
        header("Location: dashboard.php");
        exit();
    }
}

// ==============================================================================
// 2. CONSULTAS GLOBALES DE REGISTROS
// ==============================================================================
$forosLista = [];
$cursosLista = [];
$inscritosLista = [];
$noticiasLista = [];
$recursosLista = [];
$usuariosLista = [];

if (isset($pdo) && $pdo !== null) {
    try { $forosLista = $pdo->query("SELECT f.*, (SELECT COUNT(*) FROM foro_comentarios c WHERE c.foro_id = f.id) as total_comentarios FROM foros f ORDER BY f.id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
    try { $cursosLista = $pdo->query("SELECT * FROM cursos ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
    try { $inscritosLista = $pdo->query("SELECT * FROM inscripciones ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
    try { $noticiasLista = $pdo->query("SELECT * FROM portal_noticias ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
    try { $recursosLista = $pdo->query("SELECT * FROM recursos_digitales ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
    
    if ($rolUsuarioActual === 'Superadministrador') {
        try { $usuariosLista = $pdo->query("SELECT id, cedula, nombre, usuario, email, rol, activo, creado_en FROM usuarios_admin ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC); } catch (Exception $e) {}
    }
}

// INCLUIR LAYOUT BASE (Encabezado y Barra Lateral)
require_once __DIR__ . '/templates/header.php';
require_once __DIR__ . '/templates/sidebar.php';
?>

<!-- CONTENIDO CENTRAL DE LOS MÓDULOS -->
<div class="main-content">
    <div class="tab-content" id="adminTabsContent">
        
        <!-- MÓDULO RESUMEN -->
        <div class="tab-pane fade <?= $tabActiva === 'resumen' ? 'show active' : '' ?>" id="secResumen" role="tabpanel">
            <?php include_once __DIR__ . '/modules/resumen.php'; ?>
        </div>

        <!-- MÓDULO CURSOS -->
        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Control de Estudio'], true)): ?>
        <div class="tab-pane fade <?= $tabActiva === 'cursos' ? 'show active' : '' ?>" id="secCursos" role="tabpanel">
            <?php include_once __DIR__ . '/modules/cursos.php'; ?>
        </div>
        <?php endif; ?>

        <!-- MÓDULO FOROS -->
        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Profesor'], true)): ?>
        <div class="tab-pane fade <?= $tabActiva === 'foros' ? 'show active' : '' ?>" id="secForos" role="tabpanel">
            <?php include_once __DIR__ . '/modules/foros.php'; ?>
        </div>
        <?php endif; ?>

        <!-- MÓDULO COMUNICACIÓN / PRENSA Y GALERÍAS -->
        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Prensa', 'Viceministerio'], true)): ?>
        <div class="tab-pane fade <?= $tabActiva === 'comunicacion' ? 'show active' : '' ?>" id="secComunicacion" role="tabpanel">
            <?php include_once __DIR__ . '/modules/noticias.php'; ?>
        </div>
        <div class="tab-pane fade <?= $tabActiva === 'galerias' ? 'show active' : '' ?>" id="secGalerias" role="tabpanel">
            <?php include_once __DIR__ . '/modules/galerias_admin.php'; ?>
        </div>
        <?php endif; ?>

        <!-- MÓDULO RECURSOS DIGITALES -->
        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Profesor'], true)): ?>
        <div class="tab-pane fade <?= $tabActiva === 'recursos' ? 'show active' : '' ?>" id="secRecursos" role="tabpanel">
            <?php include_once __DIR__ . '/modules/recursos.php'; ?>
        </div>
        <?php endif; ?>

        <!-- MÓDULO INSCRITOS -->
        <?php if (in_array($rolUsuarioActual, ['Superadministrador', 'Viceministerio', 'Control de Estudio'], true)): ?>
        <div class="tab-pane fade <?= $tabActiva === 'inscritos' ? 'show active' : '' ?>" id="secInscritos" role="tabpanel">
            <?php include_once __DIR__ . '/modules/inscritos.php'; ?>
        </div>
        <?php endif; ?>

        <!-- MÓDULO USUARIOS (Exclusivo Superadministrador) -->
        <?php if ($rolUsuarioActual === 'Superadministrador'): ?>
        <div class="tab-pane fade <?= $tabActiva === 'usuarios' ? 'show active' : '' ?>" id="secUsuarios" role="tabpanel">
            <?php include_once __DIR__ . '/modules/usuarios.php'; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php 
// INCLUIR PIE DE PÁGINA (Carga JS y cierre HTML)
require_once __DIR__ . '/templates/footer.php'; 
?>