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
            <h1 id="view-header-title">Episodios y <span>Fichas</span></h1>
            <div class="admin-badge">Admin: <?php echo htmlspecialchars(ADMIN_USER); ?></div>
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

        <!-- VIEW 4: EDITOR DE VIDEO -->
        <section class="view-section" id="view-video">
            <h2 class="section-title"><i class="fa-solid fa-clapperboard"></i> Editor y Previsualizador de Clips</h2>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Carga tus archivos locales para visualizarlos, cortar fragmentos y preparar el material de reproducción.</p>
            
            <div class="video-editor-card">
                <div class="upload-dashed" onclick="document.getElementById('video-file-input').click()">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <h3>Arrastra un video o audio de producción aquí</h3>
                    <p>Soporta formatos .mp4, .mov, .mp3, .wav (Haz clic para seleccionar)</p>
                    <input type="file" id="video-file-input" accept="video/*,audio/*" style="display: none;" onchange="handleVideoUpload(event)">
                </div>

                <!-- CARGADOR Y PROGRESO -->
                <div id="video-progress-container" class="hidden" style="margin-bottom: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 0.9rem;">
                        <span>Cargando archivo...</span>
                        <span id="video-progress-pct">0%</span>
                    </div>
                    <div style="background: rgba(255,255,255,0.05); height: 8px; border-radius: 4px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
                        <div id="video-progress-bar" style="background: var(--neon-cyan); height: 100%; width: 0%; box-shadow: 0 0 10px var(--neon-cyan); transition: width 0.1s;"></div>
                    </div>
                </div>

                <!-- REPRODUCTOR -->
                <div class="video-player-container hidden" id="video-preview-box">
                    <h4 id="video-loaded-name" style="margin: 0 0 10px 0; color: var(--neon-cyan);"></h4>
                    <video id="dashboard-player" controls></video>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button class="btn-neon" onclick="alert('Marcando entrada de clip...')"><i class="fa-solid fa-scissors"></i> Recortar Entrada</button>
                        <button class="btn-neon btn-neon-magenta" onclick="alert('Exportando clip recortado...')"><i class="fa-solid fa-wand-magic-sparkles"></i> Exportar Hook</button>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Cargar PDF.js para lectura de documentos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Configurar worker de PDF.js
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    </script>
    <script src="../js/dashboard-pro.js"></script>
</body>
</html>
