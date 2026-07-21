<?php
/**
 * Dashboard Administrador - La Cueva del Güero
 * Endpoint: /dashboard/index.php
 */

session_start();
require_once __DIR__ . '/../config/config.php';

// Validar que el administrador esté autenticado
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
            --bg-primary: #080808;
            --bg-panel: rgba(18, 18, 18, 0.75);
            --neon-magenta: #FF00FF;
            --neon-cyan: #00FFFF;
            --border-color: rgba(255, 0, 255, 0.2);
            --text-main: #e6e6e6;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-main);
            font-family: 'Outfit', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 0, 255, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 255, 255, 0.05) 0px, transparent 50%);
        }

        /* HEADER */
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 40px;
            background: rgba(10, 10, 10, 0.9);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
        }

        header h1 {
            margin: 0;
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--neon-magenta);
            text-shadow: 0 0 10px var(--neon-magenta);
            letter-spacing: 1px;
        }

        header h1 span {
            color: var(--neon-cyan);
            text-shadow: 0 0 10px var(--neon-cyan);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .btn-logout {
            padding: 8px 16px;
            background: transparent;
            border: 1px solid #ff4d4d;
            color: #ff4d4d;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: #ff4d4d;
            color: #fff;
            box-shadow: 0 0 10px #ff4d4d;
        }

        /* CONTAINER LAYOUT */
        .dashboard-container {
            display: flex;
            gap: 25px;
            padding: 30px 40px;
            box-sizing: border-box;
            height: calc(100vh - 81px);
        }

        /* LEFT PANEL: EPISODES LIST */
        .panel-lista {
            width: 30%;
            background: var(--bg-panel);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.05);
        }

        .panel-lista h2 {
            margin: 0 0 20px 0;
            font-size: 1.3rem;
            color: var(--neon-cyan);
            text-shadow: 0 0 8px var(--neon-cyan);
        }

        .registros-scroll {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 5px;
        }

        .registros-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .registros-scroll::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        .registro-card {
            background: rgba(20, 20, 20, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .registro-card:hover {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
            transform: translateX(2px);
        }

        .registro-card.active {
            border-color: var(--neon-magenta);
            background: rgba(255, 0, 255, 0.05);
            box-shadow: 0 0 12px rgba(255, 0, 255, 0.2);
        }

        .registro-card h3 {
            margin: 0 0 5px 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .registro-card p {
            margin: 0;
            font-size: 0.8rem;
            color: #888;
        }

        /* RIGHT PANEL: DETAIL VIEW */
        .panel-detalle {
            width: 70%;
            background: var(--bg-panel);
            backdrop-filter: blur(10px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 25px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(255, 0, 255, 0.05);
        }

        .detalle-vacio {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #666;
            text-align: center;
        }

        .detalle-vacio i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: var(--border-color);
        }

        .detalle-contenido {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        .detalle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            padding-bottom: 15px;
        }

        .detalle-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: var(--neon-magenta);
            text-shadow: 0 0 8px var(--neon-magenta);
        }

        .detalle-scroll {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .detalle-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .detalle-scroll::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 4px;
        }

        /* SECCIONES Y TEXTAREAS */
        .seccion-asset {
            margin-bottom: 30px;
            background: rgba(10, 10, 10, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 20px;
        }

        .seccion-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .seccion-header h3 {
            margin: 0;
            color: var(--neon-cyan);
            text-shadow: 0 0 5px var(--neon-cyan);
            font-size: 1.1rem;
        }

        .btn-action-group {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            background: transparent;
            border: 1px solid var(--neon-cyan);
            color: var(--neon-cyan);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .btn-action:hover {
            background: var(--neon-cyan);
            color: #000;
            box-shadow: 0 0 8px var(--neon-cyan);
        }

        .btn-action.btn-magenta {
            border-color: var(--neon-magenta);
            color: var(--neon-magenta);
        }

        .btn-action.btn-magenta:hover {
            background: var(--neon-magenta);
            color: #fff;
            box-shadow: 0 0 8px var(--neon-magenta);
        }

        .text-block {
            background: rgba(15, 15, 15, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.03);
            padding: 15px;
            border-radius: 6px;
            font-size: 0.95rem;
            line-height: 1.5;
            white-space: pre-wrap;
            color: #ddd;
            min-height: 100px;
        }

        .edit-textarea {
            width: 100%;
            min-height: 200px;
            background: #000;
            border: 1px solid var(--neon-cyan);
            color: #fff;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.95rem;
            line-height: 1.5;
            box-sizing: border-box;
            font-family: inherit;
            resize: vertical;
        }

        .edit-textarea:focus {
            outline: none;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.3);
        }

        .btn-save-edit {
            margin-top: 10px;
            width: 100%;
            padding: 10px;
            background: var(--neon-cyan);
            border: none;
            color: #000;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-save-edit:hover {
            box-shadow: 0 0 15px var(--neon-cyan);
        }

        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>

    <header>
        <h1>LA CUEVA <span>DASHBOARD PRO</span></h1>
        <div class="user-info">
            <span>Sesión: <strong>Administrador</strong></span>
            <form action="logout.php" method="POST" style="margin: 0;">
                <button type="submit" class="btn-logout"><i class="fa-solid fa-power-off"></i> Salir</button>
            </form>
        </div>
    </header>

    <div class="dashboard-container">
        <!-- LISTA DE EPISODIOS -->
        <div class="panel-lista">
            <h2>🎙️ Episodios Generados</h2>
            <div class="registros-scroll" id="registrosContainer">
                <p style="color: #666; text-align: center;">Cargando episodios...</p>
            </div>
        </div>

        <!-- DETALLE DEL EPISODIO -->
        <div class="panel-detalle" id="panelDetalle">
            <div class="detalle-vacio" id="detalleVacio">
                <i class="fa-solid fa-microphone-lines"></i>
                <p>Selecciona un episodio de la lista para ver, descargar, imprimir o ajustar su contenido.</p>
            </div>
            
            <div class="detalle-contenido hidden" id="detalleContenido">
                <div class="detalle-header">
                    <h2 id="detalleNombre">Nombre del Invitado</h2>
                    <span id="detalleFecha" style="color: #888; font-size: 0.9rem;"></span>
                </div>
                
                <div class="detalle-scroll">
                    <!-- ESCALETA -->
                    <div class="seccion-asset">
                        <div class="seccion-header">
                            <h3><i class="fa-solid fa-list-check"></i> Escaleta de Conducción</h3>
                            <div class="btn-action-group">
                                <button class="btn-action" onclick="descargarAsset('escaleta')"><i class="fa-solid fa-download"></i></button>
                                <button class="btn-action" onclick="habilitarEdicion('escaleta')"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                            </div>
                        </div>
                        <div id="wrapper-escaleta">
                            <div class="text-block" id="block-escaleta"></div>
                        </div>
                    </div>

                    <!-- GUION -->
                    <div class="seccion-asset">
                        <div class="seccion-header">
                            <h3><i class="fa-solid fa-file-lines"></i> Guión Conversacional</h3>
                            <div class="btn-action-group">
                                <button class="btn-action" onclick="descargarAsset('guion')"><i class="fa-solid fa-download"></i></button>
                                <button class="btn-action" onclick="habilitarEdicion('guion')"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                            </div>
                        </div>
                        <div id="wrapper-guion">
                            <div class="text-block" id="block-guion"></div>
                        </div>
                    </div>

                    <!-- CUE CARDS -->
                    <div class="seccion-asset">
                        <div class="seccion-header">
                            <h3><i class="fa-solid fa-address-card"></i> Cue Cards (HTML Imprimible)</h3>
                            <div class="btn-action-group">
                                <button class="btn-action btn-magenta" onclick="imprimirCueCards()"><i class="fa-solid fa-print"></i> Imprimir</button>
                                <button class="btn-action" onclick="descargarAsset('cuecards')"><i class="fa-solid fa-download"></i></button>
                                <button class="btn-action" onclick="habilitarEdicion('cuecards')"><i class="fa-solid fa-pen-to-square"></i> Editar</button>
                            </div>
                        </div>
                        <div id="wrapper-cuecards">
                            <div class="text-block" id="block-cuecards" style="background: #151515; font-family: monospace;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/dashboard-pro.js"></script>
</body>
</html>
