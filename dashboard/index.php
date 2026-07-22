<?php
/**
 * Dashboard PRO Completo - La Cueva del Güero
 * Endpoint: /dashboard/index.php
 */

session_start();
require_once __DIR__ . '/../config/config.php';

// Validar administrador
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PRO - La Cueva del Güero</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --bg-primary: #06060c;
            --bg-sidebar: rgba(10, 10, 18, 0.95);
            --bg-panel: rgba(16, 16, 26, 0.7);
            --neon-magenta: #FF00FF;
            --neon-cyan: #00FFFF;
            --neon-green: #39FF14;
            --border-color: rgba(255, 0, 255, 0.15);
            --text-main: #e2e2e9;
            --text-muted: #8e8e9f;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            overflow: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 0, 255, 0.04) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 255, 255, 0.04) 0px, transparent 50%);
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(15px);
        }

        .sidebar-brand {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand h2 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--neon-magenta);
            text-shadow: 0 0 10px var(--neon-magenta);
            letter-spacing: 1px;
        }

        .sidebar-brand h2 span {
            color: var(--neon-cyan);
            text-shadow: 0 0 10px var(--neon-cyan);
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .menu-item i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .menu-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.02);
            border-left-color: var(--neon-cyan);
            text-shadow: 0 0 8px rgba(0, 255, 255, 0.4);
        }

        .menu-item.active {
            color: #fff;
            background: rgba(255, 0, 255, 0.05);
            border-left-color: var(--neon-magenta);
            text-shadow: 0 0 8px rgba(255, 0, 255, 0.4);
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .btn-logout {
            width: 100%;
            padding: 12px;
            background: transparent;
            border: 1px solid #ff4d4d;
            color: #ff4d4d;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #ff4d4d;
            color: #fff;
            box-shadow: 0 0 15px #ff4d4d;
        }

        /* MAIN CONTENT CONTAINER */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* HEADER */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(10, 10, 18, 0.8);
            border-bottom: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
            z-index: 90;
        }

        header h1 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 800;
            color: #fff;
        }

        header h1 span {
            color: var(--neon-cyan);
            text-shadow: 0 0 8px var(--neon-cyan);
        }

        .admin-badge {
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--neon-cyan);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.1);
        }

        /* VIEW CONTAINER */
        .view-section {
            flex-grow: 1;
            padding: 30px 40px;
            box-sizing: border-box;
            overflow-y: auto;
            display: none;
            height: calc(100vh - 81px);
        }

        .view-section.active {
            display: block;
        }

        /* EPISODIOS VIEW SPLIT */
        .episodios-layout {
            display: flex;
            gap: 25px;
            height: 100%;
        }

        .subpanel-lista {
            width: 32%;
            background: var(--bg-panel);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
        }

        .subpanel-detalle {
            width: 68%;
            background: var(--bg-panel);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* TABS Y PANELES GENERALES */
        h2.section-title {
            margin: 0 0 20px 0;
            font-size: 1.3rem;
            color: var(--neon-cyan);
            text-shadow: 0 0 8px var(--neon-cyan);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* CARDS MAGNÉTICAS DE HOOKS */
        .hooks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 25px;
        }

        .magnetic-card {
            background: rgba(16, 16, 28, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 22px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease-in-out;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .magnetic-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--neon-cyan), transparent);
            transition: all 0.5s ease;
        }

        .magnetic-card:hover {
            transform: translateY(-8px) scale(1.02);
            border-color: var(--neon-cyan);
            box-shadow: 0 8px 30px rgba(0, 255, 255, 0.25);
        }

        .magnetic-card.facebook-card:hover { border-color: #1877F2; box-shadow: 0 8px 30px rgba(24, 119, 242, 0.25); }
        .magnetic-card.instagram-card:hover { border-color: #E1306C; box-shadow: 0 8px 30px rgba(225, 48, 108, 0.25); }
        .magnetic-card.tiktok-card:hover { border-color: #000000; box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4); border-width: 2px; }
        .magnetic-card.spotify-card:hover { border-color: #1DB954; box-shadow: 0 8px 30px rgba(29, 185, 84, 0.25); }
        .magnetic-card.shorts-card:hover { border-color: #FF0000; box-shadow: 0 8px 30px rgba(255, 0, 0, 0.25); }
        .magnetic-card.youtube-card:hover { border-color: #FF0000; box-shadow: 0 8px 30px rgba(255, 0, 0, 0.25); }

        .magnetic-card h4 {
            margin: 0 0 15px 0;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-content {
            background: rgba(0, 0, 0, 0.4);
            border-radius: 8px;
            padding: 12px;
            font-size: 0.85rem;
            color: #ccc;
            white-space: pre-wrap;
            min-height: 120px;
            max-height: 200px;
            overflow-y: auto;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.02);
        }

        /* EDITOR DE VIDEO */
        .video-editor-card {
            background: rgba(16, 16, 28, 0.7);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }

        .upload-dashed {
            border: 2px dashed var(--neon-cyan);
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            background: rgba(0, 255, 255, 0.02);
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 25px;
        }

        .upload-dashed:hover {
            background: rgba(0, 255, 255, 0.06);
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.1);
        }

        .upload-dashed i {
            font-size: 3rem;
            color: var(--neon-cyan);
            text-shadow: 0 0 10px var(--neon-cyan);
            margin-bottom: 15px;
        }

        .video-player-container {
            margin-top: 25px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        video {
            width: 100%;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            background: #000;
        }

        /* GESTOR DE BLOG */
        .blog-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .blog-tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 10px 20px;
            cursor: pointer;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .blog-tab-btn.active {
            color: var(--neon-cyan);
            border-bottom-color: var(--neon-cyan);
            text-shadow: 0 0 8px rgba(0, 255, 255, 0.3);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--neon-cyan);
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            background: rgba(10, 10, 15, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 8px;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--neon-cyan);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
        }

        /* BOTONES COMUNES */
        .btn-neon {
            background: transparent;
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-neon:hover {
            background: var(--neon-cyan);
            color: #000;
            box-shadow: 0 0 15px var(--neon-cyan);
        }

        .btn-neon-magenta {
            border-color: var(--neon-magenta);
            color: var(--neon-magenta);
        }

        .btn-neon-magenta:hover {
            background: var(--neon-magenta);
            color: #fff;
            box-shadow: 0 0 15px var(--neon-magenta);
        }

        .hidden {
            display: none !important;
        }

        /* MOBILE FIRST & RESPONSIVE DASHBOARD */
        .btn-toggle-sidebar {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 5px;
            margin-right: 15px;
            transition: color 0.2s;
        }
        
        .btn-toggle-sidebar:hover {
            color: var(--neon-cyan);
        }

        @media (max-width: 768px) {
            .btn-toggle-sidebar {
                display: block;
            }
            body {
                overflow-x: hidden;
            }
            .sidebar {
                transform: translateX(-260px);
                transition: transform 0.3s ease;
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
            header {
                padding: 15px 20px;
            }
            .view-section {
                padding: 20px 15px;
                height: calc(100vh - 71px);
            }
            /* Episodios View mobile list */
            .episodios-layout {
                flex-direction: column;
                height: auto;
                gap: 15px;
            }
            .subpanel-lista, .subpanel-detalle {
                width: 100% !important;
                height: auto !important;
            }
            /* Canva Editor mobile grid */
            #view-canva > div {
                grid-template-columns: 1fr !important;
                gap: 15px !important;
            }
            /* Video Editor Workspace mobile layout */
            #view-video > div:nth-of-type(2) {
                grid-template-columns: 1fr !important;
                height: auto !important;
                gap: 15px !important;
            }
            #view-video > div:nth-of-type(2) > div {
                height: auto !important;
                min-height: 200px;
            }
            /* Topbar of video editor wraps */
            #view-video > div:nth-of-type(1) {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }
            #view-video > div:nth-of-type(1) > div {
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR DE NAVEGACIÓN -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <h2>LA CUEVA <span>PRO</span></h2>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active" id="menu-episodios" onclick="switchView('episodios')">
                <i class="fa-solid fa-microphone"></i> Episodios y Fichas
            </li>
            <li class="menu-item" id="menu-blog" onclick="switchView('blog')">
                <i class="fa-solid fa-pen-nib"></i> Gestor de Blog
            </li>
            <li class="menu-item" id="menu-hooks" onclick="switchView('hooks')">
                <i class="fa-solid fa-magnet"></i> Generador de Hooks
            </li>
            <li class="menu-item" id="menu-video" onclick="switchView('video')">
                <i class="fa-solid fa-video"></i> Editor de Video
            </li>
            <li class="menu-item" id="menu-canva" onclick="switchView('canva')">
                <i class="fa-solid fa-palette"></i> Editor Canva PRO
            </li>
            <li class="menu-item" id="menu-avatar" onclick="switchView('avatar')">
                <i class="fa-solid fa-masks-theater"></i> Avatar Engine
            </li>
        </ul>
        <div class="sidebar-footer">
            <form action="logout.php" method="POST" style="margin: 0;">
                <button type="submit" class="btn-logout"><i class="fa-solid fa-power-off"></i> Cerrar Sesión</button>
            </form>
        </div>
    </nav>

    <!-- ÁREA PRINCIPAL -->
    <div class="main-content">
        <header>
            <div style="display:flex; align-items:center;">
                <button class="btn-toggle-sidebar" id="mobileToggleBtn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
                <h1 id="view-header-title">Episodios y <span>Fichas</span></h1>
            </div>
            <div style="display:flex; gap:12px; align-items:center;">
                <button class="btn-neon" style="font-size:0.8rem; padding:6px 14px;" onclick="document.getElementById('modalSubirFotoGaleria').style.display='flex'"><i class="fa-solid fa-camera"></i> Subir Foto a Galería</button>
                <div class="admin-badge">Admin: <?php echo htmlspecialchars(ADMIN_USER); ?></div>
            </div>
        </header>

        <!-- VIEW 1: EPISODIOS Y FICHAS -->
        <section class="view-section active" id="view-episodios">
            <div class="episodios-layout">
                <!-- LISTA DE REGISTROS -->
                <div class="subpanel-lista">
                    <h2 class="section-title"><i class="fa-solid fa-folder-open"></i> Registros Neon</h2>
                    <div class="registros-scroll" id="registrosContainer" style="flex-grow: 1; overflow-y: auto;">
                        <p style="color: #666; text-align: center;">Cargando episodios...</p>
                    </div>
                </div>

                <!-- DETALLE DEL EPISODIO -->
                <div class="subpanel-detalle" id="panelDetalle">
                    <div class="detalle-vacio" id="detalleVacio" style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%; color: #555;">
                        <i class="fa-solid fa-circle-info" style="font-size: 2.5rem; margin-bottom: 15px;"></i>
                        <p>Selecciona un registro para visualizar y realizar ajustes manuales.</p>
                    </div>
                    
                    <div class="detalle-contenido hidden" id="detalleContenido" style="display: flex; flex-direction: column; height: 100%;">
                        <div class="detalle-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px; margin-bottom: 15px;">
                            <h2 id="detalleNombre" style="margin: 0; color: var(--neon-magenta);">Nombre</h2>
                            <span id="detalleFecha" style="color: #666; font-size: 0.85rem;"></span>
                        </div>
                        
                        <!-- BANNER DE CURADURÍA Y DECISIÓN DE PRODUCCIÓN -->
                        <div id="curaduria-banner" style="background: rgba(0,0,0,0.4); border: 1px solid var(--neon-cyan); border-radius: 10px; padding: 15px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.3);">
                            <div>
                                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                                    <span id="curaduria-badge" style="font-weight: 800; font-size: 0.85rem; padding: 4px 12px; border-radius: 20px; background: rgba(0,255,255,0.1); border: 1px solid var(--neon-cyan); text-transform: uppercase;">🟢 NIVEL ALTO</span>
                                    <strong id="curaduria-formato" style="color: #fff; font-size: 1rem;">Invitado Principal al Canal</strong>
                                </div>
                                <p id="curaduria-razon" style="margin: 0; font-size: 0.85rem; color: #aaa; line-height: 1.4;"></p>
                            </div>
                            <div id="curaduria-actions" style="display: flex; gap: 10px;">
                                <!-- Dynamic production action buttons based on level -->
                            </div>
                        </div>
                        
                        <div class="detalle-scroll" style="flex-grow: 1; overflow-y: auto;">
                            <!-- ESCALETA -->
                            <div class="seccion-asset" style="margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px;">
                                <div class="seccion-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h3 style="margin: 0; color: var(--neon-cyan); font-size: 1rem;"><i class="fa-solid fa-list-check"></i> Escaleta</h3>
                                    <div class="btn-action-group">
                                        <button class="btn-action" onclick="descargarAsset('escaleta')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-download"></i> Descargar</button>
                                        <button class="btn-action" onclick="habilitarEdicion('escaleta')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-pen"></i> Editar</button>
                                    </div>
                                </div>
                                <div id="wrapper-escaleta">
                                    <div class="text-block" id="block-escaleta" style="white-space: pre-wrap; font-size: 0.9rem; line-height: 1.4;"></div>
                                </div>
                            </div>

                            <!-- GUION -->
                            <div class="seccion-asset" style="margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px;">
                                <div class="seccion-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h3 style="margin: 0; color: var(--neon-cyan); font-size: 1rem;"><i class="fa-solid fa-file-lines"></i> Guión</h3>
                                    <div class="btn-action-group">
                                        <button class="btn-action" onclick="descargarAsset('guion')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-download"></i> Descargar</button>
                                        <button class="btn-action" onclick="habilitarEdicion('guion')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-pen"></i> Editar</button>
                                    </div>
                                </div>
                                <div id="wrapper-guion">
                                    <div class="text-block" id="block-guion" style="white-space: pre-wrap; font-size: 0.9rem; line-height: 1.4;"></div>
                                </div>
                            </div>

                            <!-- CUE CARDS -->
                            <div class="seccion-asset" style="margin-bottom: 20px; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px;">
                                <div class="seccion-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <h3 style="margin: 0; color: var(--neon-magenta); font-size: 1rem;"><i class="fa-solid fa-address-card"></i> Cue Cards</h3>
                                    <div class="btn-action-group">
                                        <button class="btn-action btn-magenta" onclick="imprimirCueCards()" style="background: transparent; border: 1px solid var(--neon-magenta); color: var(--neon-magenta); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: bold; margin-right: 5px;"><i class="fa-solid fa-print"></i> Imprimir</button>
                                        <button class="btn-action" onclick="descargarAsset('cuecards')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-download"></i> Descargar</button>
                                        <button class="btn-action" onclick="habilitarEdicion('cuecards')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-pen"></i> Editar</button>
                                    </div>
                                </div>
                                <div id="wrapper-cuecards">
                                    <div class="text-block" id="block-cuecards" style="white-space: pre-wrap; font-size: 0.9rem; line-height: 1.4;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIEW 2: GESTOR DE BLOG -->
        <section class="view-section" id="view-blog">
            <h2 class="section-title"><i class="fa-solid fa-newspaper"></i> Gestor de Artículos de Blog</h2>
            
            <div class="blog-tabs">
                <button class="blog-tab-btn active" id="btn-tab-upload" onclick="switchBlogTab('upload')">PDF Conversor</button>
                <button class="blog-tab-btn" id="btn-tab-edit" onclick="switchBlogTab('edit')">Redactar Post</button>
            </div>

            <!-- TAB 1: PDF CONVERSOR -->
            <div class="blog-content-view" id="blog-tab-upload">
                <div class="form-group">
                    <label>Procesar y Extraer Texto de Guión/Ficha PDF</label>
                    <div class="upload-dashed" id="blog-upload-area" onclick="document.getElementById('blog-pdf-input').click()">
                        <i class="fa-solid fa-file-pdf"></i>
                        <p>Arrastra tu PDF de producción aquí o haz clic para seleccionar</p>
                        <input type="file" id="blog-pdf-input" accept=".pdf" style="display: none;" onchange="handleBlogPDFSelect(event)">
                    </div>
                    <div id="blog-file-info" style="margin-bottom: 15px; font-weight: bold; color: var(--neon-green); display: none;"></div>
                </div>
                
                <div class="form-group hidden" id="blog-preview-container">
                    <label>Texto Extraído del PDF</label>
                    <textarea class="form-input" id="blog-extracted-text" style="min-height: 200px; font-family: monospace;" readonly></textarea>
                    <button class="btn-neon" style="margin-top: 15px;" onclick="convertirExtraccionAPost()"><i class="fa-solid fa-wand-magic-sparkles"></i> Convertir a Post Editable</button>
                </div>
            </div>

            <!-- TAB 2: REDACTAR / PUBLICAR POST -->
            <div class="blog-content-view hidden" id="blog-tab-edit">
                <div class="form-group">
                    <label>Título del Post *</label>
                    <input type="text" class="form-input" id="blog-title" placeholder="Ej: Ficha Storytelling de Invitado X">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Autor *</label>
                        <input type="text" class="form-input" id="blog-author" value="La Cueva del Güero">
                    </div>
                    <div class="form-group">
                        <label>Categoría</label>
                        <select class="form-input" id="blog-category">
                            <option value="podcast">Podcast</option>
                            <option value="storytelling">Storytelling</option>
                            <option value="reflexion">Reflexión</option>
                            <option value="entrevista">Entrevista</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Contenido del Artículo *</label>
                    <textarea class="form-input" id="blog-content" style="min-height: 250px;" placeholder="Escribe o pega el cuerpo del artículo de blog..."></textarea>
                </div>
                <button class="btn-neon btn-neon-magenta" onclick="publicarBlogPost()"><i class="fa-solid fa-upload"></i> Publicar en el Blog Oficial</button>
            </div>
        </section>

        <!-- VIEW 3: GENERADOR DE HOOKS -->
        <section class="view-section" id="view-hooks">
            <h2 class="section-title"><i class="fa-solid fa-magnet"></i> Generador de Hooks (Redes Sociales)</h2>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Ingresa el tema o frase central de la plática para generar automáticamente ganchos y copys adaptados al flow de cada red social.</p>
            
            <div style="display: flex; gap: 15px; margin-bottom: 30px;">
                <input type="text" class="form-input" id="hooks-topic" placeholder="Ej: Sobrevivir a la traición de tus amigos del barrio" style="flex-grow: 1;">
                <button class="btn-neon" onclick="generarHooksParaRedes()"><i class="fa-solid fa-wand-magic-sparkles"></i> Generar Ganchos</button>
            </div>

            <div class="hooks-grid">
                <!-- CARD: FACEBOOK -->
                <div class="magnetic-card facebook-card" style="border-top: 3px solid #1877F2;">
                    <h4><i class="fab fa-facebook" style="color: #1877F2;"></i> Facebook Feed</h4>
                    <div class="card-content" id="hook-facebook">Escribe un tema arriba para generar ganchos...</div>
                    <button class="btn-neon" onclick="copyHook('facebook')" style="width: 100%; padding: 6px 12px; font-size: 0.8rem;"><i class="fa-regular fa-copy"></i> Copiar Gancho</button>
                </div>

                <!-- CARD: INSTAGRAM -->
                <div class="magnetic-card instagram-card" style="border-top: 3px solid #E1306C;">
                    <h4><i class="fab fa-instagram" style="color: #E1306C;"></i> Instagram Carousel</h4>
                    <div class="card-content" id="hook-instagram">Escribe un tema arriba para generar ganchos...</div>
                    <button class="btn-neon" onclick="copyHook('instagram')" style="width: 100%; padding: 6px 12px; font-size: 0.8rem;"><i class="fa-regular fa-copy"></i> Copiar Gancho</button>
                </div>

                <!-- CARD: TIKTOK -->
                <div class="magnetic-card tiktok-card" style="border-top: 3px solid #000;">
                    <h4><i class="fab fa-tiktok" style="color: #fff;"></i> TikTok Hook</h4>
                    <div class="card-content" id="hook-tiktok">Escribe un tema arriba para generar ganchos...</div>
                    <button class="btn-neon" onclick="copyHook('tiktok')" style="width: 100%; padding: 6px 12px; font-size: 0.8rem;"><i class="fa-regular fa-copy"></i> Copiar Gancho</button>
                </div>

                <!-- CARD: SPOTIFY -->
                <div class="magnetic-card spotify-card" style="border-top: 3px solid #1DB954;">
                    <h4><i class="fab fa-spotify" style="color: #1DB954;"></i> Spotify Intro Teaser</h4>
                    <div class="card-content" id="hook-spotify">Escribe un tema arriba para generar ganchos...</div>
                    <button class="btn-neon" onclick="copyHook('spotify')" style="width: 100%; padding: 6px 12px; font-size: 0.8rem;"><i class="fa-regular fa-copy"></i> Copiar Gancho</button>
                </div>

                <!-- CARD: YOUTUBE SHORTS -->
                <div class="magnetic-card shorts-card" style="border-top: 3px solid #FF0000;">
                    <h4><i class="fab fa-youtube" style="color: #FF0000;"></i> YouTube Shorts</h4>
                    <div class="card-content" id="hook-shorts">Escribe un tema arriba para generar ganchos...</div>
                    <button class="btn-neon" onclick="copyHook('shorts')" style="width: 100%; padding: 6px 12px; font-size: 0.8rem;"><i class="fa-regular fa-copy"></i> Copiar Gancho</button>
                </div>

                <!-- CARD: YOUTUBE LONG -->
                <div class="magnetic-card youtube-card" style="border-top: 3px solid #FF0000;">
                    <h4><i class="fab fa-youtube" style="color: #FF0000;"></i> YouTube Videos</h4>
                    <div class="card-content" id="hook-youtube">Escribe un tema arriba para generar ganchos...</div>
                    <button class="btn-neon" onclick="copyHook('youtube')" style="width: 100%; padding: 6px 12px; font-size: 0.8rem;"><i class="fa-regular fa-copy"></i> Copiar Gancho</button>
                </div>
            </div>
        </section>

        <!-- VIEW 4: EDITOR DE VIDEO (LA CUEVA VIDEO EDITOR PRO) -->
        <section class="view-section" id="view-video" style="padding: 15px 25px;">
            <!-- TOP BAR -->
            <div style="display:flex; justify-content:space-between; align-items:center; background:#0f0f18; border:1px solid rgba(0,255,255,0.2); border-radius:12px; padding:10px 20px; margin-bottom:15px; box-shadow:0 0 15px rgba(0,255,255,0.1);">
                <div style="display:flex; align-items:center; gap:15px;">
                    <span style="font-family:'Outfit', sans-serif; font-weight:800; color:#FF00FF; font-size:1.1rem; text-shadow:0 0 8px #FF00FF;"><i class="fa-solid fa-clapperboard"></i> LA CUEVA VIDEO EDITOR PRO</span>
                    <span id="editor-project-name" style="background:rgba(255,255,255,0.05); padding:4px 10px; border-radius:6px; font-size:0.8rem; color:#aaa;">Proyecto_Sin_Nombre.mp4</span>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <button class="btn-neon" style="font-size:0.75rem; padding:5px 10px;" onclick="document.getElementById('editor-file-input').click()"><i class="fa-solid fa-file-import"></i> Local</button>
                    <button class="btn-neon" style="font-size:0.75rem; padding:5px 10px; background:rgba(0, 255, 255, 0.1);" onclick="abrirImportarNube()"><i class="fa-solid fa-cloud-arrow-up"></i> Nube (Drive/Dropbox/TeraBox)</button>
                    <button class="btn-neon" style="font-size:0.75rem; padding:5px 10px;" onclick="alert('Proyecto Guardado')"><i class="fa-solid fa-save"></i> Guardar</button>
                    <button class="btn-neon btn-neon-magenta" style="font-size:0.75rem; padding:5px 12px;" onclick="abrirExportarVideo()"><i class="fa-solid fa-upload"></i> Exportar Presets</button>
                    <input type="file" id="editor-file-input" accept="video/*,audio/*" style="display:none;">
                    <div style="font-size:0.75rem; color:#666; border-left:1px solid rgba(255,255,255,0.1); padding-left:15px; display:flex; gap:10px;">
                        <span>CPU: <strong style="color:#00FFFF;">12%</strong></span>
                        <span>GPU: <strong style="color:#FF00FF;">44%</strong></span>
                    </div>
                </div>
            </div>

            <!-- WORKSPACE GRID (LEFT, CENTER, RIGHT PANELS) -->
            <div style="display:grid; grid-template-columns: 240px 1fr 280px; gap:15px; height:380px; align-items:stretch; margin-bottom:15px;">
                <!-- PANEL IZQUIERDO: BIBLIOTECA & MODELOS IA -->
                <div style="background:rgba(15,15,15,0.8); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:15px; overflow-y:auto;">
                    <h4 style="color:#00FFFF; margin:0 0 5px 0; border-bottom:1px solid rgba(0,255,255,0.2); padding-bottom:5px; font-size:0.85rem;"><i class="fa-solid fa-folder"></i> Recursos e IA</h4>
                    <div style="display:flex; flex-direction:column; gap:8px; font-size:0.8rem;">
                        <span style="color:#aaa; font-weight:bold;">🚀 Modelos de IA Directa</span>
                        <button class="btn-neon" style="width:100%; text-align:left; font-size:0.75rem;" onclick="ejecutarIAVideo('Subtítulos Automáticos')"><i class="fa-solid fa-closed-captioning"></i> Subtítulos Auto IA</button>
                        <button class="btn-neon" style="width:100%; text-align:left; font-size:0.75rem;" onclick="ejecutarIAVideo('Corrección de Color IA')"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Color IA</button>
                        <button class="btn-neon" style="width:100%; text-align:left; font-size:0.75rem;" onclick="ejecutarIAVideo('Quitar Fondo')"><i class="fa-solid fa-user-minus"></i> Quitar Fondo IA</button>
                        <button class="btn-neon" style="width:100%; text-align:left; font-size:0.75rem;" onclick="ejecutarIAVideo('Mejora de Voz IA')"><i class="fa-solid fa-microphone-lines"></i> Reducir Ruido IA</button>
                    </div>
                    <div style="display:flex; flex-direction:column; gap:8px; font-size:0.8rem; border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
                        <span style="color:#aaa; font-weight:bold;">🎨 Biblioteca stock</span>
                        <span style="color:#666; cursor:pointer;" onclick="alert('Cargando plantillas Filmora...')"><i class="fa-solid fa-cubes"></i> Plantillas CapCut</span>
                        <span style="color:#666; cursor:pointer;" onclick="alert('Cargando LUTs profesionales...')"><i class="fa-solid fa-droplet"></i> LUTs & Filtros</span>
                        <span style="color:#666; cursor:pointer;" onclick="alert('Cargando música sin derechos...')"><i class="fa-solid fa-music"></i> Audio Libres</span>
                    </div>
                </div>

                <!-- PANEL CENTRAL: VISTA PREVIA -->
                <div style="background:#050508; border:1px solid rgba(255,255,255,0.05); border-radius:12px; display:flex; flex-direction:column; justify-content:space-between; padding:15px; position:relative; overflow:hidden;">
                    <div id="preview-wrapper-box" style="flex-grow:1; display:flex; justify-content:center; align-items:center; overflow:hidden; transition: all 0.3s ease;">
                        <video id="editor-preview-video" style="max-height:100%; max-width:100%; border-radius:8px; box-shadow:0 0 20px rgba(0,0,0,0.8);"></video>
                    </div>
                    <!-- CONTROLES PREVIEW -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
                        <div style="display:flex; gap:10px; align-items:center;">
                            <button id="editor-play-btn" class="btn-neon" style="padding:6px 12px; font-size:0.8rem;"><i class="fa-solid fa-play"></i></button>
                            <span id="timecode-display" style="font-family:monospace; font-size:0.8rem; color:#aaa;">00:00:00</span>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button class="btn-neon" style="font-size:0.75rem; padding:4px 8px;" onclick="toggleCompareFilters()"><i class="fa-solid fa-right-left"></i> Antes/Después</button>
                            <button class="btn-neon btn-neon-magenta" style="font-size:0.75rem; padding:4px 8px;" onclick="toggleMobileView()"><i class="fa-solid fa-mobile-screen-button"></i> Vista 9:16</button>
                        </div>
                    </div>
                </div>

                <!-- PANEL DERECHO: PROPIEDADES & AUDIO/VIDEO -->
                <div style="background:rgba(15,15,15,0.8); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:15px; overflow-y:auto; font-size:0.8rem;">
                    <h4 style="color:#FF00FF; margin:0; border-bottom:1px solid rgba(255,0,255,0.2); padding-bottom:5px; font-size:0.85rem;"><i class="fa-solid fa-sliders"></i> Ajustes del Clip</h4>
                    <div>
                        <span style="color:#aaa; font-weight:bold;">Transformación</span>
                        <div style="margin-top:5px; display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                            <div>
                                <label style="font-size:0.7rem; color:#666;">Escala:</label>
                                <input type="range" min="50" max="150" value="100" style="width:100%;">
                            </div>
                            <div>
                                <label style="font-size:0.7rem; color:#666;">Rotación:</label>
                                <input type="range" min="0" max="360" value="0" style="width:100%;">
                            </div>
                        </div>
                    </div>
                    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
                        <span style="color:#aaa; font-weight:bold;">Audio y Voz</span>
                        <div style="margin-top:5px;">
                            <label style="font-size:0.7rem; color:#666;">Volumen del Clip:</label>
                            <input type="range" min="0" max="100" value="80" style="width:100%;">
                        </div>
                    </div>
                    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
                        <span style="color:#aaa; font-weight:bold;">Subtítulos IA</span>
                        <textarea class="form-input" style="min-height:60px; font-size:0.75rem; margin-top:5px;" placeholder="Los subtítulos generados por IA aparecerán aquí..."></textarea>
                    </div>
                </div>
            </div>

            <!-- TIMELINE (BOTTOM PANEL) -->
            <div style="background:#0f0f15; border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px; position:relative;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; border-bottom:1px solid rgba(255,255,255,0.05); padding-bottom:5px;">
                    <div style="display:flex; gap:10px; align-items:center; font-size:0.8rem; color:#aaa;">
                        <button class="btn-neon" style="font-size:0.7rem; padding:2px 8px;" onclick="ejecutarIAVideo('Edición Rápida TikTok')"><i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Edición IA</button>
                        <span><i class="fa-solid fa-scissors"></i> Herramientas:</span>
                        <span style="cursor:pointer; color:#00FFFF;"><i class="fa-solid fa-cut"></i> Dividir</span>
                        <span style="cursor:pointer;"><i class="fa-solid fa-clock"></i> Velocidad</span>
                    </div>
                    <span style="font-size:0.75rem; color:#666;"> Snapping Activo | 60 FPS</span>
                </div>
                <!-- MULTI-TRACK WINDOW -->
                <div style="display:flex; flex-direction:column; gap:8px; background:rgba(0,0,0,0.4); border-radius:8px; padding:10px; position:relative; min-height:110px;">
                    <!-- Cabezal de reproducción rojo -->
                    <div id="timeline-progress" style="position:absolute; top:0; bottom:0; left:0; width:2px; background:#ff4d4d; z-index:10; box-shadow:0 0 8px #ff4d4d;">
                        <div style="width:10px; height:10px; background:#ff4d4d; border-radius:50%; margin-left:-4px; margin-top:-4px;"></div>
                    </div>

                    <!-- PISTA SUBTÍTULOS -->
                    <div id="subtitles-track" style="display:none; height:24px; background:rgba(0,255,255,0.1); border:1px solid var(--neon-cyan); border-radius:4px; font-size:0.7rem; color:#00FFFF; padding-left:10px; line-height:22px; position:relative;">
                        <i class="fa-solid fa-closed-captioning"></i> [IA Subtítulos Generados] "A los 10 años, mi papá me mandó a la calle..."
                    </div>

                    <!-- PISTA VIDEO -->
                    <div style="height:32px; background:rgba(255,0,255,0.1); border:1px solid var(--neon-magenta); border-radius:4px; font-size:0.75rem; color:#FF00FF; padding-left:10px; line-height:30px; position:relative; overflow:hidden;">
                        <i class="fa-solid fa-video"></i> Video_Principal_Capitulo.mp4 (Premiere Multicapa Layer)
                        <div style="position:absolute; right:10px; top:0; bottom:0; width:40px; background:rgba(255,0,255,0.2); border-left:1px solid #FF00FF; cursor:ew-resize;"></div>
                    </div>

                    <!-- PISTA AUDIO -->
                    <div style="height:28px; background:rgba(78,252,34,0.1); border:1px solid #4EFC22; border-radius:4px; font-size:0.75rem; color:#4EFC22; padding-left:10px; line-height:26px; position:relative; overflow:hidden;">
                        <i class="fa-solid fa-music"></i> Audio_Episodio_Mejorado.wav (Filmora Sync Auto)
                        <div style="position:absolute; right:10px; top:0; bottom:0; width:40px; background:rgba(78,252,34,0.2); border-left:1px solid #4EFC22; cursor:ew-resize;"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MODAL: EXPORTAR VIDEO CON PRESETS -->
        <div id="modalExportarVideo" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center;">
            <div style="background:rgba(15,15,15,0.95); border:2px solid var(--neon-magenta); border-radius:20px; padding:30px; width:90%; max-width:480px; box-shadow:0 0 40px rgba(255,0,255,0.4);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0; color:#FF00FF;"><i class="fa-solid fa-upload"></i> Exportación Directa con GPU</h3>
                    <button onclick="cerrarExportarVideo()" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
                </div>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <button class="btn-neon" style="text-align:left; padding:12px 20px;" onclick="iniciarRenderVideo('TikTok (9:16 Vert)')"><i class="fab fa-tiktok"></i> Exportar para TikTok (Vertical 1080p)</button>
                    <button class="btn-neon" style="text-align:left; padding:12px 20px;" onclick="iniciarRenderVideo('Instagram Reels')"><i class="fab fa-instagram"></i> Exportar para Instagram Reels (Vertical 1080p)</button>
                    <button class="btn-neon" style="text-align:left; padding:12px 20px;" onclick="iniciarRenderVideo('YouTube Shorts')"><i class="fab fa-youtube"></i> Exportar para YouTube Shorts (1080p)</button>
                    <button class="btn-neon btn-neon-magenta" style="text-align:left; padding:12px 20px;" onclick="iniciarRenderVideo('YouTube HD (Horizontal 16:9)')"><i class="fab fa-youtube"></i> Exportar para YouTube Canal (4K / 1080p HD)</button>
                </div>
            </div>
        </div>

        <!-- OVERLAY DE CARGA / ESTADO IA -->
        <div id="editor-ia-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.8); z-index:100000; justify-content:center; align-items:center; flex-direction:column; gap:15px;">
            <div style="border: 4px solid rgba(0,255,255,0.1); border-left-color: var(--neon-cyan); width: 50px; height: 50px; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <span class="ia-status-text" style="color:#00FFFF; font-family:'Outfit', sans-serif; font-size:1.1rem; text-shadow:0 0 8px #00FFFF;">Ejecutando proceso de IA...</span>
        </div>

        <!-- MODAL: IMPORTADOR DESDE LA NUBE -->
        <div id="modalImportarNube" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center;">
            <div style="background:rgba(15,15,15,0.98); border:2px solid var(--neon-cyan); border-radius:20px; padding:30px; width:90%; max-width:640px; box-shadow:0 0 40px rgba(0,255,255,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid rgba(0,255,255,0.2); padding-bottom:10px;">
                    <h3 style="margin:0; color:#00FFFF; font-family:'Outfit', sans-serif;"><i class="fa-solid fa-cloud-arrow-up"></i> Conectores de Almacenamiento en la Nube</h3>
                    <button onclick="cerrarImportarNube()" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
                </div>
                
                <p style="color:#aaa; font-size:0.85rem; margin-bottom:20px;">Elige un proveedor de nube para sincronizar y cargar tus archivos de video de producción de forma nativa a la línea de tiempo:</p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                    <!-- GOOGLE DRIVE -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px;">
                        <h4 style="color:#ffb703; margin:0 0 10px 0; font-size:0.9rem;"><i class="fab fa-google-drive"></i> Google Drive / One</h4>
                        <ul style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:flex; flex-direction:column; gap:6px;">
                            <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Google Drive', 'Grabacion_Calle_Ep13_Drive.mp4')"><i class="fa-regular fa-file-video"></i> Grabacion_Calle_Ep13_Drive.mp4</a></li>
                            <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Google Drive', 'Entrevista_Ramon_Ep14_Drive.mp4')"><i class="fa-regular fa-file-video"></i> Entrevista_Ramon_Ep14_Drive.mp4</a></li>
                        </ul>
                    </div>

                    <!-- DROPBOX -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px;">
                        <h4 style="color:#007fff; margin:0 0 10px 0; font-size:0.9rem;"><i class="fab fa-dropbox"></i> Dropbox</h4>
                        <ul style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:flex; flex-direction:column; gap:6px;">
                            <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Dropbox', 'Invitado_JeyB_Ep15_Dropbox.mp4')"><i class="fa-regular fa-file-video"></i> Invitado_JeyB_Ep15_Dropbox.mp4</a></li>
                            <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Dropbox', 'Audio_Master_Ep15_Dropbox.wav')"><i class="fa-regular fa-file-audio"></i> Audio_Master_Ep15_Dropbox.wav</a></li>
                        </ul>
                    </div>

                    <!-- ONEDRIVE -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px;">
                        <h4 style="color:#00a4ef; margin:0 0 10px 0; font-size:0.9rem;"><i class="fa-solid fa-cloud"></i> Microsoft OneDrive</h4>
                        <ul style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:flex; flex-direction:column; gap:6px;">
                            <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('OneDrive', 'Capitulo16_Final_OneDrive.mov')"><i class="fa-regular fa-file-video"></i> Capitulo16_Final_OneDrive.mov</a></li>
                        </ul>
                    </div>

                    <!-- TERABOX -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px;">
                        <h4 style="color:#4caf50; margin:0 0 10px 0; font-size:0.9rem;"><i class="fa-solid fa-server"></i> TeraBox (TheraBite)</h4>
                        <ul style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:flex; flex-direction:column; gap:6px;">
                            <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('TeraBox', 'Archivo_Pesado_1TB_Ep17.mp4')"><i class="fa-regular fa-file-video"></i> Archivo_Pesado_1TB_Ep17.mp4</a></li>
                        </ul>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button class="btn-neon" onclick="cerrarImportarNube()">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- VIEW 5: EDITOR CANVA PRO -->
        <section class="view-section" id="view-canva">
            <p style="color: var(--text-muted); margin-bottom: 25px;">Sube tus fotos de produccion, elimina fondos, ajusta colores y exporta posters o miniaturas en PNG transparente, JPEG o WEBP.</p>

            <div style="display: grid; grid-template-columns: 320px 1fr; gap: 25px; align-items: start;">
                <!-- CONTROLES -->
                <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-cyan); border-radius: 16px; padding: 20px;">
                    <h3 style="color:#00FFFF; margin-top:0;">🛠️ Herramientas</h3>
                    <div class="form-group" style="margin-bottom:15px;">
                        <label>Subir Foto / Material</label>
                        <input type="file" id="canvaFileInput" accept="image/*" class="form-input">
                    </div>
                    <button class="btn-neon btn-neon-magenta" style="width:100%; margin-bottom:12px;" onclick="removerFondoCanva()"><i class="fa-solid fa-scissors"></i> Eliminar Fondo / Transparente</button>
                    <button class="btn-neon" style="width:100%; margin-bottom:15px; border-color:#ff4d4d; color:#ff4d4d;" onclick="limpiarAreaPoster()"><i class="fa-solid fa-trash-can"></i> Limpiar Área de Poster</button>

                    <h4 style="color:#fff; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:5px;">🎨 Ajuste de Color</h4>
                    <div style="margin-bottom:10px;">
                        <label style="font-size:0.8rem; color:#aaa;">Brillo:</label>
                        <input type="range" id="canva-brightness" min="0" max="200" value="100" style="width:100%;">
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-size:0.8rem; color:#aaa;">Contraste:</label>
                        <input type="range" id="canva-contrast" min="0" max="200" value="100" style="width:100%;">
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="font-size:0.8rem; color:#aaa;">Saturación:</label>
                        <input type="range" id="canva-saturate" min="0" max="200" value="100" style="width:100%;">
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="font-size:0.8rem; color:#aaa;">Sepia:</label>
                        <input type="range" id="canva-sepia" min="0" max="100" value="0" style="width:100%;">
                    </div>

                    <h4 style="color:#fff; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:5px;">✍️ Texto de Poster</h4>
                    <input type="text" id="canva-text" class="form-input" placeholder="Ej: LA CUEVA PODCAST" oninput="applyCanvaFilters()" style="margin-bottom:15px;">

                    <h4 style="color:#fff; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:5px;">💾 Descargar Resultado</h4>
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" style="flex:1; font-size:0.75rem;" onclick="exportarImagenCanva('png')">PNG (Transp)</button>
                        <button class="btn-neon" style="flex:1; font-size:0.75rem;" onclick="exportarImagenCanva('jpeg')">JPEG</button>
                        <button class="btn-neon" style="flex:1; font-size:0.75rem;" onclick="exportarImagenCanva('webp')">WEBP</button>
                    </div>
                </div>

                <!-- LIENZO HTML5 -->
                <div style="background: rgba(0,0,0,0.5); border: 1px dashed rgba(0,255,255,0.3); border-radius: 16px; padding: 20px; text-align: center; min-height: 450px; display: flex; justify-content: center; align-items: center;">
                    <canvas id="canvaCanvas" style="max-width:100%; max-height:550px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.8);"></canvas>
                </div>
            </div>
        </section>

        <!-- VIEW 6: AVATAR ENGINE -->
        <section class="view-section" id="view-avatar">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <div>
                    <h2 class="section-title" style="margin:0;"><i class="fa-solid fa-masks-theater"></i> Avatar-Engine: Creador de Personajes</h2>
                    <p style="color: var(--text-muted); margin:5px 0 0;">Genera humanoide aislado estilo Comic Neón (fondo transparente, sin muebles) e importa avatares pre-existentes.</p>
                </div>
                <button class="btn-neon btn-neon-magenta" onclick="abrirModalImportarExistente()"><i class="fa-solid fa-file-import"></i> Importar Avatar Pre-Existente (Oculto)</button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 30px;">
                <!-- PANEL 1: REGISTRO DE NUEVO PERSONAJE (3 FOTOS + CONSENTIMIENTO) -->
                <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-cyan); border-radius: 16px; padding: 20px;">
                    <h3 style="color:#00FFFF; margin-top:0;">👤 1. Nuevo Personaje (3 Fotos + Consentimiento)</h3>
                    <form onsubmit="registrarNuevoAvatar(event)">
                        <div class="form-group">
                            <label>Nombre del Personaje *</label>
                            <input type="text" id="avatar-nombre" class="form-input" placeholder="Ej: El Junior" required>
                        </div>
                        <div class="form-group">
                            <label>Rasgos Faciales & Estilo</label>
                            <textarea id="avatar-rasgos" class="form-input" placeholder="Ej: Cejas pobladas, barba ligera, estilo norteño urbano..." style="min-height:70px;"></textarea>
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px; margin-bottom:15px;">
                            <div>
                                <label style="font-size:0.75rem;">Foto Frente *</label>
                                <input type="file" id="avatar-frente" accept="image/*" class="form-input" required>
                            </div>
                            <div>
                                <label style="font-size:0.75rem;">Perfil Izq (Cuerpo)</label>
                                <input type="file" id="avatar-izq" accept="image/*" class="form-input">
                            </div>
                            <div>
                                <label style="font-size:0.75rem;">Perfil Der (Cuerpo)</label>
                                <input type="file" id="avatar-der" accept="image/*" class="form-input">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="color:#FF00FF;">⚖️ Documento Firmado de Consentimiento (PDF) *</label>
                            <input type="file" id="avatar-pdf" accept=".pdf" class="form-input" required>
                        </div>
                        <button type="submit" class="btn-neon" style="width:100%;"><i class="fa-solid fa-save"></i> Guardar Ficha & Crear Personaje Base</button>
                    </form>
                </div>

                <!-- PANEL 2: GENERADOR DE HUMANOIDE AISLADO -->
                <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-magenta); border-radius: 16px; padding: 20px;">
                    <h3 style="color:#FF00FF; margin-top:0;">⚡ 2. Generar Humanoide Aislado</h3>
                    <div class="form-group">
                        <label>Selecciona Personaje Guardado *</label>
                        <select id="avatarCharacterSelect" class="form-input">
                            <option value="">-- Cargar de BD --</option>
                        </select>
                    </div>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                        <div>
                            <label>Actividad / Pose</label>
                            <select id="avatarActividadSelect" class="form-input">
                                <option value="sentado">Sentado</option>
                                <option value="parado">Parado / De pie</option>
                                <option value="corriendo">Corriendo</option>
                                <option value="cantando">Cantando</option>
                                <option value="conduciendo al aire con micro">Conduciendo Podcast</option>
                            </select>
                        </div>
                        <div>
                            <label>Estilo de Ropa</label>
                            <select id="avatarRopaSelect" class="form-input">
                                <option value="deportivo 👟">Deportivo 👟</option>
                                <option value="casual 👕">Casual 👕</option>
                                <option value="formal 👔">Formal 👔</option>
                            </select>
                        </div>
                    </div>
                    <button class="btn-neon btn-neon-magenta" style="width:100%; margin-bottom:15px;" onclick="generarHumanoideAislado()"><i class="fa-solid fa-wand-magic-sparkles"></i> Generar Humanoide Aislado (Transparente)</button>

                    <div id="avatarResultOutput"></div>
                </div>
            </div>

            <!-- PANEL 3: GENERADOR SEPARADO DE OBJETOS / PROPS -->
            <div style="background: rgba(10,10,18,0.8); border: 1px solid rgba(0,255,255,0.2); border-radius: 16px; padding: 20px; margin-bottom:30px;">
                <h3 style="color:#00FFFF; margin-top:0;">🛋️ Generador Separado de Utilería & Objetos (Comic Neón)</h3>
                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <select id="propSelect" class="form-input" style="flex:1;">
                        <option value="Sofá Neón de La Cueva">Sofá Neón</option>
                        <option value="Silla Conductor de Podcast">Silla de Conducción</option>
                        <option value="Guitarra Eléctrica Neón">Guitarra Eléctrica</option>
                        <option value="Micrófono de Pie Retro">Micrófono Vintage</option>
                    </select>
                    <button class="btn-neon" onclick="generarPropObjeto()"><i class="fa-solid fa-cube"></i> Generar Objeto Transparente</button>
                </div>
                <div id="propResultOutput"></div>
            </div>

            <!-- GALERÍA DE PERSONAJES -->
            <h3 style="color:#fff; margin-bottom:15px;">👥 Personajes & Avatares Registrados</h3>
            <div id="avatarGallery" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:15px;"></div>
        </section>
    </div>

    <!-- MODAL OCULTO: IMPORTAR AVATAR PRE-EXISTENTE -->
    <div id="modalImportarAvatarExistente" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:rgba(15,15,15,0.95); border:2px solid var(--neon-magenta); border-radius:20px; padding:30px; width:90%; max-width:480px; box-shadow:0 0 40px rgba(255,0,255,0.4);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; color:#FF00FF;"><i class="fa-solid fa-file-import"></i> Importar Avatar Existente</h3>
                <button onclick="cerrarModalImportarExistente()" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
            </div>
            <form onsubmit="guardarAvatarPreExistente(event)">
                <div class="form-group">
                    <label>Nombre del Personaje *</label>
                    <input type="text" id="import-nombre" class="form-input" placeholder="Ej: El Junior" required>
                </div>
                <div class="form-group">
                    <label>Número de Capítulo / Episodio *</label>
                    <input type="text" id="import-episodio" class="form-input" placeholder="Ej: Episodio 12" required>
                </div>
                <div class="form-group">
                    <label>Imagen Limpia sin Fondo (PNG Transparente) *</label>
                    <input type="file" id="import-imagen" accept="image/png" class="form-input" required>
                </div>
                <div class="form-group">
                    <label style="color:#FF00FF;">⚖️ Documento Firmado de Consentimiento (PDF) *</label>
                    <input type="file" id="import-pdf" accept=".pdf" class="form-input" required>
                </div>
                <button type="submit" class="btn-neon btn-neon-magenta" style="width:100%;"><i class="fa-solid fa-cloud-arrow-up"></i> Registrar Avatar & Sincronizar con Dify</button>
            </form>
        </div>
    </div>

    <!-- MODAL: SUBIR FOTOS A LA GALERÍA -->
    <div id="modalSubirFotoGaleria" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center;">
        <div style="background:rgba(15,15,15,0.95); border:2px solid var(--neon-cyan); border-radius:20px; padding:30px; width:90%; max-width:480px; box-shadow:0 0 40px rgba(0,255,255,0.4);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 style="margin:0; color:#00FFFF;"><i class="fa-solid fa-camera"></i> Subir Foto a la Galería</h3>
                <button onclick="document.getElementById('modalSubirFotoGaleria').style.display='none'" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
            </div>
            <form onsubmit="guardarFotoGaleriaDashboard(event)">
                <div class="form-group">
                    <label>Título de la Fotografía *</label>
                    <input type="text" id="galeria-titulo" class="form-input" placeholder="Ej: Grabación en vivo del Episodio 10" required>
                </div>
                <div class="form-group">
                    <label>Categoría</label>
                    <select id="galeria-categoria" class="form-input">
                        <option value="La Cueva">La Cueva</option>
                        <option value="Invitados">Invitados</option>
                        <option value="Detrás de Cámara">Detrás de Cámara</option>
                        <option value="Eventos">Eventos</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Seleccionar Imagen (JPG / PNG / WEBP) *</label>
                    <input type="file" id="galeria-archivo" accept="image/*" class="form-input" required>
                </div>
                <button type="submit" class="btn-neon" style="width:100%;"><i class="fa-solid fa-cloud-arrow-up"></i> Publicar Fotografía en la Galería Pública</button>
            </form>
        </div>
    </div>

    <!-- Cargar PDF.js para lectura de documentos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Configurar worker de PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        // Alternar barra lateral en pantallas celulares
        function toggleSidebar() {
            const sidebar = document.querySelector(".sidebar");
            if (sidebar) {
                sidebar.classList.toggle("active");
            }
        }
    </script>
    <script src="../js/dashboard-pro.js"></script>
    <script src="../js/editor-canva.js"></script>
    <script src="../js/avatar-engine.js"></script>
    <script src="../js/video-editor.js"></script>
</body>
</html>
