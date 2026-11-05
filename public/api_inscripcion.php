<?php
/**
 * ==============================================================================
 * MÓDULO API DE INSCRIPCIÓN, VERIFICACIÓN DE PADRÓN Y PREVENCIÓN DE DUPLICADOS
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Maneja las solicitudes AJAX para la verificación de identidad en 
 *              el padrón público y el procesamiento seguro de inscripciones
 *              con notificación automatizada por correo electrónico (PHPMailer).
 * Auditoría:
 *  - Encabezados JSON estrictos para prevención de MIME-sniffing.
 *  - Consultas preparadas PDO contra inyecciones SQL.
 *  - Control de duplicados en inscripciones por cédula + curso.
 *  - Integración SMTP con Gmail con manejo de excepciones.
 * ==============================================================================
 */

// Definición de cabeceras de respuesta JSON y seguridad HTTP
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff'); // Previene ataques de lectura incorrecta de tipos MIME

// Carga de la conexión centralizada a la base de datos
$database_path = __DIR__ . '/../config/database.php';
if (file_exists($database_path)) {
    require_once $database_path;
} else {
    // Respuesta de falla inmediata si no se localiza el archivo de configuración BD
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// Carga modular de las clases del conector PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$phpmailerPath = __DIR__ . '/../libs/PHPMailer/src/';
if (file_exists($phpmailerPath . 'PHPMailer.php')) {
    require_once $phpmailerPath . 'Exception.php';
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
}

// Captura del parámetro de acción mediante GET
$action = $_GET['action'] ?? '';

/**
 * ------------------------------------------------------------------------------
 * ACCIÓN 1: CONSULTA SILENCIOSA DE PADRÓN CIUDADANO
 * Propósito: Buscar los datos filiales de la persona en la tabla "public.datos"
 *            para precargar nombres, sexo, fecha de nacimiento y edad calculada.
 * ------------------------------------------------------------------------------
 */
if ($action === 'consultar_cedula') {
    // Sanitización y normalización de parámetros recibidos por POST
    $nacionalidad   = strtoupper(trim($_POST['nacionalidad'] ?? 'V'));
    $cedulaOriginal = trim($_POST['cedula'] ?? '');
    $cedulaLimpia   = preg_replace('/[^a-zA-Z0-9]/', '', $cedulaOriginal); // Elimina caracteres especiales

    if (empty($cedulaLimpia)) {
        echo json_encode(['success' => false, 'message' => 'Ingrese una cédula o pasaporte válido.']);
        exit;
    }

    try {
        if (!isset($pdo) || $pdo === null) {
            throw new Exception("Sin conexión a BD.");
        }

        // Consulta preparada para buscar por coincidencia exacta de documento y nacionalidad
        $sql = "SELECT first_name, second_name, first_surname, second_surname, birth_date, genero 
                FROM public.datos 
                WHERE UPPER(TRIM(nac)) = :nac 
                  AND (document = :ced_int OR CAST(document AS VARCHAR) = :ced_str)
                LIMIT 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':nac', $nacionalidad, PDO::PARAM_STR);
        $stmt->bindValue(':ced_int', is_numeric($cedulaLimpia) ? intval($cedulaLimpia) : 0, PDO::PARAM_INT);
        $stmt->bindValue(':ced_str', $cedulaLimpia, PDO::PARAM_STR);
        $stmt->execute();
        
        $persona = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($persona) {
            // Concatenación limpia de los nombres y apellidos encontrados
            $partesNombre = array_filter([
                $persona['first_name'] ?? '',
                $persona['second_name'] ?? '',
                $persona['first_surname'] ?? '',
                $persona['second_surname'] ?? ''
            ], function($v) { return trim($v) !== ''; });

            $nombreCompleto = implode(' ', $partesNombre);
            $fechaNac       = $persona['birth_date'] ?? '';

            // Mapeo estandarizado del género
            $gen         = strtoupper(trim($persona['genero'] ?? ''));
            $sexoMapeado = ($gen === 'F' || $gen === 'FEMENINO') ? 'Femenino' : (($gen === 'M' || $gen === 'MASCULINO') ? 'Masculino' : '');

            // Cálculo automatizado de la edad según la fecha de nacimiento del padrón
            $edad = 0;
            if (!empty($fechaNac)) {
                try {
                    $cumple = new DateTime($fechaNac);
                    $hoy    = new DateTime();
                    $edad   = $hoy->diff($cumple)->y;
                } catch (Exception $eDate) { $edad = 0; }
            }

            // Retorno exitoso en formato JSON
            echo json_encode([
                'success'    => true,
                'encontrado' => true,
                'datos'      => [
                    'nombre_apellido'  => trim($nombreCompleto),
                    'fecha_nacimiento' => $fechaNac,
                    'edad'             => $edad,
                    'sexo'             => $sexoMapeado
                ]
            ]);
        } else {
            // Documento no hallado en el padrón
            echo json_encode(['success' => true, 'encontrado' => false]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'encontrado' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * ------------------------------------------------------------------------------
 * ACCIÓN 2: GUARDAR INSCRIPCIÓN Y PREVENIR REGISTROS DUPLICADOS
 * Propósito: Validar requeridos, chequear que la cédula no esté inscrita en el
 *            mismo curso, almacenar en "public.inscripciones" y enviar correo.
 * ------------------------------------------------------------------------------
 */
if ($action === 'guardar_registro') {
    // Limpieza de inputs enviados por el aspirante
    $nacionalidad = strtoupper(trim($_POST['nacionalidad'] ?? 'V'));
    $cedula       = preg_replace('/[^a-zA-Z0-9]/', '', $_POST['cedula'] ?? '');
    $nombre       = trim($_POST['nombre_apellido'] ?? '');
    $fechaNac     = !empty($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : null;
    $edad         = intval($_POST['edad'] ?? 0);
    $sexo         = trim($_POST['sexo'] ?? '');
    $pais         = trim($_POST['pais_origen'] ?? 'Venezuela');
    $correo       = trim($_POST['correo'] ?? '');
    $telefono     = trim($_POST['telefono'] ?? '');
    
    $cursosInput  = $_POST['cursos'] ?? [];
    $cursosLimpio = is_array($cursosInput) ? implode(', ', $cursosInput) : trim($cursosInput);

    // Claves foráneas opcionales (Etnia y Discapacidad)
    $etnia_id        = (!empty($_POST['etnia_id']) && is_numeric($_POST['etnia_id'])) ? intval($_POST['etnia_id']) : null;
    $discapacidad_id = (!empty($_POST['discapacidad_id']) && is_numeric($_POST['discapacidad_id'])) ? intval($_POST['discapacidad_id']) : null;

    // Control de campos obligatorios
    if (empty($cedula) || empty($nombre) || empty($correo) || empty($telefono) || empty($cursosLimpio)) {
        echo json_encode(['success' => false, 'message' => 'Por favor complete todos los campos requeridos.']);
        exit;
    }

    try {
        if (!isset($pdo) || $pdo === null) {
            throw new Exception("Sin conexión a BD.");
        }

        // VALIDACIÓN ANTI-DUPLICADOS: Evita inscripciones múltiples en la misma oferta
        $sqlCheck = "SELECT COUNT(*) FROM public.inscripciones 
                     WHERE nacionalidad = :nac 
                       AND cedula = :ced 
                       AND cursos_seleccionados = :curso";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([
            ':nac'   => $nacionalidad,
            ':ced'   => $cedula,
            ':curso' => $cursosLimpio
        ]);

        if ($stmtCheck->fetchColumn() > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'Atención: Usted ya se encuentra registrada(o) en el curso: "' . htmlspecialchars($cursosLimpio) . '".'
            ]);
            exit;
        }

        // Inserción segura de los datos del participante
        $sql = "INSERT INTO public.inscripciones 
                (nacionalidad, cedula, nombre_apellido, fecha_nacimiento, edad, sexo, pais_origen, correo, telefono, cursos_seleccionados, etnia_id, discapacidad_id) 
                VALUES (:nac, :ced, :nom, :fec, :edad, :sexo, :pais, :correo, :tel, :cursos, :etnia, :discapacidad)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nac'          => $nacionalidad,
            ':ced'          => $cedula,
            ':nom'          => $nombre,
            ':fec'          => $fechaNac,
            ':edad'         => $edad,
            ':sexo'         => $sexo,
            ':pais'         => $pais,
            ':correo'       => $correo,
            ':tel'          => $telefono,
            ':cursos'       => $cursosLimpio,
            ':etnia'        => $etnia_id,
            ':discapacidad' => $discapacidad_id
        ]);

        // Disparo automatizado de comprobante vía correo electrónico
        enviarCorreoConfirmacion($correo, $nombre, $nacionalidad . '-' . $cedula, $cursosLimpio);

        echo json_encode([
            'success' => true, 
            'message' => '¡Inscripción registrada exitosamente! Se ha enviado el comprobante a su correo electrónico.'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar el registro: ' . $e->getMessage()]);
    }
    exit;
}

/**
 * ------------------------------------------------------------------------------
 * FUNCIÓN AUXILIAR: ENVÍO DE CORREO DE CONFIRMACIÓN CON PHPMANILER
 * ------------------------------------------------------------------------------
 */
function enviarCorreoConfirmacion($correoDestino, $nombreUsuario, $cedulaUsuario, $nombreCurso) {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) return false;

    $mail = new PHPMailer(true);
    try {
        // Configuración del Servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tecnologianoracastaneda@gmail.com'; 
        $mail->Password   = 'niqxrnncvjyxbavn'; // Contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Bypass de certificados en entorno de desarrollo local
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            )
        );

        // Remitente y Destinatario
        $mail->setFrom('tecnologianoracastaneda@gmail.com', 'Escuela Nora Castañeda');
        $mail->addAddress($correoDestino, $nombreUsuario);

        // Plantilla HTML del Comprobante
        $mail->isHTML(true);
        $mail->Subject = 'Comprobante de Inscripción - Escuela Nora Castañeda';
        $fechaHoy = date('d/m/Y h:i A');

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; overflow: hidden; background-color: #fdf8f3;'>
            <div style='background-color: #521f29; padding: 20px; text-align: center;'>
                <h2 style='color: #ffffff; margin: 0; font-size: 18px;'>ESCUELA INTERNACIONAL DE LIDERAZGO DE LA MUJER</h2>
                <h3 style='color: #e5b871; margin: 5px 0 0 0; font-size: 16px;'>\"NORA CASTAÑEDA\"</h3>
            </div>
            <div style='padding: 25px; color: #333333;'>
                <h3 style='color: #ff8a00; margin-top: 0;'>¡Comprobante de Inscripción Exitoso!</h3>
                <p>Estimado(a) <strong>{$nombreUsuario}</strong>,</p>
                <p>Su inscripción ha sido procesada satisfactoriamente.</p>
                <div style='background-color: #ffffff; border-left: 4px solid #ff8a00; padding: 15px; margin: 20px 0; border-radius: 4px;'>
                    <p style='margin: 3px 0;'><strong>Documento:</strong> {$cedulaUsuario}</p>
                    <p style='margin: 3px 0;'><strong>Curso / Taller:</strong> <span style='color: #521f29;'>{$nombreCurso}</span></p>
                    <p style='margin: 3px 0;'><strong>Fecha:</strong> {$fechaHoy}</p>
                </div>
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) { return false; }
}