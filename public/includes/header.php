<?php
/**
 * ==============================================================================
 * PLANTILLA DE ENCABEZADO Y BARRA DE NAVEGACIÓN (HEADER)
 * Sistema: Escuela Internacional de Liderazgo de la Mujer "Nora Castañeda"
 * Descripción: Incluye las hojas de estilo globales (Bootstrap 5, Icons, Fonts),
 *              viñeta tricolor, cintillo del ministerio y la barra de navegación.
 * Auditoría:
 *  - Título dinámico por vista mediante `$pageTitle`.
 *  - Definición de paleta institucional mediante CSS Variables (:root).
 * ==============================================================================
 */
$tituloPagina = isset($pageTitle) ?$pageTitle : 'Escuela Internacional de Liderazgo "Nora Castañeda"';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?></title>

    <!-- Favicon Institucional -->
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/Logo 16 x16.png">

    <!-- Bootstrap 5 CSS, Bootstrap Icons y Tipografía Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Estilos CSS Institucionales Centralizados -->
    <style>
        :root {
            --color-vinotinto:     #521f29;
            --color-vinotinto-dark:#3b151d;
            --color-crema:         #eecfb1;
            --color-crema-suave:   #fdf8f3;
            --color-dorado:        #e5b871;
            --color-naranja:       #ff8a00;
            --color-naranja-hover: #e07a00;
            --bandera-amarillo:    #ffcc00;
            --bandera-azul:        #2147de;
            --bandera-rojo:        #cd1e3e;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #ffffff; 
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Viñeta Tricolor Superior */
        .barra-bandera { height: 5px; width: 100%; display: flex; position: fixed; top: 0; z-index: 1060; }
        .barra-bandera .amarillo { background-color: var(--bandera-amarillo); width: 33.33%; }
        .barra-bandera .azul { background-color: var(--bandera-azul); width: 33.33%; }
        .barra-bandera .rojo { background-color: var(--bandera-rojo); width: 33.34%; }

        /* Cintillo Superior Institucional */
        .cintillo-ministerio { background-color: #ffffff; padding: 6px 0; margin-top: 5px; border-bottom: 1px solid #e0e0e0; }

        /* NAVBAR OPTIMIZADA */
        .navbar-custom { 
            background: linear-gradient(135deg, var(--color-naranja), var(--color-naranja-hover)) !important; 
            box-shadow: 0 4px 15px rgba(255, 138, 0, 0.3); 
            padding: 8px 0; 
        }

        .brand-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .brand-sub { 
            font-size: 0.68rem; 
            color: #ffffff !important; 
            font-weight: 600; 
            letter-spacing: 0.3px;
            line-height: 1.2; 
            margin-bottom: 2px;
        }
        .brand-main { 
            font-size: 1.05rem; 
            color: #ffffff !important; 
            font-weight: 800; 
            letter-spacing: 0.5px;
            line-height: 1.2; 
        }

        .nav-link-custom {
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.82rem;
            padding: 6px 10px !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .nav-link-custom:hover {
            color: var(--color-vinotinto-dark) !important;
            transform: translateY(-1px);
        }

        /* Botón Galería de Fotos (Blanco Destacado) */
        .btn-galeria-fotos {
            background-color: #ffffff;
            color: var(--color-vinotinto) !important;
            font-weight: 800;
            border-radius: 20px;
            padding: 6px 16px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.25s ease;
            font-size: 0.80rem;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.9);
        }
        .btn-galeria-fotos:hover {
            background-color: var(--color-vinotinto);
            color: #ffffff !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(82, 31, 41, 0.35);
        }

        /* Botón Aula Virtual (Vinotinto Institucional) */
        .btn-aula-virtual {
            background-color: var(--color-vinotinto);
            color: #ffffff !important; 
            font-weight: 800; 
            border-radius: 20px; 
            padding: 6px 16px;
            box-shadow: 0 3px 8px rgba(82, 31, 41, 0.3); 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            transition: all 0.25s ease;
            font-size: 0.80rem;
            white-space: nowrap;
            border: 1px solid var(--color-vinotinto-dark);
        }
        .btn-aula-virtual:hover { 
            background-color: var(--color-vinotinto-dark); 
            transform: translateY(-2px); 
            box-shadow: 0 5px 12px rgba(82, 31, 41, 0.5); 
        }

        /* Banner Principal Hero */
        .hero-banner { 
            background: linear-gradient(135deg, var(--color-vinotinto), var(--color-vinotinto-dark)) !important; 
            color: #ffffff; 
            padding: 70px 0; 
            border-bottom: 4px solid var(--color-dorado);
        }

        .hero-logo-img {
            max-width: 230px;
            height: auto;
            display: inline-block;
            filter: drop-shadow(0px 8px 16px rgba(0,0,0,0.25));
        }

        .section-title { color: var(--color-vinotinto); font-weight: 800; font-size: 2.1rem; }
        .section-divider { width: 60px; height: 4px; background-color: var(--color-naranja); border-radius: 2px; margin: 12px auto 0 auto; }

        .card-fundamento-original {
            border: none; border-radius: 20px; background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.07); overflow: hidden; height: 100%; transition: all 0.35s ease;
        }
        .card-fundamento-original:hover { transform: translateY(-8px); box-shadow: 0 16px 35px rgba(255, 138, 0, 0.2); }

        .card-header-naranja { background: linear-gradient(135deg, var(--color-naranja), var(--color-naranja-hover)); color: #ffffff; padding: 20px; border-bottom-left-radius: 50% 15px; border-bottom-right-radius: 50% 15px; text-align: center; }
        .card-header-vinotinto { background: linear-gradient(135deg, var(--color-vinotinto), var(--color-vinotinto-dark)); color: #ffffff; padding: 20px; border-bottom-left-radius: 50% 15px; border-bottom-right-radius: 50% 15px; text-align: center; }

        .btn-revista-naranja {
            background: linear-gradient(135deg, var(--color-naranja), var(--color-naranja-hover));
            color: #ffffff !important; font-weight: 800; border-radius: 30px; padding: 12px 28px;
            transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(255, 138, 0, 0.35); border: none; display: inline-block; text-decoration: none;
        }

        .flyer-container-719x1265 {
            width: 100%; max-width: 320px; aspect-ratio: 719 / 1265; border-radius: 20px; overflow: hidden;
            background-color: #f8f9fa; box-shadow: 0 10px 30px rgba(82, 31, 41, 0.12); transition: all 0.35s ease; position: relative; margin: 0 auto;
        }
        .flyer-img-719x1265 { width: 100%; height: 100%; object-fit: cover; object-position: center top; display: block; }
        .flyer-badge { position: absolute; top: 15px; left: 15px; z-index: 2; font-size: 0.75rem; padding: 6px 12px; }

        .flyer-card { border: none; border-radius: 16px; overflow: hidden; background: #ffffff; box-shadow: 0 8px 25px rgba(0,0,0,0.08); transition: all 0.35s ease; cursor: pointer; }
        .flyer-card:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 16px 35px rgba(82, 31, 41, 0.18); }
        .flyer-img-container { width: 100%; height: 240px; background-color: var(--color-crema-suave); display: flex; align-items: center; justify-content: center; overflow: hidden; }
        .flyer-img-full { width: 100%; height: 100%; object-fit: cover; display: block; }

        /* Estilos Footer e Institucionales */
        .footer-naranja { background: linear-gradient(135deg, var(--color-naranja), var(--color-naranja-hover)) !important; color: #ffffff; }
        .link-institucional {
            background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 30px; padding: 8px 18px; color: #ffffff !important; text-decoration: none;
            font-weight: 700; font-size: 0.85rem; transition: all 0.3s ease; display: inline-flex; align-items: center;
        }
        .link-institucional:hover { background: #ffffff; color: var(--color-vinotinto) !important; transform: translateY(-2px); }
    </style>
</head>
<body>

    <!-- Viñeta Tricolor Superior -->
    <div class="barra-bandera">
        <div class="amarillo"></div><div class="azul"></div><div class="rojo"></div>
    </div>

    <!-- Cintillo Institucional Ministerio de la Mujer -->
    <div class="cintillo-ministerio">
        <div class="container d-flex justify-content-between align-items-center">
            <img src="assets/img/MPP_Mujer.png" alt="Ministerio de la Mujer" style="max-height: 85px;" class="img-fluid">
        </div>
    </div>

    <!-- Navegación Principal -->
    <nav class="navbar navbar-expand-xl sticky-top navbar-custom">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="index.php#inicio">
                <img src="assets/img/LogoEscuelaNora_NUEVO.png" alt="Logo Nora" width="42" height="42" class="me-2 rounded-circle border border-white flex-shrink-0">
                <div class="brand-container">
                    <span class="brand-sub text-uppercase">Escuela Internacional de Liderazgo de la Mujer</span>
                    <span class="brand-main text-uppercase">NORA CASTAÑEDA</span>
                </div>
            </a>
            <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navNora">
                <i class="bi bi-list fs-2"></i>
            </button>
            <div class="collapse navbar-collapse" id="navNora">
                <ul class="navbar-nav ms-auto align-items-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php#inicio">INICIO</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php#fundamentos">FUNDAMENTOS</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="ofertas.php">OFERTA ACADÉMICA</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php#noticias">NOTICIAS</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php#nosotros">NOSOTROS</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="index.php#contacto">SEDE</a></li>

                    <!-- BOTÓN GALERÍA DE FOTOS -->
                    <li class="nav-item ms-xl-2 my-1 my-xl-0">
                        <a href="galeria.php" class="btn-galeria-fotos">
                            <i class="bi bi-images me-1 text-danger fs-6"></i> GALERÍA DE FOTOS
                        </a>
                    </li>

                    <!-- BOTÓN AULA VIRTUAL -->
                    <li class="nav-item ms-xl-1 my-1 my-xl-0">
                        <a href="https://escuelanora.minmujer.gob.ve/login/index.php" target="_blank" class="btn-aula-virtual">
                            <i class="bi bi-mortarboard-fill me-1 text-warning fs-6"></i> AULA VIRTUAL
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>