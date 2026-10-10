<?php
/**
 * Dashboard PRO Completo - La Cueva del Güero
 * Endpoint: /dashboard/index.php
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';

// Acceso Libre al Dashboard PRO (Sin bloqueo de login ni OAuth)
$_SESSION['admin_logged'] = true;
$_SESSION['cueva_authenticated'] = true;
$_SESSION['admin_user'] = $_SESSION['admin_user'] ?? 'admin';
$_SESSION['admin_name'] = $_SESSION['admin_name'] ?? 'Equipo La Cueva';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard PRO - La Cueva del Güero</title>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="shortcut icon" href="/favicon.ico">
    <link rel="icon" type="image/webp" href="/images/logotipo.webp">
    <link rel="icon" type="image/png" href="/images/logotipo.png">
    <link rel="apple-touch-icon" href="/images/logotipo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Architects+Daughter&family=Montserrat+Alternates:wght@400;700&family=Luckiest+Guy&family=Permanent+Marker&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="/css/mobile-first-duo.css">
    <link rel="stylesheet" href="/css/dashboard-pro.css">
    <script>window.activeEventSource = null; window.activePollingInterval = null;</script>
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

        /* SIDEBAR (Drawer Hamburguesa Desplegable) */
        .sidebar {
            width: 320px;
            max-width: 88vw;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            box-shadow: none;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 2500;
            transform: translateX(-105%);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease;
            backdrop-filter: blur(20px);
        }

        .sidebar.active {
            transform: translateX(0);
            box-shadow: 15px 0 50px rgba(0, 255, 255, 0.2), 0 0 100px rgba(0, 0, 0, 0.9);
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

        .btn-home:hover {
            background: var(--neon-cyan) !important;
            color: #000 !important;
            box-shadow: 0 0 15px var(--neon-cyan) !important;
        }

        /* MAIN CONTENT CONTAINER */
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
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
            overflow-y: auto;
            overflow-x: hidden;
            height: 100%;
            scroll-behavior: smooth;
        }

        /* Scrollbar Neón estilizada (Slide bar vertical) */
        .subpanel-detalle::-webkit-scrollbar,
        .registros-scroll::-webkit-scrollbar {
            width: 9px;
        }

        .subpanel-detalle::-webkit-scrollbar-track,
        .registros-scroll::-webkit-scrollbar-track {
            background: rgba(4, 4, 10, 0.85);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.04);
        }

        .subpanel-detalle::-webkit-scrollbar-thumb,
        .registros-scroll::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, var(--neon-cyan), var(--neon-magenta));
            border-radius: 6px;
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .subpanel-detalle::-webkit-scrollbar-thumb:hover,
        .registros-scroll::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #39FF14, var(--neon-cyan));
            box-shadow: 0 0 15px rgba(57, 255, 20, 0.7);
        }

        /* Estilos expandidos y legibles para Escaleta, Guion y Cue Cards */
        .seccion-asset {
            margin-bottom: 22px;
            background: rgba(8, 8, 18, 0.9);
            border: 1px solid rgba(0, 255, 255, 0.2);
            border-radius: 12px;
            padding: 20px 22px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .seccion-asset:hover {
            border-color: rgba(0, 255, 255, 0.5);
            box-shadow: 0 6px 25px rgba(0, 255, 255, 0.15);
        }

        .text-block {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px;
            padding: 18px 20px;
            color: #ececf3;
            font-size: 0.95rem;
            line-height: 1.65;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: 'Outfit', sans-serif;
            min-height: 120px;
        }

        #block-cuecards {
            background: #06060e !important;
            font-family: 'Consolas', 'Courier New', monospace !important;
            color: #39FF14 !important;
            border: 1px solid rgba(57, 255, 20, 0.35) !important;
            text-shadow: 0 0 6px rgba(57, 255, 20, 0.3) !important;
            font-size: 0.92rem !important;
            letter-spacing: 0.5px;
            line-height: 1.7 !important;
            min-height: 150px;
        }

        .edit-textarea {
            width: 100%;
            min-height: 240px;
            background: #06060e;
            color: #fff;
            border: 1px solid var(--neon-cyan);
            border-radius: 8px;
            padding: 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 0.95rem;
            line-height: 1.6;
            resize: vertical;
            box-sizing: border-box;
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.2);
        }

        .btn-save-edit {
            margin-top: 12px;
            padding: 10px 20px;
            background: var(--neon-cyan);
            color: #06060c;
            border: none;
            border-radius: 6px;
            font-weight: 800;
            cursor: pointer;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .btn-save-edit:hover {
            box-shadow: 0 0 15px var(--neon-cyan);
            transform: translateY(-2px);
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

        /* ==========================================================================
           DESKTOP WORKSTATION & LARGE DISPLAY PROPORTION HARMONIZATION (>= 1200px)
           admin.lacuevadelguero.com - Preservando 100% Mobile-First
           ========================================================================== */
        @media (min-width: 1200px) {
            /* 1. Header & Navbar */
            header, .dashboard-header {
                padding: 14px 36px !important;
                background: rgba(8, 8, 16, 0.94) !important;
                border-bottom: 1px solid rgba(0, 255, 255, 0.2) !important;
                backdrop-filter: blur(14px) !important;
                box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5) !important;
                min-height: 68px;
            }
            .header-brand-wrap {
                display: flex !important;
                align-items: center !important;
                gap: 16px !important;
            }
            #view-header-title {
                font-size: clamp(1.2rem, 1.35vw, 1.55rem) !important;
                font-weight: 800 !important;
                letter-spacing: 0.5px !important;
                margin: 0 !important;
                white-space: nowrap !important;
            }
            .desktop-workstation-badge {
                display: inline-flex !important;
                align-items: center !important;
                gap: 6px !important;
                padding: 4px 12px !important;
                font-size: 0.72rem !important;
                font-weight: 800 !important;
                letter-spacing: 1px !important;
                color: var(--neon-cyan) !important;
                background: rgba(0, 255, 255, 0.08) !important;
                border: 1px solid rgba(0, 255, 255, 0.35) !important;
                border-radius: 20px !important;
                text-shadow: 0 0 8px rgba(0, 255, 255, 0.4) !important;
                box-shadow: 0 0 10px rgba(0, 255, 255, 0.1) !important;
                text-transform: uppercase;
            }
            .header-actions-bar {
                display: flex !important;
                gap: 10px !important;
                align-items: center !important;
                flex-wrap: nowrap !important;
            }
            .header-actions-bar .btn-neon {
                padding: 7px 15px !important;
                font-size: 0.8rem !important;
                font-weight: 700 !important;
                border-radius: 8px !important;
                white-space: nowrap !important;
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
                background: rgba(10, 10, 18, 0.6) !important;
                backdrop-filter: blur(8px) !important;
            }
            .header-actions-bar .btn-neon:hover {
                transform: translateY(-2px) !important;
            }
            .admin-badge {
                display: inline-flex !important;
                align-items: center !important;
                gap: 8px !important;
                padding: 6px 14px !important;
                font-size: 0.8rem !important;
                font-weight: 700 !important;
                background: rgba(0, 255, 255, 0.08) !important;
                border: 1px solid var(--neon-cyan) !important;
                border-radius: 20px !important;
                white-space: nowrap !important;
                letter-spacing: 0.5px;
            }
            .admin-badge::before {
                content: '';
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #39FF14;
                box-shadow: 0 0 8px #39FF14, 0 0 15px rgba(57, 255, 20, 0.6);
                display: inline-block;
                animation: pulseLiveDot 2s infinite ease-in-out;
            }
            @keyframes pulseLiveDot {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.6; transform: scale(0.85); }
            }

            /* 2. Área de vistas */
            .view-section {
                padding: 24px 36px !important;
                height: calc(100vh - 68px) !important;
            }

            /* 3. Episodios y Fichas: proporción fija para lista (360px) y flexible para detalle */
            .episodios-layout {
                display: flex !important;
                gap: 24px !important;
                height: 100% !important;
                align-items: stretch !important;
            }
            .subpanel-lista {
                width: 360px !important;
                min-width: 340px !important;
                max-width: 380px !important;
                flex-shrink: 0 !important;
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                border: 1px solid rgba(0, 255, 255, 0.2) !important;
                background: rgba(12, 12, 22, 0.8) !important;
            }
            .subpanel-detalle {
                flex: 1 !important;
                width: auto !important;
                max-width: calc(100% - 384px) !important;
                height: 100% !important;
                padding: 26px 32px !important;
                border: 1px solid rgba(255, 0, 255, 0.2) !important;
                background: rgba(12, 12, 22, 0.8) !important;
            }
            #ponderacion-criterios {
                grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)) !important;
                gap: 12px !important;
                max-height: 480px !important;
            }

            /* 4. Canva Editor PRO: panel de controles estilizado y viewport amplio */
            .canva-editor-workspace {
                display: grid !important;
                grid-template-columns: 390px minmax(520px, 1fr) !important;
                gap: 24px !important;
                align-items: stretch !important;
                height: calc(100vh - 165px) !important;
                min-height: 580px !important;
            }
            .canva-controls-card {
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                overflow-y: auto !important;
                background: rgba(12, 12, 22, 0.85) !important;
                border: 1px solid rgba(0, 255, 255, 0.25) !important;
                border-radius: 16px !important;
                padding: 20px !important;
                box-shadow: 0 4px 25px rgba(0, 0, 0, 0.5) !important;
            }
            .canva-viewport-card {
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: center !important;
                align-items: center !important;
                background: radial-gradient(circle at center, rgba(16, 16, 30, 0.9) 0%, rgba(6, 6, 12, 0.98) 100%) !important;
                border: 1px solid rgba(0, 255, 255, 0.25) !important;
                border-radius: 16px !important;
                padding: 24px !important;
                box-shadow: inset 0 0 50px rgba(0, 0, 0, 0.85), 0 0 30px rgba(0, 255, 255, 0.08) !important;
                position: relative !important;
                overflow: hidden !important;
            }
            #canvaCanvas {
                max-width: 100% !important;
                max-height: calc(100vh - 240px) !important;
                object-fit: contain !important;
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.9), 0 0 35px rgba(0, 255, 255, 0.2) !important;
                border: 1px solid rgba(0, 255, 255, 0.3) !important;
            }

            /* 5. Editor de Video: proporción 280px / 1fr / 340px y altura profesional */
            .video-editor-workspace {
                display: grid !important;
                grid-template-columns: 280px minmax(460px, 1fr) 340px !important;
                gap: 20px !important;
                height: min(530px, calc(100vh - 380px)) !important;
                min-height: 460px !important;
                margin-bottom: 20px !important;
                align-items: stretch !important;
            }
            .video-panel-assets, .video-panel-inspector {
                height: 100% !important;
                overflow-y: auto !important;
                background: rgba(12, 12, 22, 0.85) !important;
                border: 1px solid rgba(255, 255, 255, 0.08) !important;
            }
            .video-panel-preview {
                height: 100% !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                background: #030308 !important;
                border: 1px solid rgba(0, 255, 255, 0.25) !important;
                box-shadow: 0 0 35px rgba(0, 0, 0, 0.8) !important;
                border-radius: 14px !important;
            }
            #preview-wrapper-box {
                flex: 1 !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                overflow: hidden !important;
            }
            #editor-preview-video {
                max-height: 100% !important;
                max-width: 100% !important;
                border-radius: 8px !important;
            }
            .video-timeline-card {
                background: #0d0d16 !important;
                border: 1px solid rgba(0, 255, 255, 0.2) !important;
                border-radius: 14px !important;
                padding: 16px 20px !important;
            }
            #timeline-tracks-wrapper {
                min-height: 155px !important;
                gap: 10px !important;
            }

            /* 6. Hooks y Blog */
            .hooks-grid {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
                gap: 24px !important;
            }
            .blog-content-view {
                max-width: 1300px !important;
                margin: 0 auto !important;
            }
            #blog-content {
                min-height: 380px !important;
                font-size: 0.98rem !important;
                line-height: 1.7 !important;
            }
        }

        @media (min-width: 1600px) {
            .subpanel-lista {
                width: 380px !important;
                max-width: 400px !important;
            }
            .subpanel-detalle {
                max-width: calc(100% - 404px) !important;
                padding: 30px 42px !important;
            }
            .canva-editor-workspace {
                grid-template-columns: 420px minmax(650px, 1fr) !important;
                gap: 28px !important;
            }
            #canvaCanvas {
                max-height: calc(100vh - 220px) !important;
            }
            .video-editor-workspace {
                grid-template-columns: 310px minmax(560px, 1fr) 370px !important;
                height: min(600px, calc(100vh - 360px)) !important;
                min-height: 520px !important;
            }
            .hooks-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 28px !important;
            }
        }
        .hidden {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- OVERLAY DEL MENÚ HAMBURGUESA -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar(false)"></div>

    <!-- MENÚ HAMBURGUESA DESPLEGABLE AJUSTADO A LA PANTALLA (DRAWER OFFCANVAS) -->
    <nav class="sidebar" id="sidebarDrawer" aria-label="Navegación de Secciones">
        <button class="sidebar-close-btn" type="button" onclick="toggleSidebar(false)" title="Cerrar Menú" aria-label="Cerrar Menú">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <div class="sidebar-brand">
            <h2>LA CUEVA <span>PRO</span></h2>
            <p style="margin: 6px 0 0 0; font-size: 0.76rem; color: #8e8e9f; letter-spacing: 0.5px;">Ecosistema Digital de Producción</p>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item active" id="menu-hub" onclick="switchView('hub')">
                <i class="fa-solid fa-grip"></i> Centro de Mando (Hub)
            </li>
            <li class="menu-item" id="menu-episodios" onclick="switchView('episodios')">
                <i class="fa-solid fa-microphone"></i> Episodios y Fichas
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
            <li class="menu-item" id="menu-blog" onclick="switchView('blog')">
                <i class="fa-solid fa-pen-nib"></i> Gestor de Blog
            </li>
            <li class="menu-item" id="menu-avatar" onclick="switchView('avatar')">
                <i class="fa-solid fa-masks-theater"></i> Avatar Engine
            </li>
            <li class="menu-item" id="menu-mesa" onclick="switchView('mesa')">
                <i class="fa-solid fa-chalkboard-user"></i> Mesa de Trabajo
            </li>

            <!-- SEPARADOR DE ACCESOS RÁPIDOS Y MODALES -->
            <li style="padding: 16px 24px 6px 24px; font-size: 0.7rem; font-weight: 800; color: #55556a; text-transform: uppercase; letter-spacing: 1px;">
                Herramientas en Vivo
            </li>
            <li class="menu-item" onclick="toggleSidebar(false); if(window.location.hostname.includes('lacuevadelguero.com')){window.open('https://s.lacuevadelguero.com/', '_blank');}else{window.open('../tracking/index.html', '_blank');}">
                <i class="fa-solid fa-satellite-dish" style="color: var(--neon-green);"></i> Radar de Tracking
            </li>
            <li class="menu-item" onclick="toggleSidebar(false); window.open('../storytelling-invitado.html', '_blank');">
                <i class="fa-solid fa-clipboard-user" style="color: var(--neon-cyan);"></i> Cuestionario Invitado
            </li>
            <li class="menu-item" onclick="toggleSidebar(false); window.open('../cesion-derechos.html', '_blank');">
                <i class="fa-solid fa-file-contract" style="color: var(--neon-magenta);"></i> Cesión de Derechos
            </li>
            <li class="menu-item" onclick="toggleSidebar(false); document.getElementById('modalSubirFotoGaleria').style.display='flex';">
                <i class="fa-solid fa-camera" style="color: #00FFFF;"></i> Subir Foto Galería
            </li>
            <li class="menu-item" onclick="toggleSidebar(false); toggleCentroAvisos();">
                <i class="fa-solid fa-bell" style="color: #FFD700;"></i> Avisos & Webhook
            </li>
        </ul>

        <div class="sidebar-footer">
            <a href="../index.html" class="btn-home" style="display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px; margin-bottom: 10px; background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); border-radius: 8px; font-weight: 700; text-decoration: none; text-transform: uppercase; font-size: 0.85rem; text-shadow: 0 0 5px rgba(0,255,255,0.4); box-shadow: 0 0 5px rgba(0,255,255,0.1); box-sizing: border-box; transition: all 0.2s ease;">
                <i class="fa-solid fa-house"></i> Ir a Inicio
            </a>
            <button type="button" class="btn-logout" onclick="cerrarSesionPro()">
                <i class="fa-solid fa-power-off"></i> Cerrar Sesión
            </button>
        </div>
    </nav>

    <!-- ÁREA PRINCIPAL FULL WIDTH -->
    <div class="main-content">
        <!-- CABECERA ESTÁTICA Y FIJA -->
        <header class="dashboard-header">
            <div class="header-left-cluster">
                <!-- BOTÓN HAMBURGUESA DESPLEGABLE -->
                <button class="btn-hamburger" id="btnHamburger" type="button" onclick="toggleSidebar()" title="Abrir Menú de Secciones">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <!-- FLECHA PARA VOLVER EN TODO MOMENTO -->
                <button class="btn-header-back hidden" id="btnHeaderBack" type="button" onclick="goBackOrHub()" title="Volver a la pantalla anterior">
                    <i class="fa-solid fa-arrow-left"></i> <span>Volver</span>
                </button>

                <!-- TÍTULO ESTÁTICO DE LA CABECERA -->
                <h1 class="header-static-title">
                    La Cueva del Güero <span class="badge-pro">PRO</span>
                    <span class="header-active-view-name" id="viewHeaderSubtitle">/ Centro de Mando</span>
                </h1>
            </div>

            <!-- CLUSTER DE ACCIONES RÁPIDAS -->
            <div class="header-actions-bar" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <a href="../storytelling-invitado.html" target="_blank" class="btn-neon" style="font-size:0.8rem; padding:6px 12px; text-decoration:none; border-color:var(--neon-cyan); color:var(--neon-cyan);" title="Abrir Cuestionario para Invitados en pestaña nueva">
                    <i class="fa-solid fa-clipboard-user"></i> Cuestionario
                </a>
                <a href="../tracking/index.html" onclick="if(window.location.hostname.includes('lacuevadelguero.com')){this.href='https://s.lacuevadelguero.com/';}" target="_blank" class="btn-neon" style="font-size:0.8rem; padding:6px 12px; text-decoration:none; border-color:var(--neon-green); color:var(--neon-green);" title="Abrir Radar de Tracking de Invitados">
                    <i class="fa-solid fa-satellite-dish"></i> Tracking
                </a>
                <a href="../cesion-derechos.html" target="_blank" class="btn-neon" style="font-size:0.8rem; padding:6px 12px; text-decoration:none; border-color:var(--neon-magenta); color:var(--neon-magenta);" title="Abrir Generador de Cesión Legal">
                    <i class="fa-solid fa-file-contract"></i> Cesión
                </a>
                <button class="btn-neon" style="font-size:0.8rem; padding:6px 12px;" onclick="document.getElementById('modalSubirFotoGaleria').style.display='flex'" title="Subir imagen a la galería de producción">
                    <i class="fa-solid fa-camera"></i> Subir Foto
                </button>
                <button class="btn-neon" id="btn-centro-avisos" type="button" style="font-size:0.8rem; padding:6px 14px; border-color:#FFD700; color:#FFD700; background:rgba(255,215,0,0.1); display:inline-flex; align-items:center; gap:6px; cursor:pointer;" onclick="toggleCentroAvisos()" title="Centro de Monitoreo y Avisos en Vivo (Webhook)">
                    <i class="fa-solid fa-bell"></i> Avisos <span id="badge-avisos-count" style="background:#ff0055; color:#fff; font-size:0.7rem; font-weight:900; padding:1px 6px; border-radius:10px;">0</span>
                </button>
                <button class="btn-neon" type="button" onclick="cerrarSesionPro()" style="border-color:#ff4d4d; color:#ff4d4d; font-size:0.8rem; padding:6px 12px;" title="Cerrar Sesión de Forma Segura">
                    <i class="fa-solid fa-power-off"></i> Salir
                </button>
            </div>
        </header>

        <!-- VIEW 0: HUB DE FUNCIONES PRO (TARJETAS FLOTANTES MAGNÉTICAS CON LUZ CYAN) -->
        <section class="view-section active" id="view-hub">
            <div class="hub-container">
                <div class="hub-hero">
                    <h2>Centro de Mando & <span>Automatización PRO</span></h2>
                    <p>Selecciona una sección en las tarjetas interactivas o accede al menú hamburguesa en la cabecera. Todas las áreas cuentan con retorno inmediato.</p>
                </div>

                <!-- GRID DE TARJETAS FLOTANTES MAGNÉTICAS -->
                <div class="magnetic-grid" id="magneticGrid">
                    <!-- TARJETA 1: EPISODIOS Y FICHAS -->
                    <div class="magnetic-card" onclick="switchView('episodios')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box">
                                    <i class="fa-solid fa-microphone"></i>
                                </div>
                                <span class="card-tag">PostgreSQL Neon</span>
                            </div>
                            <h3 class="card-title">Episodios y Fichas</h3>
                            <p class="card-description">Curaduría de 33 criterios de storytelling, expedientes en vivo, generación de escaleta técnica, guiones y cue cards para set.</p>
                            <div class="card-footer-action">
                                <span>Abrir Expedientes</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 2: GENERADOR DE HOOKS -->
                    <div class="magnetic-card" onclick="switchView('hooks')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(255, 0, 255, 0.4); color: var(--neon-magenta);">
                                    <i class="fa-solid fa-magnet"></i>
                                </div>
                                <span class="card-tag">Gemini AI</span>
                            </div>
                            <h3 class="card-title">Generador de Hooks</h3>
                            <p class="card-description">Algoritmos de alta retención viral con IA para TikTok, Instagram Reels, YouTube Shorts y Spotify con copys adaptados.</p>
                            <div class="card-footer-action" style="color: var(--neon-magenta);">
                                <span>Generar Ganchos</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 3: EDITOR DE VIDEO -->
                    <div class="magnetic-card" onclick="switchView('video')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(57, 255, 20, 0.4); color: var(--neon-green);">
                                    <i class="fa-solid fa-video"></i>
                                </div>
                                <span class="card-tag">FFmpeg & NLE</span>
                            </div>
                            <h3 class="card-title">Editor de Video Multi-Pista</h3>
                            <p class="card-description">Postproducción en cabina, timeline interactivo, nivelación de audio a -14 LUFS, cortes rápidos y presets de exportación.</p>
                            <div class="card-footer-action" style="color: var(--neon-green);">
                                <span>Abrir Timeline</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 4: EDITOR CANVA PRO -->
                    <div class="magnetic-card" onclick="switchView('canva')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box">
                                    <i class="fa-solid fa-palette"></i>
                                </div>
                                <span class="card-tag">Fabric Canvas</span>
                            </div>
                            <h3 class="card-title">Editor Canva PRO</h3>
                            <p class="card-description">Compositor gráfico cyberpunk para miniaturas de YouTube, portadas de podcast, corte inteligente y activos de branding.</p>
                            <div class="card-footer-action">
                                <span>Diseñar Miniaturas</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 5: GESTOR DE BLOG -->
                    <div class="magnetic-card" onclick="switchView('blog')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(255, 0, 255, 0.4); color: var(--neon-magenta);">
                                    <i class="fa-solid fa-pen-nib"></i>
                                </div>
                                <span class="card-tag">CMS Transmedia</span>
                            </div>
                            <h3 class="card-title">Gestor de Blog</h3>
                            <p class="card-description">Conversión de fichas PDF a notas de prensa y redacción asistida por Gemini para la comunidad y posicionamiento SEO.</p>
                            <div class="card-footer-action" style="color: var(--neon-magenta);">
                                <span>Redactar Artículos</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 6: AVATAR ENGINE -->
                    <div class="magnetic-card" onclick="switchView('avatar')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box">
                                    <i class="fa-solid fa-masks-theater"></i>
                                </div>
                                <span class="card-tag">Interactive AI</span>
                            </div>
                            <h3 class="card-title">Avatar Engine</h3>
                            <p class="card-description">Personalización y control del comportamiento visual y gestual de El Güero Bot para streaming y respuestas interactivas.</p>
                            <div class="card-footer-action">
                                <span>Configurar Avatar</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 7: MESA DE TRABAJO -->
                    <div class="magnetic-card" onclick="switchView('mesa')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(255, 215, 0, 0.4); color: #FFD700;">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                </div>
                                <span class="card-tag">Auditoría & Equipo</span>
                            </div>
                            <h3 class="card-title">Mesa de Trabajo</h3>
                            <p class="card-description">Monitoreo de latencia de PostgreSQL, simulador de conversión de leads, pruebas de Stripe y votación de acuerdos en vivo.</p>
                            <div class="card-footer-action" style="color: #FFD700;">
                                <span>Abrir Mesa</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 8: RADAR DE TRACKING EN VIVO -->
                    <div class="magnetic-card" onclick="if(window.location.hostname.includes('lacuevadelguero.com')){window.open('https://s.lacuevadelguero.com/', '_blank');}else{window.open('../tracking/index.html', '_blank');}">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(57, 255, 20, 0.4); color: var(--neon-green);">
                                    <i class="fa-solid fa-satellite-dish"></i>
                                </div>
                                <span class="card-tag">S.LACUEVADELGUERO</span>
                            </div>
                            <h3 class="card-title">Radar de Tracking</h3>
                            <p class="card-description">Monitoreo en 5 fases del avance de cuestionarios de invitados, códigos de seguimiento y enlaces directos de WhatsApp.</p>
                            <div class="card-footer-action" style="color: var(--neon-green);">
                                <span>Abrir Portal Tracking</span>
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 9: CUESTIONARIO DE STORYTELLING -->
                    <div class="magnetic-card" onclick="window.open('../storytelling-invitado.html', '_blank')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box">
                                    <i class="fa-solid fa-clipboard-user"></i>
                                </div>
                                <span class="card-tag">33 Criterios</span>
                            </div>
                            <h3 class="card-title">Cuestionario Invitados</h3>
                            <p class="card-description">Formulario público dinámico para captar vivencias, momentos difíciles, frases y anécdotas auténticas de los participantes.</p>
                            <div class="card-footer-action">
                                <span>Ver Cuestionario</span>
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 10: CESIÓN DE DERECHOS -->
                    <div class="magnetic-card" onclick="window.open('../cesion-derechos.html', '_blank')">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(255, 0, 255, 0.4); color: var(--neon-magenta);">
                                    <i class="fa-solid fa-file-contract"></i>
                                </div>
                                <span class="card-tag">Legal Tech</span>
                            </div>
                            <h3 class="card-title">Cesión de Derechos</h3>
                            <p class="card-description">Contratos legales automatizados de imagen y voz con firma digitalizada listos para exportar a PDF antes de grabar.</p>
                            <div class="card-footer-action" style="color: var(--neon-magenta);">
                                <span>Generar Contrato</span>
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 11: SUBIR FOTO A GALERÍA -->
                    <div class="magnetic-card" onclick="document.getElementById('modalSubirFotoGaleria').style.display='flex'">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box">
                                    <i class="fa-solid fa-camera"></i>
                                </div>
                                <span class="card-tag">Modal Rápido</span>
                            </div>
                            <h3 class="card-title">Galería de Producción</h3>
                            <p class="card-description">Sube y cataloga fotografías detrás de cámara, invitados y momentos cumbre directo al feed público.</p>
                            <div class="card-footer-action">
                                <span>Subir Imagen</span>
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                        </div>
                    </div>

                    <!-- TARJETA 12: CENTRO DE AVISOS Y WEBHOOK -->
                    <div class="magnetic-card" onclick="toggleCentroAvisos()">
                        <div class="card-spotlight"></div>
                        <div class="card-content-wrap">
                            <div class="card-header-row">
                                <div class="card-icon-box" style="border-color: rgba(255, 215, 0, 0.4); color: #FFD700;">
                                    <i class="fa-solid fa-bell"></i>
                                </div>
                                <span class="card-tag">Webhooks en Vivo</span>
                            </div>
                            <h3 class="card-title">Centro de Avisos</h3>
                            <p class="card-description">Monitoreo de eventos síncronos, notificaciones de captación de leads y alertas operativas en set.</p>
                            <div class="card-footer-action" style="color: #FFD700;">
                                <span>Ver Alertas</span>
                                <i class="fa-solid fa-satellite-dish"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIEW 1: EPISODIOS Y FICHAS -->
        <section class="view-section" id="view-episodios">
            <div class="section-return-bar">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-folder-open"></i> Expedientes y Curaduría Neon PostgreSQL</span>
            </div>
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
                    
                    <div class="detalle-contenido hidden" id="detalleContenido" style="display: flex; flex-direction: column; width: 100%;">
                        <!-- TARJETA TIPO AURELIO — CABECERA -->
                        <div id="tarjeta-aurelio" style="background: rgba(10,10,20,0.9); border: 1px solid rgba(0,255,255,0.3); border-radius: 12px; padding: 20px 24px; margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                                <div>
                                    <h2 id="detalleNombre" style="margin: 0 0 2px 0; color: var(--neon-cyan); font-size: 1.4rem; display: flex; align-items: center; gap: 8px;">
                                        <i class="fa-solid fa-clapperboard"></i> Nombre
                                    </h2>
                                    <span id="detalleAlias" style="color: var(--neon-magenta); font-weight: 700; font-size: 0.95rem;">Alias: ---</span>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    <span id="detalleFecha" style="color: #666; font-size: 0.8rem;"></span>
                                    <button class="btn-neon" id="btn-ver-cuestionario-modal" type="button" onclick="abrirModalCuestionarioCompleto()" style="border-color: #39FF14; color: #39FF14; background: rgba(57,255,20,0.1); padding: 6px 14px; font-size: 0.8rem; border-radius: 20px; cursor: pointer; white-space: nowrap; font-weight: 700; transition: all 0.2s ease;" title="Ver cuestionario completo de 33 preguntas contestadas">
                                        <i class="fa-solid fa-clipboard-question"></i> Ver Cuestionario Completo
                                    </button>
                                    <button class="btn-neon" id="btn-tracking" onclick="const trackUrl = window.location.hostname.includes('lacuevadelguero.com') ? 'https://s.lacuevadelguero.com/' : '../tracking/index.html'; window.open(trackUrl + (activeId ? '?id=' + activeId : ''), '_blank')" style="border-color: var(--neon-cyan); color: var(--neon-cyan); padding: 6px 14px; font-size: 0.8rem; border-radius: 20px; background: transparent; cursor: pointer; white-space: nowrap;">
                                        <i class="fa-solid fa-bullseye"></i> Ver Tracking en Vivo
                                    </button>
                                </div>
                            </div>

                            <!-- GRID 2 COLUMNAS: Enfoque + Reto -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(0,255,255,0.15); border-radius: 8px; padding: 14px;">
                                    <h4 style="margin: 0 0 8px 0; color: var(--neon-cyan); font-size: 0.85rem;"><i class="fa-solid fa-bullhorn"></i> Storytelling & Enfoque:</h4>
                                    <p id="detalle-enfoque" style="margin: 0; color: #ccc; font-size: 0.9rem; line-height: 1.4;"></p>
                                </div>
                                <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(255,0,255,0.15); border-radius: 8px; padding: 14px;">
                                    <h4 style="margin: 0 0 8px 0; color: var(--neon-magenta); font-size: 0.85rem;"><i class="fa-solid fa-triangle-exclamation"></i> Reto / Momento Difícil:</h4>
                                    <p id="detalle-reto" style="margin: 0; color: #ccc; font-size: 0.9rem; line-height: 1.4;"></p>
                                </div>
                            </div>

                            <!-- FRASE -->
                            <div style="background: rgba(0,0,0,0.4); border: 1px solid rgba(0,255,255,0.15); border-radius: 8px; padding: 14px; margin-bottom: 14px;">
                                <h4 style="margin: 0 0 8px 0; color: var(--neon-cyan); font-size: 0.85rem;"><i class="fa-solid fa-quote-left"></i> Frase para la Audiencia:</h4>
                                <p id="detalle-frase" style="margin: 0; color: #eee; font-size: 1rem; font-style: italic; line-height: 1.4;"></p>
                            </div>

                            <!-- BOTÓN GUARDAR -->
                            <button class="btn-neon" onclick="alert('Ajustes guardados (próximamente)')" style="border-color: var(--neon-cyan); background: var(--neon-cyan); color: #0a0a14; padding: 8px 18px; font-size: 0.85rem; border-radius: 6px; cursor: pointer; font-weight: 700;">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar Ajustes
                            </button>
                        </div>

                        <!-- PONDERACIÓN DE CURADURÍA (33 PARÁMETROS, ESCALA 1 A 10, TOTAL 33 A 330) -->
                        <div id="ponderacion-panel" style="background: rgba(10,10,20,0.9); border: 1px solid rgba(57,255,20,0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; cursor: pointer;" onclick="togglePonderacion()">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <span id="ponderacion-score-badge" style="font-size: 1.65rem; font-weight: 900; color: #39FF14; letter-spacing: -0.5px;">0 / 330 PTS</span>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span id="ponderacion-nivel-badge" style="font-weight: 800; font-size: 0.8rem; padding: 3px 10px; border-radius: 20px; background: rgba(57,255,20,0.1); border: 1px solid #39FF14; color: #39FF14;">🟢 NIVEL ALTO</span>
                                            <span style="font-size: 0.72rem; color: #888; border: 1px solid rgba(255,255,255,0.1); padding: 2px 8px; border-radius: 10px;">33 Parámetros (Escala 1 a 10)</span>
                                        </div>
                                        <p id="ponderacion-formato" style="margin: 4px 0 0 0; font-size: 0.85rem; color: #aaa;"></p>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <button type="button" class="btn-neon" onclick="event.stopPropagation(); abrirModalCuestionarioCompleto()" style="border-color: #39FF14; color: #39FF14; background: rgba(57,255,20,0.12); padding: 4px 12px; font-size: 0.74rem; border-radius: 12px; cursor: pointer; font-weight: 700; display: inline-flex; align-items: center; gap: 5px;" title="Abrir modal con el cuestionario completo">
                                        <i class="fa-solid fa-expand"></i> Ver en Modal
                                    </button>
                                    <span style="font-size: 0.75rem; color: var(--neon-cyan); font-weight: 600;">Ver 33 Preguntas</span>
                                    <i class="fa-solid fa-chevron-down" id="ponderacion-toggle-icon" style="color: var(--neon-cyan); font-size: 0.9rem; transition: transform 0.3s;"></i>
                                </div>
                            </div>
                            <!-- CRITERIOS EXPANDIBLES (33 PARÁMETROS) -->
                            <div id="ponderacion-criterios" style="display: none; max-height: 480px; overflow-y: auto; padding-right: 6px; margin-top: 12px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px;">
                                <!-- Se llena dinámicamente por JS -->
                            </div>
                            <!-- ACCIONES DE PRODUCCIÓN -->
                            <div id="curaduria-actions" style="display: flex; gap: 10px; margin-top: 10px;"></div>
                        </div>
                        
                        <!-- SELECCIÓN DE TEMA PARA BLOG CON IA (DIRECCIONAMIENTO A PROCESAR BLOG) -->
                        <div id="tema-blog-panel" class="seccion-asset" style="border-color: rgba(255, 0, 255, 0.4); background: rgba(14, 8, 24, 0.95); margin-bottom: 22px; box-shadow: 0 4px 20px rgba(255, 0, 255, 0.12);">
                            <div class="seccion-header" style="border-bottom-color: rgba(255, 0, 255, 0.25); flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fa-solid fa-feather-pointed" style="color: var(--neon-magenta); font-size: 1.3rem; text-shadow: 0 0 10px var(--neon-magenta);"></i>
                                    <div>
                                        <h3 style="margin: 0; color: #fff; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                                            Selección de Tema para Blog <span style="font-size: 0.72rem; background: rgba(255,0,255,0.15); border: 1px solid var(--neon-magenta); color: var(--neon-magenta); padding: 2px 8px; border-radius: 12px;">IA Editorial</span>
                                        </h3>
                                        <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: var(--text-muted);">Ángulo narrativo con peso y poder de barrio extraído del episodio para desarrollar artículo.</p>
                                    </div>
                                </div>
                                <div class="btn-action-group" style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <button class="btn-neon btn-neon-magenta" id="btn-releer-tema-ia" onclick="reanalizarTemaBlogConIA()" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                        <i class="fa-solid fa-rotate"></i> 1. Releer Episodio y Generar Otro Tema
                                    </button>
                                    <button class="btn-neon" id="btn-enviar-tema-blog" onclick="generarPDFYEnviarABlog()" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border-color: #39FF14; color: #39FF14;">
                                        <i class="fa-solid fa-file-pdf"></i> 2. Generar PDF y Enviar a Procesar Blog
                                    </button>
                                </div>
                            </div>

                            <!-- CUERPO DE LA PROPUESTA TEMÁTICA -->
                            <div id="tema-blog-body" style="padding-top: 10px;">
                                <div style="margin-bottom: 12px;">
                                    <span style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; color: var(--neon-magenta); font-weight: 700;">Título Editorial Sugerido:</span>
                                    <h4 id="tema-blog-titulo" style="margin: 4px 0 0 0; font-size: 1.15rem; color: var(--neon-cyan); line-height: 1.4;">Selecciona un invitado para ver propuesta editorial...</h4>
                                </div>

                                <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 14px; margin-bottom: 14px;">
                                    <div style="background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 14px;">
                                        <h5 style="margin: 0 0 6px 0; color: var(--neon-magenta); font-size: 0.82rem;"><i class="fa-solid fa-compass"></i> Tesis & Ángulo de Peso:</h5>
                                        <p id="tema-blog-tesis" style="margin: 0; font-size: 0.88rem; color: #ddd; line-height: 1.5;">El análisis con IA identificará la tesis con mayor resonancia para la audiencia.</p>
                                    </div>
                                    <div style="background: rgba(0,0,0,0.45); border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 14px;">
                                        <h5 style="margin: 0 0 6px 0; color: var(--neon-cyan); font-size: 0.82rem;"><i class="fa-solid fa-list-ol"></i> Ejes Temáticos a Desarrollar:</h5>
                                        <ul id="tema-blog-puntos" style="margin: 0; padding-left: 18px; font-size: 0.84rem; color: #ccc; line-height: 1.45;">
                                            <li>Eje temático de origen y raíces.</li>
                                            <li>Conflicto y lección clave del episodio.</li>
                                            <li>Sabiduría aplicable para el lector.</li>
                                        </ul>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(0,0,0,0.35); border-left: 3px solid #39FF14; padding: 10px 14px; border-radius: 6px; flex-wrap: wrap; gap: 10px;">
                                    <div style="flex-grow: 1;">
                                        <span style="font-size: 0.72rem; color: #39FF14; font-weight: 700; text-transform: uppercase;">Frase / Gancho Detonador:</span>
                                        <p id="tema-blog-gancho" style="margin: 2px 0 0 0; font-size: 0.88rem; color: #fff; font-style: italic;">"Frase detonadora pendiente de análisis."</p>
                                    </div>
                                    <div style="text-align: right; min-width: 140px;">
                                        <span id="tema-blog-categoria-badge" style="font-size: 0.75rem; padding: 4px 10px; border-radius: 12px; background: rgba(0,255,255,0.1); border: 1px solid var(--neon-cyan); color: var(--neon-cyan); font-weight: 600;">Storytelling</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- RULETA INTERACTIVA DE RETOS DE CABINA CON IA -->
                        <div id="retos-cabina-panel" class="seccion-asset" style="border-color: rgba(255, 215, 0, 0.45); background: rgba(18, 14, 6, 0.95); margin-bottom: 22px; box-shadow: 0 4px 25px rgba(255, 215, 0, 0.15);">
                            <div class="seccion-header" style="border-bottom-color: rgba(255, 215, 0, 0.25); flex-wrap: wrap; gap: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fa-solid fa-dice-d20" style="color: #FFD700; font-size: 1.4rem; text-shadow: 0 0 12px #FFD700;"></i>
                                    <div>
                                        <h3 style="margin: 0; color: #fff; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                                            Ruleta de Retos & Dinámicas de Cabina <span style="font-size: 0.72rem; background: rgba(255, 215, 0, 0.15); border: 1px solid #FFD700; color: #FFD700; padding: 2px 8px; border-radius: 12px; font-weight: 700;">IA Contextual</span>
                                        </h3>
                                        <p style="margin: 2px 0 0 0; font-size: 0.8rem; color: var(--text-muted);">Recomendador interactivo de pruebas según la categoría elegida por el invitado o al azar.</p>
                                    </div>
                                </div>
                                <div class="btn-action-group" style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <button class="btn-neon" id="btn-reto-aleatorio" onclick="tirarRetoAleatorio()" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border-color: #FFD700; color: #FFD700; background: rgba(255,215,0,0.1);">
                                        <i class="fa-solid fa-dice"></i> 1. Tirar Reto Rápido
                                    </button>
                                    <button class="btn-neon btn-neon-magenta" id="btn-reto-ia" onclick="generarRetoConIA()" style="padding: 6px 14px; font-size: 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                        <i class="fa-solid fa-wand-magic-sparkles"></i> 2. Personalizar con IA al Invitado
                                    </button>
                                </div>
                            </div>

                            <!-- SELECTOR DE CATEGORÍAS EN FORMA DE BOTONES / PILLS NEÓN -->
                            <div style="padding-top: 14px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.06); padding-bottom: 12px; margin-bottom: 14px;">
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: #8e8e9f; font-weight: 700; letter-spacing: 1px; margin-right: 6px;">Categoría:</span>
                                <button type="button" class="btn-cat-reto active" id="cat-reto-aleatorio" data-cat="aleatorio" onclick="seleccionarCategoriaReto('aleatorio')" style="background: rgba(255,215,0,0.2); border: 1px solid #FFD700; color: #FFD700; padding: 4px 12px; border-radius: 14px; font-size: 0.76rem; font-weight: 700; cursor: pointer;">🎲 Sorpresa / Azar</button>
                                <button type="button" class="btn-cat-reto" id="cat-reto-destreza" data-cat="destreza" onclick="seleccionarCategoriaReto('destreza')" style="background: transparent; border: 1px solid rgba(0,255,255,0.3); color: #8e8e9f; padding: 4px 12px; border-radius: 14px; font-size: 0.76rem; font-weight: 600; cursor: pointer;">🎯 Destreza (Malabares / Equilibrio)</button>
                                <button type="button" class="btn-cat-reto" id="cat-reto-fisico" data-cat="fisico" onclick="seleccionarCategoriaReto('fisico')" style="background: transparent; border: 1px solid rgba(57,255,20,0.3); color: #8e8e9f; padding: 4px 12px; border-radius: 14px; font-size: 0.76rem; font-weight: 600; cursor: pointer;">💪 Reto Físico (Sentadillas / Fuerza)</button>
                                <button type="button" class="btn-cat-reto" id="cat-reto-artistico" data-cat="artistico" onclick="seleccionarCategoriaReto('artistico')" style="background: transparent; border: 1px solid rgba(255,0,255,0.3); color: #8e8e9f; padding: 4px 12px; border-radius: 14px; font-size: 0.76rem; font-weight: 600; cursor: pointer;">🎨 Artístico (Cantar / Dibujo / Rima)</button>
                                <button type="button" class="btn-cat-reto" id="cat-reto-callejero" data-cat="callejero" onclick="seleccionarCategoriaReto('callejero')" style="background: transparent; border: 1px solid rgba(255,77,77,0.3); color: #8e8e9f; padding: 4px 12px; border-radius: 14px; font-size: 0.76rem; font-weight: 600; cursor: pointer;">🌶️ Callejero (Salsa Brava / Bromas)</button>
                            </div>

                            <!-- TARJETA DEL RETO RECOMENDADO / GENERADO -->
                            <div id="reto-display-container" style="background: rgba(0,0,0,0.45); border: 1px solid rgba(255,215,0,0.25); border-radius: 10px; padding: 18px; position: relative;">
                                <!-- Cabecera del reto con badge y temporizador -->
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <span id="reto-categoria-badge" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; color: #FFD700; font-weight: 800; background: rgba(255,215,0,0.15); border: 1px solid #FFD700; padding: 3px 10px; border-radius: 12px;">Destreza</span>
                                        <span id="reto-origen-badge" style="font-size: 0.68rem; color: #00FFFF; margin-left: 8px; border: 1px solid rgba(0,255,255,0.3); padding: 2px 8px; border-radius: 10px;">⚡ Modo Interactivo</span>
                                        <h4 id="reto-titulo" style="margin: 8px 0 0 0; font-size: 1.25rem; color: #fff; text-shadow: 0 0 10px rgba(255,215,0,0.4);">Torre Caguamera en 45 Segundos</h4>
                                    </div>
                                    
                                    <!-- CRONÓMETRO DE CABINA -->
                                    <div style="background: rgba(10,10,20,0.85); border: 1px solid rgba(0,255,255,0.3); border-radius: 8px; padding: 8px 14px; display: flex; align-items: center; gap: 12px;">
                                        <div style="text-align: center;">
                                            <span style="font-size: 0.65rem; color: #888; text-transform: uppercase; display: block;">Tiempo Límite</span>
                                            <span id="reto-cronometro" style="font-size: 1.3rem; font-weight: 900; color: #00FFFF; font-family: monospace;">00:45</span>
                                        </div>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" id="btn-crono-start" onclick="iniciarCronometroReto()" title="Iniciar tiempo" style="background: #39FF14; border: none; color: #000; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-play"></i></button>
                                            <button type="button" id="btn-crono-pause" onclick="pausarCronometroReto()" title="Pausar" style="background: #FFD700; border: none; color: #000; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;"><i class="fa-solid fa-pause"></i></button>
                                            <button type="button" id="btn-crono-reset" onclick="reiniciarCronometroReto()" title="Reiniciar" style="background: #FF00FF; border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;"><i class="fa-solid fa-rotate-left"></i></button>
                                        </div>
                                    </div>
                                </div>

                                <!-- REGLAS Y DESCRIPCIÓN -->
                                <div style="margin-bottom: 14px; background: rgba(0,0,0,0.3); border-left: 3px solid #FFD700; padding: 12px 14px; border-radius: 4px;">
                                    <span style="font-size: 0.72rem; color: #FFD700; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 4px;">Reglas & Dinámica de Ejecución:</span>
                                    <p id="reto-reglas" style="margin: 0; color: #ddd; font-size: 0.95rem; line-height: 1.5;">Hacer equilibrio apilando 5 corcholatas o vasos sobre una botella de cerveza cerrada en menos de 45 segundos usando solo una mano.</p>
                                </div>

                                <!-- GRID 2 COLUMNAS: Contexto / Por qué este invitado + Castigo / Penitencia -->
                                <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 12px; margin-bottom: 14px;">
                                    <div style="background: rgba(0,0,0,0.35); border: 1px solid rgba(0,255,255,0.15); border-radius: 8px; padding: 12px;">
                                        <h5 style="margin: 0 0 6px 0; color: #00FFFF; font-size: 0.82rem;"><i class="fa-solid fa-bullseye"></i> Ángulo Contextual del Invitado:</h5>
                                        <p id="reto-contexto" style="margin: 0; font-size: 0.86rem; color: #ccc; line-height: 1.45;">Pone a prueba el pulso bajo presión y los nervios frente a cámara.</p>
                                    </div>
                                    <div style="background: rgba(0,0,0,0.35); border: 1px solid rgba(255,0,255,0.2); border-radius: 8px; padding: 12px;">
                                        <h5 style="margin: 0 0 6px 0; color: #FF00FF; font-size: 0.82rem;"><i class="fa-solid fa-skull"></i> Castigo / Penitencia si Falla:</h5>
                                        <p id="reto-castigo" style="margin: 0; font-size: 0.86rem; color: #ff99ff; line-height: 1.45; font-weight: 600;">Darle un trago a la salsa más picosa del set sin hacer muecas.</p>
                                    </div>
                                </div>

                                <!-- FOOTER DE LA TARJETA: Materiales necesarios + Botón para inyectar a Cue Cards -->
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 12px; flex-wrap: wrap; gap: 10px;">
                                    <div>
                                        <span style="font-size: 0.72rem; color: #888; text-transform: uppercase;">Materiales en Set:</span>
                                        <span id="reto-materiales" style="font-size: 0.82rem; color: #fff; margin-left: 6px; font-weight: 600;">1 botella de vidrio y 5 tapas/vasos</span>
                                    </div>
                                    <div style="display: flex; gap: 8px;">
                                        <button type="button" class="btn-neon" onclick="copiarRetoACueCards()" style="padding: 5px 12px; font-size: 0.75rem; border-color: #39FF14; color: #39FF14; background: rgba(57,255,20,0.1); cursor: pointer; border-radius: 4px;">
                                            <i class="fa-solid fa-copy"></i> Copiar Reto al Portapapeles / Set
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                            <!-- ESCALETA -->
                            <div class="seccion-asset">
                                <div class="seccion-header">
                                    <h3 style="margin: 0; color: var(--neon-cyan); font-size: 1.05rem;"><i class="fa-solid fa-list-check"></i> Escaleta Técnica de Producción</h3>
                                    <div class="btn-action-group">
                                        <button class="btn-action" onclick="descargarAsset('escaleta')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-file-pdf"></i> Exportar PDF</button>
                                        <button class="btn-action" onclick="habilitarEdicion('escaleta')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-pen"></i> Editar</button>
                                    </div>
                                </div>
                                <div id="wrapper-escaleta">
                                    <div class="text-block" id="block-escaleta"></div>
                                </div>
                            </div>

                            <!-- GUION -->
                            <div class="seccion-asset">
                                <div class="seccion-header">
                                    <h3 style="margin: 0; color: var(--neon-cyan); font-size: 1.05rem;"><i class="fa-solid fa-file-lines"></i> Guión para Set (El Güero & El Junior)</h3>
                                    <div class="btn-action-group">
                                        <button class="btn-action" onclick="descargarAsset('guion')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-file-pdf"></i> Exportar PDF</button>
                                        <button class="btn-action" onclick="habilitarEdicion('guion')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-pen"></i> Editar</button>
                                    </div>
                                </div>
                                <div id="wrapper-guion">
                                    <div class="text-block" id="block-guion"></div>
                                </div>
                            </div>

                            <!-- CUE CARDS -->
                            <div class="seccion-asset">
                                <div class="seccion-header">
                                    <h3 style="margin: 0; color: var(--neon-green); font-size: 1.05rem;"><i class="fa-solid fa-address-card"></i> Cue Cards para Conducción</h3>
                                    <div class="btn-action-group">
                                        <button class="btn-action btn-magenta" onclick="imprimirCueCards()" style="background: transparent; border: 1px solid var(--neon-magenta); color: var(--neon-magenta); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem; font-weight: bold; margin-right: 5px;"><i class="fa-solid fa-print"></i> Imprimir</button>
                                        <button class="btn-action" onclick="descargarAsset('cuecards')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-file-pdf"></i> Exportar PDF</button>
                                        <button class="btn-action" onclick="habilitarEdicion('cuecards')" style="background: transparent; border: 1px solid var(--neon-cyan); color: var(--neon-cyan); padding: 5px 10px; border-radius: 4px; cursor: pointer; font-size: 0.75rem;"><i class="fa-solid fa-pen"></i> Editar</button>
                                    </div>
                                </div>
                                <div id="wrapper-cuecards">
                                    <div class="text-block" id="block-cuecards"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIEW 2: GESTOR DE BLOG -->
        <section class="view-section" id="view-blog">
            <div class="section-return-bar">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-newspaper"></i> Publicaciones y Artículos Transmedia</span>
            </div>
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
                <button class="btn-neon" id="btn-blog-ai" onclick="crearPostConGemini()" style="margin-bottom:20px; width:100%; border-color:#00ffff; color:#00ffff;"><i class="fa-solid fa-brain"></i> Escribir Post con Gemini (Desde Guión del Capítulo)</button>
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
            <div class="section-return-bar">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-magnet"></i> Algoritmos Virales con Gemini AI</span>
            </div>
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
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" onclick="copyHook('facebook')" style="flex:1; padding: 6px 12px; font-size: 0.75rem;"><i class="fa-regular fa-copy"></i> Copiar</button>
                        <button class="btn-neon btn-neon-magenta" id="btn-pub-fb" onclick="publicarHookIndividual('fb', 'facebook')" style="flex:1; padding: 6px 12px; font-size: 0.75rem; border-color:#1877F2; color:#1877F2;"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
                    </div>
                </div>

                <!-- CARD: INSTAGRAM -->
                <div class="magnetic-card instagram-card" style="border-top: 3px solid #E1306C;">
                    <h4><i class="fab fa-instagram" style="color: #E1306C;"></i> Instagram Carousel</h4>
                    <div class="card-content" id="hook-instagram">Escribe un tema arriba para generar ganchos...</div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" onclick="copyHook('instagram')" style="flex:1; padding: 6px 12px; font-size: 0.75rem;"><i class="fa-regular fa-copy"></i> Copiar</button>
                        <button class="btn-neon btn-neon-magenta" id="btn-pub-ig" onclick="publicarHookIndividual('ig', 'instagram')" style="flex:1; padding: 6px 12px; font-size: 0.75rem; border-color:#E1306C; color:#E1306C;"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
                    </div>
                </div>

                <!-- CARD: TIKTOK -->
                <div class="magnetic-card tiktok-card" style="border-top: 3px solid #000;">
                    <h4><i class="fab fa-tiktok" style="color: #fff;"></i> TikTok Hook</h4>
                    <div class="card-content" id="hook-tiktok">Escribe un tema arriba para generar ganchos...</div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" onclick="copyHook('tiktok')" style="flex:1; padding: 6px 12px; font-size: 0.75rem;"><i class="fa-regular fa-copy"></i> Copiar</button>
                        <button class="btn-neon btn-neon-magenta" id="btn-pub-tk" onclick="publicarHookIndividual('tk', 'tiktok')" style="flex:1; padding: 6px 12px; font-size: 0.75rem; border-color:#fff; color:#fff;"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
                    </div>
                </div>

                <!-- CARD: SPOTIFY -->
                <div class="magnetic-card spotify-card" style="border-top: 3px solid #1DB954;">
                    <h4><i class="fab fa-spotify" style="color: #1DB954;"></i> Spotify Intro Teaser</h4>
                    <div class="card-content" id="hook-spotify">Escribe un tema arriba para generar ganchos...</div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" onclick="copyHook('spotify')" style="flex:1; padding: 6px 12px; font-size: 0.75rem;"><i class="fa-regular fa-copy"></i> Copiar</button>
                        <button class="btn-neon btn-neon-magenta" id="btn-pub-sp" onclick="publicarHookIndividual('sp', 'spotify')" style="flex:1; padding: 6px 12px; font-size: 0.75rem; border-color:#1DB954; color:#1DB954;"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
                    </div>
                </div>

                <!-- CARD: YOUTUBE SHORTS -->
                <div class="magnetic-card shorts-card" style="border-top: 3px solid #FF0000;">
                    <h4><i class="fab fa-youtube" style="color: #FF0000;"></i> YouTube Shorts</h4>
                    <div class="card-content" id="hook-shorts">Escribe un tema arriba para generar ganchos...</div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" onclick="copyHook('shorts')" style="flex:1; padding: 6px 12px; font-size: 0.75rem;"><i class="fa-regular fa-copy"></i> Copiar</button>
                        <button class="btn-neon btn-neon-magenta" id="btn-pub-shorts" onclick="publicarHookIndividual('yt', 'shorts')" style="flex:1; padding: 6px 12px; font-size: 0.75rem; border-color:#FF0000; color:#FF0000;"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
                    </div>
                </div>

                <!-- CARD: YOUTUBE LONG -->
                <div class="magnetic-card youtube-card" style="border-top: 3px solid #FF0000;">
                    <h4><i class="fab fa-youtube" style="color: #FF0000;"></i> YouTube Videos</h4>
                    <div class="card-content" id="hook-youtube">Escribe un tema arriba para generar ganchos...</div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn-neon" onclick="copyHook('youtube')" style="flex:1; padding: 6px 12px; font-size: 0.75rem;"><i class="fa-regular fa-copy"></i> Copiar</button>
                        <button class="btn-neon btn-neon-magenta" id="btn-pub-youtube" onclick="publicarHookIndividual('yt', 'youtube')" style="flex:1; padding: 6px 12px; font-size: 0.75rem; border-color:#FF0000; color:#FF0000;"><i class="fa-solid fa-paper-plane"></i> Publicar</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIEW 4: EDITOR DE VIDEO (LA CUEVA VIDEO EDITOR PRO) -->
        <section class="view-section" id="view-video" style="padding: 15px 25px;">
            <div class="section-return-bar" style="margin-bottom: 15px;">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-video"></i> Postproducción NLE & Masterización -14 LUFS</span>
            </div>
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
            <div class="video-editor-workspace" style="display:grid; grid-template-columns: 240px 1fr 280px; gap:15px; height:380px; align-items:stretch; margin-bottom:15px;">
                <!-- PANEL IZQUIERDO: BIBLIOTECA & MODELOS IA -->
                <div class="video-panel-assets" style="background:rgba(15,15,15,0.8); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:15px; overflow-y:auto;">
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
                <div class="video-panel-preview" style="background:#050508; border:1px solid rgba(255,255,255,0.05); border-radius:12px; display:flex; flex-direction:column; justify-content:space-between; padding:15px; position:relative; overflow:hidden;">
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
                <div class="video-panel-inspector" style="background:rgba(15,15,15,0.8); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:15px; overflow-y:auto; font-size:0.8rem;">
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
                    <!-- HERRAMIENTAS DE LIMPIEZA PRO IA (FFMPEG/WHISPER) -->
                    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:10px; display:flex; flex-direction:column; gap:8px;">
                        <span style="color:#aaa; font-weight:bold;"><i class="fa-solid fa-wand-magic-sparkles"></i> Limpieza Pro IA</span>
                        <div>
                            <label style="font-size:0.7rem; color:#666;">Corte de Silencios (FFmpeg):</label>
                            <div style="display:flex; gap:5px; margin-top:3px;">
                                <select id="editor-silence-time" class="form-input" style="padding:6px; font-size:0.75rem; flex:1;">
                                    <option value="0.5">Recortar silencios > 0.5s</option>
                                    <option value="1.0" selected>Recortar silencios > 1.0s</option>
                                    <option value="1.5">Recortar silencios > 1.5s</option>
                                </select>
                                <button class="btn-neon" onclick="ejecutarLimpiezaIA('trim-silences')" style="padding:6px 10px; font-size:0.75rem;"><i class="fa-solid fa-scissors"></i> Cortar</button>
                            </div>
                        </div>
                        <div>
                            <label style="font-size:0.7rem; color:#666;">Remover Muletillas (Whisper):</label>
                            <input type="text" id="editor-filler-words" class="form-input" value="eh,este,pues,o sea" style="padding:6px; font-size:0.75rem; margin-bottom:5px;">
                            <button class="btn-neon" onclick="ejecutarLimpiezaIA('remove-filler')" style="width:100%; padding:6px; font-size:0.75rem;"><i class="fa-solid fa-microphone-slash"></i> Limpiar Vocabulario</button>
                        </div>
                        <div>
                            <label style="font-size:0.7rem; color:#666;">Cortes Multicámara (J / L):</label>
                            <button class="btn-neon" onclick="ejecutarLimpiezaIA('jl-cuts')" style="width:100%; padding:6px; font-size:0.75rem; border-color:var(--neon-magenta); color:var(--neon-magenta);"><i class="fa-solid fa-rotate"></i> Aplicar Cortes J y L</button>
                        </div>
                        <div>
                            <label style="font-size:0.7rem; color:#666;">Loudness Spotify/YouTube (-14 LUFS):</label>
                            <button class="btn-neon" onclick="ejecutarLimpiezaIA('loudnorm-spotify')" style="width:100%; padding:6px; font-size:0.75rem;"><i class="fa-solid fa-music"></i> Normalizar Sonoridad</button>
                        </div>
                    </div>
                    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;">
                        <span style="color:#aaa; font-weight:bold;">Subtítulos IA</span>
                        <textarea class="form-input" style="min-height:60px; font-size:0.75rem; margin-top:5px;" placeholder="Los subtítulos generados por IA aparecerán aquí..."></textarea>
                    </div>
                </div>
            </div>

            <!-- TIMELINE (BOTTOM PANEL) -->
            <div class="video-timeline-card" style="background:#0f0f15; border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px; position:relative;">
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
                <div style="display:flex; flex-direction:column; gap:8px; background:rgba(0,0,0,0.4); border-radius:8px; padding:10px; position:relative; min-height:140px;" id="timeline-tracks-wrapper">
                    <!-- Cabezal de reproducción rojo -->
                    <div id="timeline-progress" style="position:absolute; top:0; bottom:0; left:0; width:2px; background:#ff4d4d; z-index:10; box-shadow:0 0 8px #ff4d4d;">
                        <div style="width:10px; height:10px; background:#ff4d4d; border-radius:50%; margin-left:-4px; margin-top:-4px;"></div>
                    </div>

                    <!-- PISTA DE MARCADORES VIRALES (IA HOOKS) -->
                    <div id="viral-markers-track" style="height:18px; position:relative; border-bottom:1px dashed rgba(255,255,255,0.1); margin-bottom:2px;">
                        <div class="viral-marker" style="position:absolute; left:18%; top:0; background:#ffa500; color:#000; font-size:0.65rem; font-weight:bold; padding:1px 6px; border-radius:10px; cursor:pointer;" title="Hook Viral Detectado por Gemini" onclick="saltarAMarcador(18)">
                            <i class="fa-solid fa-bolt"></i> Hook 1
                        </div>
                        <div class="viral-marker" style="position:absolute; left:48%; top:0; background:#FF00FF; color:#fff; font-size:0.65rem; font-weight:bold; padding:1px 6px; border-radius:10px; cursor:pointer;" title="Momento Picante Detectado" onclick="saltarAMarcador(48)">
                            <i class="fa-solid fa-fire"></i> Momento Clave
                        </div>
                        <div class="viral-marker" style="position:absolute; left:78%; top:0; background:#00FFFF; color:#000; font-size:0.65rem; font-weight:bold; padding:1px 6px; border-radius:10px; cursor:pointer;" title="Cierre Impactante" onclick="saltarAMarcador(78)">
                            <i class="fa-solid fa-star"></i> Frase Cierre
                        </div>
                    </div>

                    <!-- PISTA SUBTÍTULOS -->
                    <div id="subtitles-track" style="display:none; height:24px; background:rgba(0,255,255,0.1); border:1px solid var(--neon-cyan); border-radius:4px; font-size:0.7rem; color:#00FFFF; padding-left:10px; line-height:22px; position:relative;">
                        <i class="fa-solid fa-closed-captioning"></i> [IA Subtítulos Generados] "A los 10 años, mi papá me mandó a la calle..."
                    </div>

                    <!-- PISTA VIDEO (MULTICAPA DRAGGABLE) -->
                    <div id="track-video" class="timeline-clip-track" style="height:32px; background:rgba(255,0,255,0.12); border:1px solid var(--neon-magenta); border-radius:4px; font-size:0.75rem; color:#FF00FF; padding:0 10px; display:flex; align-items:center; justify-content:space-between; position:relative; overflow:hidden; cursor:grab;" draggable="true">
                        <span><i class="fa-solid fa-video"></i> <span id="track-video-label">Video_Principal.mp4</span></span>
                        <div class="clip-trim-handle" style="width:12px; height:100%; background:rgba(255,0,255,0.3); border-left:2px solid #FF00FF; cursor:ew-resize;" title="Ajustar recorte"></div>
                    </div>

                    <!-- PISTA AUDIO & WAVEFORM -->
                    <div id="track-audio" style="height:44px; background:rgba(78,252,34,0.06); border:1px solid #4EFC22; border-radius:6px; position:relative; overflow:hidden; display:flex; align-items:center;">
                        <div style="position:absolute; left:8px; top:4px; z-index:3; font-size:0.7rem; color:#4EFC22; background:rgba(0,0,0,0.7); padding:2px 6px; border-radius:4px; pointer-events:none;">
                            <i class="fa-solid fa-waveform-lines"></i> Audio / Voz Waveform
                        </div>
                        <div id="waveform" style="width:100%; height:100%; z-index:2;"></div>
                    </div>

                    <!-- PISTA FX / MÚSICA DE FONDO (DRAGGABLE) -->
                    <div id="track-fx" class="timeline-clip-track" style="height:26px; background:rgba(0,255,255,0.08); border:1px dashed #00FFFF; border-radius:4px; font-size:0.7rem; color:#00FFFF; padding:0 10px; display:flex; align-items:center; justify-content:space-between; position:relative; cursor:grab;" draggable="true">
                        <span><i class="fa-solid fa-music"></i> Pista FX / Fondo Urbano (Beat La Cueva)</span>
                        <div class="clip-trim-handle" style="width:10px; height:100%; background:rgba(0,255,255,0.2); border-left:1px solid #00FFFF; cursor:ew-resize;"></div>
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

        <!-- CONSOLA DE LOGS IA (FFMPEG/WHISPER) -->
        <div id="editor-console-overlay" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.9); z-index:100001; justify-content:center; align-items:center;">
            <div style="background:#050508; border:2px solid var(--neon-cyan); border-radius:15px; width:90%; max-width:600px; padding:20px; box-shadow:0 0 30px rgba(0,255,255,0.3); display:flex; flex-direction:column; gap:15px;">
                <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid rgba(0,255,255,0.2); padding-bottom:10px;">
                    <span style="font-family:'Outfit', sans-serif; font-weight:800; color:#00FFFF; font-size:1.05rem; text-shadow:0 0 8px #00FFFF;"><i class="fa-solid fa-terminal"></i> Terminal de Procesamiento FFmpeg & Whisper IA</span>
                    <button onclick="cerrarConsolaEditor()" style="background:none; border:none; color:#fff; font-size:1.3rem; cursor:pointer;">&times;</button>
                </div>
                <div id="editor-console-screen" style="background:#000; border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:15px; height:280px; overflow-y:auto; font-family:monospace; font-size:0.8rem; color:#39FF14; display:flex; flex-direction:column; gap:6px; box-shadow:inset 0 0 10px rgba(0,0,0,0.8);">
                    <div style="color:#888;">> Inicializando terminal de edición de La Cueva...</div>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.8rem; color:#aaa;">
                    <span id="editor-console-status">Estado: Listo</span>
                    <button class="btn-neon" id="editor-console-accept" onclick="cerrarConsolaEditor()" style="display:none; padding:5px 15px; font-size:0.75rem;">Aplicar Cambios</button>
                </div>
            </div>
        </div>

        <!-- MODAL: IMPORTADOR DESDE LA NUBE -->
        <div id="modalImportarNube" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); z-index:9999; justify-content:center; align-items:center;">
            <div style="background:rgba(15,15,15,0.98); border:2px solid var(--neon-cyan); border-radius:20px; padding:30px; width:90%; max-width:640px; box-shadow:0 0 40px rgba(0,255,255,0.3);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid rgba(0,255,255,0.2); padding-bottom:10px;">
                    <h3 style="margin:0; color:#00FFFF; font-family:'Outfit', sans-serif;"><i class="fa-solid fa-cloud-arrow-up"></i> Sincronizador de Almacenamiento en la Nube (OAuth API)</h3>
                    <button onclick="cerrarImportarNube()" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
                </div>
                
                <p style="color:#aaa; font-size:0.85rem; margin-bottom:20px;">Inicia sesión para conectar tus cuentas en la nube y sincronizar tus archivos de video, audio e imágenes directamente a la Cueva:</p>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
                    <!-- GOOGLE DRIVE / ARCHIVOS -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <h4 style="color:#ffb703; margin:0; font-size:0.9rem;"><i class="fab fa-google-drive"></i> Google Drive</h4>
                                <span id="status-google" class="cloud-status-badge" style="font-size:0.65rem; color:#ff4d4d; border:1px solid #ff4d4d; padding:2px 6px; border-radius:4px; font-weight:bold;">Desconectado</span>
                            </div>
                            <ul id="list-google" style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:none; flex-direction:column; gap:6px;">
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Google Drive', 'Invitado_Guero_Ep18.png')"><i class="fa-regular fa-image" style="color:var(--neon-cyan);"></i> Invitado_Guero_Ep18.png</a></li>
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Google Drive', 'Grabacion_Calle_Ep18.mp4')"><i class="fa-regular fa-file-video" style="color:var(--neon-magenta);"></i> Grabacion_Calle_Ep18.mp4</a></li>
                            </ul>
                        </div>
                        <button class="btn-neon" id="btn-google" style="width:100%; font-size:0.75rem; margin-top:12px; padding:5px 8px;" onclick="authCloudPlatform('Google Drive', 'google')"><i class="fa-solid fa-key"></i> Iniciar Sesión / Sincronizar</button>
                    </div>

                    <!-- DROPBOX -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <h4 style="color:#007fff; margin:0; font-size:0.9rem;"><i class="fab fa-dropbox"></i> Dropbox</h4>
                                <span id="status-dropbox" class="cloud-status-badge" style="font-size:0.65rem; color:#ff4d4d; border:1px solid #ff4d4d; padding:2px 6px; border-radius:4px; font-weight:bold;">Desconectado</span>
                            </div>
                            <ul id="list-dropbox" style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:none; flex-direction:column; gap:6px;">
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Dropbox', 'Junior_Foto_Promo.webp')"><i class="fa-regular fa-image" style="color:var(--neon-cyan);"></i> Junior_Foto_Promo.webp</a></li>
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('Dropbox', 'Audio_Master_Ep18.wav')"><i class="fa-regular fa-file-audio" style="color:#39FF14;"></i> Audio_Master_Ep18.wav</a></li>
                            </ul>
                        </div>
                        <button class="btn-neon" id="btn-dropbox" style="width:100%; font-size:0.75rem; margin-top:12px; padding:5px 8px;" onclick="authCloudPlatform('Dropbox', 'dropbox')"><i class="fa-solid fa-key"></i> Iniciar Sesión / Sincronizar</button>
                    </div>

                    <!-- MICROSOFT ONEDRIVE -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <h4 style="color:#00a4ef; margin:0; font-size:0.9rem;"><i class="fa-solid fa-cloud"></i> OneDrive</h4>
                                <span id="status-onedrive" class="cloud-status-badge" style="font-size:0.65rem; color:#ff4d4d; border:1px solid #ff4d4d; padding:2px 6px; border-radius:4px; font-weight:bold;">Desconectado</span>
                            </div>
                            <ul id="list-onedrive" style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:none; flex-direction:column; gap:6px;">
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('OneDrive', 'Graffiti_Background.jpg')"><i class="fa-regular fa-image" style="color:var(--neon-cyan);"></i> Graffiti_Background.jpg</a></li>
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('OneDrive', 'Ficha_Tecnica_Ep18.pdf')"><i class="fa-regular fa-file-pdf" style="color:#ff4d4d;"></i> Ficha_Tecnica_Ep18.pdf</a></li>
                            </ul>
                        </div>
                        <button class="btn-neon" id="btn-onedrive" style="width:100%; font-size:0.75rem; margin-top:12px; padding:5px 8px;" onclick="authCloudPlatform('OneDrive', 'onedrive')"><i class="fa-solid fa-key"></i> Iniciar Sesión / Sincronizar</button>
                    </div>

                    <!-- TERABOX -->
                    <div style="background:rgba(255,255,255,0.03); border:1px solid rgba(0,255,255,0.1); border-radius:12px; padding:15px; display:flex; flex-direction:column; justify-content:space-between;">
                        <div>
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <h4 style="color:#4caf50; margin:0; font-size:0.9rem;"><i class="fa-solid fa-server"></i> TeraBox</h4>
                                <span id="status-terabox" class="cloud-status-badge" style="font-size:0.65rem; color:#ff4d4d; border:1px solid #ff4d4d; padding:2px 6px; border-radius:4px; font-weight:bold;">Desconectado</span>
                            </div>
                            <ul id="list-terabox" style="list-style:none; padding:0; margin:0; font-size:0.8rem; display:none; flex-direction:column; gap:6px;">
                                <li><a href="#" style="color:#fff; text-decoration:none;" onclick="seleccionarArchivoNube('TeraBox', 'Clip_Crudo_Entrevista_4K.mov')"><i class="fa-regular fa-file-video" style="color:var(--neon-magenta);"></i> Clip_Crudo_Entrevista_4K.mov</a></li>
                            </ul>
                        </div>
                        <button class="btn-neon" id="btn-terabox" style="width:100%; font-size:0.75rem; margin-top:12px; padding:5px 8px;" onclick="authCloudPlatform('TeraBox', 'terabox')"><i class="fa-solid fa-key"></i> Iniciar Sesión / Sincronizar</button>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button class="btn-neon" onclick="cerrarImportarNube()">Cancelar</button>
                </div>
            </div>
        </div>

        <!-- VIEW 5: EDITOR CANVA PRO (GIMP/CANVA/EXPRESS ALTERNATIVE) -->
        <section class="view-section" id="view-canva">
            <div class="section-return-bar">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-palette"></i> Suite Gráfica Cyberpunk & Miniaturas YouTube</span>
            </div>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Diseño y composición profesional (Photoshop & Canva). Sube imágenes locales o sincroniza tus archivos desde la nube, aplica capas, filtros cyberpunk y tipografía neón.</p>

            <div class="canva-editor-workspace" style="display: grid; grid-template-columns: 360px 1fr; gap: 25px; align-items: start;">
                <!-- CONTROLES TABULADOS -->
                <div class="canva-controls-card" style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-cyan); border-radius: 16px; padding: 15px; display:flex; flex-direction:column; gap:15px;">
                    <!-- HEADER TABS -->
                    <div style="display:flex; border-bottom:1px solid rgba(0,255,255,0.2); padding-bottom:10px; gap:5px;">
                        <button class="btn-neon active" id="canva-tab-cloud" onclick="switchCanvaTab('cloud')" style="flex:1; font-size:0.7rem; padding:6px 4px;"><i class="fa-solid fa-cloud"></i> Nube / Presets</button>
                        <button class="btn-neon" id="canva-tab-layers" onclick="switchCanvaTab('layers')" style="flex:1; font-size:0.7rem; padding:6px 4px;"><i class="fa-solid fa-layer-group"></i> Capas / Filtros</button>
                        <button class="btn-neon" id="canva-tab-text" onclick="switchCanvaTab('text')" style="flex:1; font-size:0.7rem; padding:6px 4px;"><i class="fa-solid fa-font"></i> Texto</button>
                    </div>

                    <!-- TAB 1: CLOUD STORAGE & PRESETS -->
                    <div id="canva-panel-cloud" style="display:flex; flex-direction:column; gap:12px;">
                        <h4 style="color:#00FFFF; margin:0; font-size:0.85rem;"><i class="fa-solid fa-cloud-arrow-up"></i> Conectores y Cuentas Cloud (OAuth)</h4>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                            <button class="btn-neon" onclick="conectarCloudCanva('Google Drive')" style="font-size:0.7rem; padding:6px;"><i class="fab fa-google-drive"></i> Google Drive</button>
                            <button class="btn-neon" onclick="conectarCloudCanva('OneDrive')" style="font-size:0.7rem; padding:6px;"><i class="fa-solid fa-cloud"></i> OneDrive</button>
                            <button class="btn-neon" onclick="conectarCloudCanva('Dropbox')" style="font-size:0.7rem; padding:6px;"><i class="fab fa-dropbox"></i> Dropbox</button>
                            <button class="btn-neon" onclick="conectarCloudCanva('TeraBox')" style="font-size:0.7rem; padding:6px;"><i class="fa-solid fa-server"></i> TeraBox</button>
                        </div>
                        
                        <div class="form-group" style="margin-top:10px;">
                            <label style="font-size:0.8rem; color:#aaa;">Subir desde Dispositivo</label>
                            <input type="file" id="canvaFileInput" accept="image/*" class="form-input" style="padding:6px; font-size:0.75rem;">
                        </div>

                        <div class="form-group">
                            <label style="font-size:0.8rem; color:#aaa;">Formato del Lienzo (Adobe Express Presets)</label>
                            <select id="canva-preset-size" class="form-input" onchange="resizeCanvaPreset()" style="padding:8px; font-size:0.75rem;">
                                <option value="youtube">Miniatura YouTube (16:9 - 1280x720)</option>
                                <option value="instagram">Post Instagram (1:1 - 1080x1080)</option>
                                <option value="tiktok">TikTok / Historia (9:16 - 1080x1920)</option>
                                <option value="facebook">Banner Facebook (820x312)</option>
                            </select>
                        </div>
                    </div>

                    <!-- TAB 2: LAYERS & FILTERS -->
                    <div id="canva-panel-layers" style="display:none; flex-direction:column; gap:12px;">
                        <h4 style="color:#FF00FF; margin:0; font-size:0.85rem;"><i class="fa-solid fa-layer-group"></i> Administrador de Capas (Photoshop)</h4>
                        <div style="display:flex; flex-direction:column; gap:6px; background:rgba(0,0,0,0.3); border-radius:8px; padding:10px;" id="canva-layers-list">
                            <!-- Capas cargadas de forma dinamica -->
                            <div style="font-size:0.75rem; color:#888; text-align:center;">Ninguna imagen cargada</div>
                        </div>

                        <div style="display:flex; gap:8px;">
                            <button class="btn-neon" onclick="insertarLogoCanva('redondo')" style="flex:1; font-size:0.7rem; padding:6px;"><i class="fa-solid fa-circle"></i> Sello Logo</button>
                            <button class="btn-neon" onclick="insertarLogoCanva('letras')" style="flex:1; font-size:0.7rem; padding:6px;"><i class="fa-solid fa-signature"></i> Firma Logo</button>
                        </div>
                        <button class="btn-neon btn-neon-magenta" style="width:100%; font-size:0.75rem; padding:8px;" onclick="removerFondoCanva()"><i class="fa-solid fa-scissors"></i> IA Eliminar Fondo (Rembg)</button>

                        <h4 style="color:#fff; margin:10px 0 0 0; font-size:0.85rem; border-top:1px solid rgba(255,255,255,0.05); padding-top:10px;"><i class="fa-solid fa-wand-magic-sparkles"></i> Filtros y LUTs de Estilo</h4>
                        <select id="canva-filter-preset" class="form-input" onchange="applyCanvaFiltersPreset()" style="padding:8px; font-size:0.75rem;">
                            <option value="none">Original (Sin Filtro)</option>
                            <option value="cyberpunk">Cyberpunk (Cyan & Magenta Glow)</option>
                            <option value="street">Warm Street (Calor Callejero)</option>
                            <option value="neon-glow">Neon Glow Haze</option>
                            <option value="monochrome">Contraste Blanco y Negro</option>
                        </select>
                    </div>

                    <!-- TAB 3: NEON TYPOGRAPHY -->
                    <div id="canva-panel-text" style="display:none; flex-direction:column; gap:12px;">
                        <h4 style="color:#00FFFF; margin:0; font-size:0.85rem;"><i class="fa-solid fa-font"></i> Tipografías Neón (Canva Pro)</h4>
                        <div class="form-group">
                            <label style="font-size:0.8rem; color:#aaa;">Texto a Insertar</label>
                            <input type="text" id="canva-text-input" class="form-input" placeholder="Ej: LA CUEVA" value="LA CUEVA">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.8rem; color:#aaa;">Fuente Google Fonts</label>
                            <select id="canva-text-font" class="form-input" style="padding:8px; font-size:0.75rem;">
                                <option value="Architects Daughter" style="font-family:'Architects Daughter', cursive;">Architects Daughter</option>
                                <option value="Montserrat Alternates" style="font-family:'Montserrat Alternates', sans-serif;">Montserrat Alternates</option>
                                <option value="Luckiest Guy" style="font-family:'Luckiest Guy', cursive;">Luckiest Guy</option>
                                <option value="Permanent Marker" style="font-family:'Permanent Marker', cursive;" selected>Permanent Marker</option>
                            </select>
                        </div>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                            <div>
                                <label style="font-size:0.7rem; color:#666;">Tamaño (px):</label>
                                <input type="number" id="canva-text-size" class="form-input" value="60" style="padding:6px; font-size:0.75rem;">
                            </div>
                            <div>
                                <label style="font-size:0.7rem; color:#666;">Color Brillo:</label>
                                <select id="canva-text-glow" class="form-input" style="padding:6px; font-size:0.75rem;">
                                    <option value="#00FFFF">Cyan 💎</option>
                                    <option value="#FF00FF" selected>Magenta 💖</option>
                                    <option value="#39FF14">Verde 🍏</option>
                                    <option value="#ffa500">Naranja 🍊</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn-neon" onclick="agregarTextoLienzo()" style="width:100%; font-size:0.75rem; padding:8px;"><i class="fa-solid fa-plus"></i> Añadir Capa de Texto</button>
                    </div>

                    <!-- SMART TYPOGRAPHY & THE DARKROOM BUTTONS -->
                    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:10px; display:flex; flex-direction:column; gap:6px;">
                        <button class="btn-neon" style="width:100%; font-size:0.75rem; padding:8px; border-color:var(--neon-magenta); color:var(--neon-magenta);" onclick="generarPosterAutomatico('youtube-hero')">
                            <i class="fa-solid fa-wand-magic-sparkles"></i> Poster Automático (Smart Typography)
                        </button>
                        <div style="display:flex; gap:6px;">
                            <button class="btn-neon" style="flex:1; font-size:0.7rem; padding:6px;" onclick="guardarSesionDarkroom()">
                                <i class="fa-solid fa-cloud-arrow-up"></i> Guardar Sesión
                            </button>
                            <button class="btn-neon" style="flex:1; font-size:0.7rem; padding:6px; border-color:#39FF14; color:#39FF14;" onclick="exportarEstandarizadoDarkroom()">
                                <i class="fa-solid fa-layer-group"></i> Exportar 3 Formatos
                            </button>
                        </div>
                    </div>

                    <!-- DESCARGAR/LIMPIAR -->
                    <div style="border-top:1px solid rgba(255,255,255,0.05); padding-top:10px; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; gap:8px;">
                            <button class="btn-neon" style="flex:1; font-size:0.75rem; padding:6px;" onclick="exportarImagenCanva('png')"><i class="fa-solid fa-download"></i> PNG</button>
                            <button class="btn-neon" style="flex:1; font-size:0.75rem; padding:6px;" onclick="exportarImagenCanva('jpeg')">JPEG</button>
                            <button class="btn-neon" style="flex:1; font-size:0.75rem; padding:6px;" onclick="exportarImagenCanva('webp')">WEBP</button>
                        </div>
                        <button class="btn-neon" style="width:100%; border-color:#ff4d4d; color:#ff4d4d; padding:6px; font-size:0.75rem;" onclick="limpiarAreaPoster()"><i class="fa-solid fa-trash-can"></i> Limpiar Lienzo</button>
                    </div>
                </div>

                <!-- LIENZO HTML5 -->
                <div class="canva-viewport-card" style="background: rgba(0,0,0,0.5); border: 1px dashed rgba(0,255,255,0.3); border-radius: 16px; padding: 20px; text-align: center; min-height: 450px; display: flex; justify-content: center; align-items: center;">
                    <canvas id="canvaCanvas" style="max-width:100%; max-height:550px; border-radius:10px; box-shadow:0 0 20px rgba(0,0,0,0.8);"></canvas>
                </div>
            </div>
        </section>

        <!-- VIEW 6: AVATAR ENGINE -->
        <section class="view-section" id="view-avatar">
            <div class="section-return-bar">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-masks-theater"></i> Generador de Personajes & Comportamiento Visual</span>
            </div>
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

        <!-- VIEW 7: MESA DE TRABAJO INTERACTIVA (AUDITORÍA & LEADS CONTROLLER) -->
        <section class="view-section" id="view-mesa" style="overflow-y:auto; padding: 25px;">
            <div class="section-return-bar">
                <button class="btn-section-back" type="button" onclick="switchView('hub')">
                    <i class="fa-solid fa-arrow-left"></i> Volver al Centro de Mando
                </button>
                <span style="font-size:0.85rem; color:#8e8e9f;"><i class="fa-solid fa-chalkboard-user"></i> Mesa de Trabajo, Auditoría & Toma de Decisiones</span>
            </div>
            <p style="color: var(--text-muted); margin-bottom: 25px;">Mesa de control interactiva para auditoría del sistema, simulación de pagos Stripe, mapeo de flujos y colaboración del equipo.</p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; align-items: start;">
                <!-- PANEL: CONEXIONES & STRIPE SIMULATOR -->
                <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-cyan); border-radius: 16px; padding: 20px; display:flex; flex-direction:column; gap:18px; box-shadow: 0 0 15px rgba(0,255,255,0.1);">
                    <h3 style="color:#00FFFF; margin:0; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-server"></i> Conexión DB & Stripe Gateway</h3>
                    
                    <!-- Live DB Test -->
                    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(0,255,255,0.1); border-radius:10px; padding:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <strong style="font-size:0.85rem; color:#fff;">Estado Base de Datos (Cloud SQL / PostgreSQL)</strong>
                            <span id="db-status-badge" style="font-size:0.65rem; color:#39FF14; border:1px solid #39FF14; padding:2px 6px; border-radius:4px; font-weight:bold;">Operativo</span>
                        </div>
                        <button class="btn-neon" onclick="testDBConnection()" style="font-size:0.75rem; padding:6px 12px; width:100%;"><i class="fa-solid fa-rotate"></i> Testear Conexión en Vivo</button>
                        <div id="db-test-result" style="margin-top:10px; font-size:0.75rem; color:#888; font-family:monospace; white-space:pre-wrap;"></div>
                    </div>

                    <!-- Stripe Mock Gateway -->
                    <div style="background:rgba(255,255,255,0.02); border:1px solid rgba(255,0,255,0.1); border-radius:10px; padding:12px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <strong style="font-size:0.85rem; color:#fff; display:flex; align-items:center; gap:5px;"><i class="fab fa-stripe" style="color:#635bff; font-size:1.4rem;"></i> Stripe Gateway</strong>
                            <span id="stripe-status-badge" style="font-size:0.65rem; color:#ff4d4d; border:1px solid #ff4d4d; padding:2px 6px; border-radius:4px; font-weight:bold;">No Instalada / Mock</span>
                        </div>
                        <p style="font-size:0.75rem; color:#aaa; margin-bottom:10px;">La pasarela Stripe no tiene credenciales en el archivo config.php. Activa la simulación de cobro VIP.</p>
                        
                        <div style="display:flex; gap:8px; margin-bottom:12px;">
                            <button class="btn-neon" onclick="generateMockStripeTokens()" style="flex:1; font-size:0.7rem; padding:5px;"><i class="fa-solid fa-key"></i> Generar API Keys</button>
                            <button class="btn-neon btn-neon-magenta" onclick="simulateStripeCheckout()" style="flex:1; font-size:0.7rem; padding:5px;"><i class="fa-solid fa-credit-card"></i> Cobrar Membresía VIP</button>
                        </div>
                        <div id="stripe-result" style="font-size:0.75rem; color:#888; font-family:monospace; background:rgba(0,0,0,0.3); border-radius:6px; padding:8px;">Estado: Esperando acción...</div>
                    </div>
                </div>

                <!-- PANEL: LEADS & AUDIENCE CALCULATOR -->
                <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-magenta); border-radius: 16px; padding: 20px; display:flex; flex-direction:column; gap:15px; box-shadow: 0 0 15px rgba(255,0,255,0.1);">
                    <h3 style="color:#FF00FF; margin:0; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-chart-line"></i> Leads & Analítica de Suscriptores</h3>
                    
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                        <div class="form-group">
                            <label style="font-size:0.75rem; color:#aaa;">Alcance Mensual (Promedio)</label>
                            <input type="number" id="calc-reach" class="form-input" value="150000" oninput="calculateLeads()" style="padding:6px; font-size:0.8rem;">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.75rem; color:#aaa;">Tasa Conversión (CTR %)</label>
                            <input type="number" id="calc-ctr" class="form-input" value="2.5" step="0.1" oninput="calculateLeads()" style="padding:6px; font-size:0.8rem;">
                        </div>
                    </div>

                    <div style="background:rgba(0,0,0,0.3); border-radius:10px; padding:12px; display:grid; grid-template-columns:1fr 1fr; gap:10px; text-align:center;">
                        <div>
                            <div style="color:#aaa; font-size:0.7rem; text-transform:uppercase;">Nuevos Leads Estimados</div>
                            <div id="lead-output-num" style="color:#00FFFF; font-size:1.8rem; font-weight:800; text-shadow:0 0 8px #00FFFF;">3,750</div>
                        </div>
                        <div>
                            <div style="color:#aaa; font-size:0.7rem; text-transform:uppercase;">Valor Mensual ($5 USD/VIP)</div>
                            <div id="lead-output-val" style="color:#39FF14; font-size:1.8rem; font-weight:800; text-shadow:0 0 8px #39FF14;">$18,750</div>
                        </div>
                    </div>

                    <button class="btn-neon" onclick="exportLeadsSimulator()" style="width:100%; font-size:0.75rem; padding:8px; margin-bottom: 5px;"><i class="fa-solid fa-download"></i> Exportar Leads Simulación a CSV</button>
                    <div id="leads-export-status" style="font-size:0.7rem; color:#888; text-align:center; margin-bottom: 10px;"></div>

                    <!-- INTEGRACIÓN DE YOUTUBE STUDIO CON RECOMENDACIONES IA -->
                    <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                        <span style="color:#aaa; font-weight:bold; font-size:0.75rem;"><i class="fab fa-youtube" style="color:#ff0000;"></i> YouTube Studio Insights & Sugerencias IA</span>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:8px;">
                            <button class="btn-neon" onclick="syncYouTubeStudioStats()" style="font-size:0.65rem; padding:5px; border-color:#ff0000; color:#ff0000;"><i class="fa-solid fa-rotate"></i> Sincronizar Métricas</button>
                            <button class="btn-neon btn-neon-magenta" id="btn-yt-suggest" onclick="generarPlanAccionesYT()" style="font-size:0.65rem; padding:5px; border-color:#00ffff; color:#00ffff;" disabled><i class="fa-solid fa-brain"></i> Crear Plan de Acción</button>
                        </div>
                        
                        <!-- Panel de estadísticas cargadas -->
                        <div id="yt-stats-panel" style="display:none; background:rgba(0,0,0,0.3); border-radius:10px; padding:10px; font-size:0.7rem; grid-template-columns: 1fr 1fr; gap:8px; border:1px solid rgba(255,255,255,0.03); margin-top:5px;">
                            <div>Vistas 24h: <strong style="color:#fff;" id="yt-stat-views">-</strong></div>
                            <div>CTR: <strong style="color:#ffb703;" id="yt-stat-ctr">-</strong></div>
                            <div>Retención: <strong style="color:#ff4d4d;" id="yt-stat-retention">-</strong></div>
                            <div>Impresiones: <strong style="color:#00ffff;" id="yt-stat-impressions">-</strong></div>
                        </div>
                        
                        <!-- Terminal de Acciones IA -->
                        <div id="yt-action-plan" style="display:none; font-size:0.7rem; color:#ccc; background:rgba(15,15,20,0.9); border:1px solid var(--neon-magenta); border-radius:8px; padding:10px; border-left:4px solid var(--neon-magenta); max-height:160px; overflow-y:auto; margin-top:5px; font-family:monospace;">
                            <!-- Acciones Gemini aparecerán aquí -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL: MAPA DE FLUJO DE TRABAJO INTERACTIVO -->
            <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-cyan); border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: 0 0 15px rgba(0,255,255,0.1);">
                <h3 style="color:#00FFFF; margin:0 0 15px 0;"><i class="fa-solid fa-circle-nodes"></i> Workflows de Información Internos</h3>
                
                <div style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                    <!-- Step 1 -->
                    <div onclick="showFlowInfo(1)" style="flex:1; min-width:130px; background:rgba(0,255,255,0.05); border:1px solid var(--neon-cyan); border-radius:10px; padding:10px; text-align:center; cursor:pointer;">
                        <strong style="color:var(--neon-cyan); font-size:0.75rem;">1. Captación</strong>
                        <p style="font-size:0.65rem; color:#aaa; margin:5px 0 0 0;">Chatbot & Landing Page</p>
                    </div>
                    <i class="fa-solid fa-angles-right" style="color:#555;"></i>
                    <!-- Step 2 -->
                    <div onclick="showFlowInfo(2)" style="flex:1; min-width:130px; background:rgba(255,0,255,0.05); border:1px solid var(--neon-magenta); border-radius:10px; padding:10px; text-align:center; cursor:pointer;">
                        <strong style="color:var(--neon-magenta); font-size:0.75rem;">2. Curation</strong>
                        <p style="font-size:0.65rem; color:#aaa; margin:5px 0 0 0;">Evaluación 3 Niveles</p>
                    </div>
                    <i class="fa-solid fa-angles-right" style="color:#555;"></i>
                    <!-- Step 3 -->
                    <div onclick="showFlowInfo(3)" style="flex:1; min-width:130px; background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px; text-align:center; cursor:pointer;">
                        <strong style="color:#fff; font-size:0.75rem;">3. Scripting</strong>
                        <p style="font-size:0.65rem; color:#aaa; margin:5px 0 0 0;">Escaletas IA Dify</p>
                    </div>
                    <i class="fa-solid fa-angles-right" style="color:#555;"></i>
                    <!-- Step 4 -->
                    <div onclick="showFlowInfo(4)" style="flex:1; min-width:130px; background:rgba(57,255,20,0.05); border:1px solid #39FF14; border-radius:10px; padding:10px; text-align:center; cursor:pointer;">
                        <strong style="color:#39FF14; font-size:0.75rem;">4. Postpro & Canva</strong>
                        <p style="font-size:0.65rem; color:#aaa; margin:5px 0 0 0;">Video Editor / PNGs</p>
                    </div>
                </div>

                <div id="flow-info-display" style="margin-top:15px; font-size:0.8rem; color:#ccc; background:rgba(0,0,0,0.3); border-radius:8px; padding:12px; border-left:4px solid var(--neon-cyan);">
                    Haz clic en cualquiera de las fases del flujo arriba para ver detalles de la auditoría y mapeo técnico.
                </div>
            </div>

            <!-- PANEL: CONEXIÓN Y AUTOMATIZACIÓN DE REDES SOCIALES (1-CLICK PUBLISH) -->
            <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-cyan); border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: 0 0 15px rgba(0,255,255,0.1);">
                <h3 style="color:#00FFFF; margin:0 0 5px 0; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-share-nodes"></i> Conexión & Automatización de Redes Sociales</h3>
                <p style="font-size:0.75rem; color:#aaa; margin-bottom:15px;">Enlaza los canales oficiales de La Cueva del Güero para publicar episodios, clips y audios en un solo clic.</p>
                
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap:12px; margin-bottom:15px;">
                    <!-- YouTube -->
                    <div style="background:rgba(255,255,255,0.01); border:1px solid rgba(255,0,0,0.15); border-radius:10px; padding:12px; text-align:center;">
                        <i class="fab fa-youtube" style="font-size:1.8rem; color:#ff0000; margin-bottom:8px; display:block;"></i>
                        <span style="font-weight:bold; font-size:0.75rem; color:#fff; display:block; margin-bottom:2px;">YouTube</span>
                        <span id="status-yt" style="font-size:0.6rem; color:#ff4d4d; display:block; margin-bottom:8px;"><i class="fa-solid fa-circle-dot"></i> Desconectado</span>
                        <button class="btn-neon" id="btn-connect-yt" onclick="conectarRedSocial('yt')" style="font-size:0.65rem; padding:4px 8px; width:100%; border-color:#ff0000; color:#ff0000;">Conectar</button>
                    </div>
                    <!-- Spotify -->
                    <div style="background:rgba(255,255,255,0.01); border:1px solid rgba(30,215,96,0.15); border-radius:10px; padding:12px; text-align:center;">
                        <i class="fab fa-spotify" style="font-size:1.8rem; color:#1ed760; margin-bottom:8px; display:block;"></i>
                        <span style="font-weight:bold; font-size:0.75rem; color:#fff; display:block; margin-bottom:2px;">Spotify</span>
                        <span id="status-sp" style="font-size:0.6rem; color:#ff4d4d; display:block; margin-bottom:8px;"><i class="fa-solid fa-circle-dot"></i> Desconectado</span>
                        <button class="btn-neon" id="btn-connect-sp" onclick="conectarRedSocial('sp')" style="font-size:0.65rem; padding:4px 8px; width:100%; border-color:#1ed760; color:#1ed760;">Conectar</button>
                    </div>
                    <!-- TikTok -->
                    <div style="background:rgba(255,255,255,0.01); border:1px solid rgba(0,242,234,0.15); border-radius:10px; padding:12px; text-align:center;">
                        <i class="fab fa-tiktok" style="font-size:1.8rem; color:#00f2ea; margin-bottom:8px; display:block;"></i>
                        <span style="font-weight:bold; font-size:0.75rem; color:#fff; display:block; margin-bottom:2px;">TikTok</span>
                        <span id="status-tk" style="font-size:0.6rem; color:#ff4d4d; display:block; margin-bottom:8px;"><i class="fa-solid fa-circle-dot"></i> Desconectado</span>
                        <button class="btn-neon" id="btn-connect-tk" onclick="conectarRedSocial('tk')" style="font-size:0.65rem; padding:4px 8px; width:100%; border-color:#00f2ea; color:#00f2ea;">Conectar</button>
                    </div>
                    <!-- Facebook -->
                    <div style="background:rgba(255,255,255,0.01); border:1px solid rgba(24,119,242,0.15); border-radius:10px; padding:12px; text-align:center;">
                        <i class="fab fa-facebook" style="font-size:1.8rem; color:#1877f2; margin-bottom:8px; display:block;"></i>
                        <span style="font-weight:bold; font-size:0.75rem; color:#fff; display:block; margin-bottom:2px;">Facebook</span>
                        <span id="status-fb" style="font-size:0.6rem; color:#ff4d4d; display:block; margin-bottom:8px;"><i class="fa-solid fa-circle-dot"></i> Desconectado</span>
                        <button class="btn-neon" id="btn-connect-fb" onclick="conectarRedSocial('fb')" style="font-size:0.65rem; padding:4px 8px; width:100%; border-color:#1877f2; color:#1877f2;">Conectar</button>
                    </div>
                    <!-- Instagram -->
                    <div style="background:rgba(255,255,255,0.01); border:1px solid rgba(225,48,108,0.15); border-radius:10px; padding:12px; text-align:center;">
                        <i class="fab fa-instagram" style="font-size:1.8rem; color:#e1306c; margin-bottom:8px; display:block;"></i>
                        <span style="font-weight:bold; font-size:0.75rem; color:#fff; display:block; margin-bottom:2px;">Instagram</span>
                        <span id="status-ig" style="font-size:0.6rem; color:#ff4d4d; display:block; margin-bottom:8px;"><i class="fa-solid fa-circle-dot"></i> Desconectado</span>
                        <button class="btn-neon" id="btn-connect-ig" onclick="conectarRedSocial('ig')" style="font-size:0.65rem; padding:4px 8px; width:100%; border-color:#e1306c; color:#e1306c;">Conectar</button>
                    </div>
                </div>

                <div style="background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.05); border-radius:12px; padding:15px; display:flex; flex-direction:column; gap:10px;">
                    <div style="font-weight:bold; font-size:0.8rem; color:#fff;"><i class="fa-solid fa-rocket"></i> Distribuidor Directo (1-Click Multi-Upload)</div>
                    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:12px; align-items:end;">
                        <div class="form-group" style="margin:0;">
                            <label style="font-size:0.7rem; color:#aaa;">Pista a Distribuir</label>
                            <select id="publish-video-select" class="form-input" style="padding:6px; font-size:0.75rem; background:#000;">
                                <option value="Capitulo_Multicam_JL.mp4">Capitulo_Multicam_JL.mp4 (YouTube / Facebook)</option>
                                <option value="Capitulo_Sin_Silencios.mp4">Capitulo_Sin_Silencios.mp4 (TikTok / Instagram Reels)</option>
                                <option value="Capitulo_Loudness_Normalizado.mp3">Capitulo_Loudness_Normalizado.mp3 (Spotify Audio Podcast)</option>
                            </select>
                        </div>
                        <button class="btn-neon btn-neon-magenta" onclick="publicarTodoRedes()" style="padding:8px 12px; font-size:0.75rem; font-weight:bold; width:100%;"><i class="fa-solid fa-rocket"></i> Publicar en Redes</button>
                    </div>
                    <div id="publish-console-log" style="font-size:0.7rem; color:#666; font-family:monospace; background:rgba(0,0,0,0.4); padding:8px; border-radius:6px; border:1px solid rgba(255,255,255,0.03);">Consola: Esperando automatización...</div>
                </div>
            </div>

            <!-- PANEL: BANDEJA DE REVISIÓN Y CURADURÍA MAESTRA (Human-in-the-loop) -->
            <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-magenta); border-radius: 16px; padding: 20px; margin-bottom: 25px; box-shadow: 0 0 15px rgba(255,0,255,0.1);">
                <h3 style="color:#FF00FF; margin:0 0 5px 0; display:flex; align-items:center; gap:8px;"><i class="fa-solid fa-stamp"></i> Filtro de Curaduría & Bandeja de Aprobación</h3>
                <p style="font-size:0.75rem; color:#aaa; margin-bottom:15px;">Todo el contenido generado (hooks, artículos de blog y archivos multimedia) debe ser revisado y aprobado manualmente aquí antes de ser subido a internet.</p>
                
                <div style="display:flex; flex-direction:column; gap:12px;" id="approval-queue-container">
                    <!-- Los elementos en revisión se renderizan aquí dinámicamente -->
                    <div style="text-align:center; padding:20px; color:#666; font-size:0.8rem; background:rgba(0,0,0,0.2); border-radius:10px; border:1px dashed rgba(255,255,255,0.05);" id="approval-empty-msg">
                        <i class="fa-solid fa-circle-check" style="color:#39FF14; font-size:1.4rem; margin-bottom:8px; display:block;"></i>
                        Bandeja vacía. Todo el contenido ha sido revisado o no hay elementos en cola.
                    </div>
                </div>
            </div>

            <!-- PANEL: KANBAN COOPERACIÓN DE SOCIOS -->
            <div style="background: rgba(15,15,15,0.7); border: 1px solid var(--neon-magenta); border-radius: 16px; padding: 20px; box-shadow: 0 0 15px rgba(255,0,255,0.1);">
                <h3 style="color:#FF00FF; margin:0 0 15px 0;"><i class="fa-solid fa-chalkboard-user"></i> Kanban de Trabajo en Equipo en Vivo (Socios)</h3>
                
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">
                    <input type="text" id="kanban-new-task" class="form-input" placeholder="Agregar nueva tarea (ej: Revisar guion Ep 19)..." style="padding:8px; font-size:0.8rem;">
                    <button class="btn-neon btn-neon-magenta" onclick="addKanbanTask()" style="font-size:0.75rem; padding:8px 12px;"><i class="fa-solid fa-plus"></i> Asignar Tarea</button>
                </div>

                <div id="kanban-list" style="display:flex; flex-direction:column; gap:8px;">
                    <!-- Lista de tareas dinamicas -->
                </div>
            </div>

            <!-- PANEL: VOTACIÓN DE DECISIONES DE EQUIPO Y PRODUCCIÓN -->
            <div style="background: rgba(15,15,15,0.7); border: 1px solid #FFD700; border-radius: 16px; padding: 20px; margin-top: 25px; box-shadow: 0 0 15px rgba(255,215,0,0.15);">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px;">
                    <div>
                        <h3 style="color:#FFD700; margin:0; display:flex; align-items:center; gap:8px;">
                            <i class="fa-solid fa-check-double"></i> Votación de Decisiones de Producción & Editorial
                        </h3>
                        <p style="font-size:0.75rem; color:#aaa; margin:4px 0 0 0;">Consenso en tiempo real para temas de episodios, cambios en escaletas y lanzamientos del show.</p>
                    </div>
                    <span style="font-size:0.7rem; color:#FFD700; background:rgba(255,215,0,0.1); border:1px solid rgba(255,215,0,0.3); padding:4px 10px; border-radius:12px;">
                        <i class="fa-solid fa-satellite-dish"></i> Sincronizado vía Webhook
                    </span>
                </div>

                <div style="display:grid; grid-template-columns: 2fr 1fr; gap:12px; margin-bottom:15px;">
                    <input type="text" id="decision-nueva-input" class="form-input" placeholder="Proponer nueva decisión (ej: Grabar especial en vivo en Mexicali)..." style="padding:8px; font-size:0.8rem;">
                    <div style="display:flex; gap:8px;">
                        <select id="decision-rol-select" class="form-input" style="padding:8px; font-size:0.8rem; flex:1; background:#111;">
                            <option value="El Güero">El Güero (Host)</option>
                            <option value="Productor">Productor</option>
                            <option value="Editor">Editor</option>
                            <option value="Comunidad">Comunidad</option>
                        </select>
                        <button class="btn-neon" onclick="proponerDecisionEquipo()" style="font-size:0.75rem; padding:8px 12px; border-color:#FFD700; color:#FFD700; white-space:nowrap;">
                            <i class="fa-solid fa-plus"></i> Proponer
                        </button>
                    </div>
                </div>

                <div id="decisiones-lista" style="display:flex; flex-direction:column; gap:10px;">
                    <!-- Se renderizan dinámicamente -->
                </div>
            </div>
        </section>
    </div>

    <!-- MODAL OCULTO: IMPORTAR AVATAR PRE-EXISTENTE -->
    <div id="modalImportarAvatarExistente" class="modal-translucent-overlay" style="display:none;">
        <div class="modal-translucent-card" style="max-width:520px;">
            <div class="modal-header-row">
                <button type="button" class="btn-section-back" onclick="cerrarModalImportarExistente()">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </button>
                <h3 class="modal-header-title" style="margin:0; font-size:1.15rem; color:#FF00FF;">
                    <i class="fa-solid fa-file-import"></i> Importar Avatar Existente
                </h3>
                <button class="btn-modal-close" onclick="cerrarModalImportarExistente()" title="Cerrar">&times;</button>
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
    <div id="modalSubirFotoGaleria" class="modal-translucent-overlay" style="display:none;">
        <div class="modal-translucent-card" style="max-width:520px;">
            <div class="modal-header-row">
                <button type="button" class="btn-section-back" onclick="document.getElementById('modalSubirFotoGaleria').style.display='none'">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </button>
                <h3 class="modal-header-title" style="margin:0; font-size:1.15rem; color:#00FFFF;">
                    <i class="fa-solid fa-camera"></i> Subir Foto a la Galería
                </h3>
                <button class="btn-modal-close" onclick="document.getElementById('modalSubirFotoGaleria').style.display='none'" title="Cerrar">&times;</button>
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

    <!-- ============================================================
         MODAL: VISUALIZACIÓN DEL CUESTIONARIO COMPLETO DEL INVITADO
         ============================================================ -->
    <div id="modalCuestionarioCompleto" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(4,4,10,0.88); backdrop-filter:blur(14px); z-index:99999; justify-content:center; align-items:center; padding:15px; box-sizing:border-box;">
        <div style="background:rgba(12,12,22,0.98); border:2px solid var(--neon-cyan); border-radius:20px; width:100%; max-width:1050px; height:92vh; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 0 50px rgba(0,255,255,0.3), 0 0 30px rgba(255,0,255,0.2); position:relative;">
            
            <!-- HEADER DEL MODAL -->
            <div style="padding:18px 24px; background:linear-gradient(180deg, rgba(20,20,38,0.95) 0%, rgba(12,12,22,0.95) 100%); border-bottom:1px solid rgba(0,255,255,0.25); display:flex; justify-content:space-between; align-items:flex-start; gap:16px;">
                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px; flex-wrap:wrap;">
                        <span style="background:rgba(0,255,255,0.12); border:1px solid var(--neon-cyan); color:var(--neon-cyan); font-size:0.75rem; font-weight:800; padding:2px 10px; border-radius:12px; text-transform:uppercase; letter-spacing:0.5px;">
                            <i class="fa-solid fa-clipboard-check"></i> Cuestionario Oficial Contestado
                        </span>
                        <span id="modal-cuest-token-badge" style="font-size:0.75rem; color:#888; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding:2px 8px; border-radius:10px;">Token: ---</span>
                        <span id="modal-cuest-fecha-badge" style="font-size:0.75rem; color:#aaa;"><i class="fa-regular fa-clock"></i> ---</span>
                    </div>
                    <h2 id="modal-cuest-nombre" style="margin:2px 0 6px 0; color:#fff; font-size:1.5rem; display:flex; align-items:center; gap:10px; flex-wrap:wrap; font-weight:800;">
                        <span>Invitado</span>
                        <span id="modal-cuest-alias" style="color:var(--neon-magenta); font-size:1.05rem; font-weight:700;">(Alias)</span>
                    </h2>
                    
                    <!-- METADATA DEL INVITADO -->
                    <div style="display:flex; gap:14px; align-items:center; font-size:0.82rem; color:#bbb; flex-wrap:wrap;">
                        <span id="modal-cuest-barrio"><i class="fa-solid fa-location-dot" style="color:var(--neon-cyan);"></i> Barrio: ---</span>
                        <span id="modal-cuest-ocupacion"><i class="fa-solid fa-briefcase" style="color:var(--neon-magenta);"></i> Jale: ---</span>
                        <span id="modal-cuest-contacto"><i class="fa-solid fa-envelope" style="color:var(--neon-green);"></i> Contacto: ---</span>
                    </div>
                </div>

                <!-- BADGE DE SCORE Y ACCIONES TOP -->
                <div style="display:flex; flex-direction:column; align-items:flex-end; gap:8px;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <button type="button" onclick="copiarCuestionarioTexto()" class="btn-neon" style="font-size:0.78rem; padding:6px 12px; border-radius:8px; border-color:var(--neon-cyan); color:var(--neon-cyan); background:rgba(0,255,255,0.08); cursor:pointer;" title="Copiar todas las preguntas y respuestas al portapapeles">
                            <i class="fa-solid fa-copy"></i> Copiar Texto
                        </button>
                        <button type="button" onclick="imprimirCuestionario()" class="btn-neon" style="font-size:0.78rem; padding:6px 12px; border-radius:8px; border-color:var(--neon-magenta); color:var(--neon-magenta); background:rgba(255,0,255,0.08); cursor:pointer;" title="Imprimir o exportar cuestionario en PDF">
                            <i class="fa-solid fa-print"></i> Imprimir
                        </button>
                        <button type="button" onclick="cerrarModalCuestionario()" style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.2); color:#fff; border-radius:50%; width:34px; height:34px; font-size:1.3rem; line-height:1; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;" onmouseover="this.style.background='#ff0055'; this.style.borderColor='#ff0055';" onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.borderColor='rgba(255,255,255,0.2)';">&times;</button>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <span id="modal-cuest-score-badge" style="font-weight:900; font-size:1.15rem; color:#39FF14; background:rgba(0,0,0,0.5); border:1px solid rgba(57,255,20,0.4); padding:3px 12px; border-radius:14px;">--- / 330 PTS</span>
                        <span id="modal-cuest-nivel-badge" style="font-weight:700; font-size:0.75rem; color:#39FF14; border:1px solid #39FF14; background:rgba(57,255,20,0.1); padding:4px 10px; border-radius:12px;">🟢 NIVEL ALTO</span>
                    </div>
                </div>
            </div>

            <!-- TOOLBAR DE BÚSQUEDA Y FILTRADO POR BLOQUES -->
            <div style="padding:12px 24px; background:rgba(16,16,28,0.9); border-bottom:1px solid rgba(255,255,255,0.06); display:flex; flex-direction:column; gap:10px;">
                <div style="display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                    <div style="position:relative; flex:1; min-width:260px;">
                        <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#888; font-size:0.85rem;"></i>
                        <input type="text" id="modal-cuest-search" oninput="filtrarPreguntasCuestionario()" placeholder="Buscar por palabra clave, pregunta o respuesta del invitado..." style="width:100%; box-sizing:border-box; background:rgba(0,0,0,0.5); border:1px solid rgba(0,255,255,0.3); border-radius:20px; padding:8px 14px 8px 34px; color:#fff; font-size:0.85rem; outline:none; transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--neon-cyan)';" onblur="this.style.borderColor='rgba(0,255,255,0.3)';">
                    </div>
                    <div style="font-size:0.8rem; color:#aaa; display:flex; align-items:center; gap:8px;">
                        <span>Mostrando: <strong id="modal-cuest-conteo" style="color:var(--neon-cyan); font-size:0.95rem;">33</strong> de 33 preguntas</span>
                    </div>
                </div>

                <!-- FILTRO POR BLOQUES TEMÁTICOS -->
                <div id="modal-cuest-filtros" style="display:flex; gap:6px; overflow-x:auto; padding-bottom:2px; -webkit-overflow-scrolling:touch;">
                    <button type="button" class="btn-bloque-filter active" onclick="setFiltroBloqueCuestionario('todos', this)" style="background:rgba(0,255,255,0.2); border:1px solid var(--neon-cyan); color:#fff; padding:4px 12px; border-radius:12px; font-size:0.75rem; font-weight:700; cursor:pointer; white-space:nowrap;">Todos (33)</button>
                    <button type="button" class="btn-bloque-filter" onclick="setFiltroBloqueCuestionario('ident', this)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#bbb; padding:4px 12px; border-radius:12px; font-size:0.75rem; cursor:pointer; white-space:nowrap;">Identificación (3)</button>
                    <button type="button" class="btn-bloque-filter" onclick="setFiltroBloqueCuestionario('raices', this)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#bbb; padding:4px 12px; border-radius:12px; font-size:0.75rem; cursor:pointer; white-space:nowrap;">Bloque 1: Raíces (7)</button>
                    <button type="button" class="btn-bloque-filter" onclick="setFiltroBloqueCuestionario('madrazos', this)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#bbb; padding:4px 12px; border-radius:12px; font-size:0.75rem; cursor:pointer; white-space:nowrap;">Bloque 2: Madrazos (7)</button>
                    <button type="button" class="btn-bloque-filter" onclick="setFiltroBloqueCuestionario('mentalidad', this)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#bbb; padding:4px 12px; border-radius:12px; font-size:0.75rem; cursor:pointer; white-space:nowrap;">Bloque 3: Mentalidad (5)</button>
                    <button type="button" class="btn-bloque-filter" onclick="setFiltroBloqueCuestionario('anecdotas', this)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#bbb; padding:4px 12px; border-radius:12px; font-size:0.75rem; cursor:pointer; white-space:nowrap;">Bloque 4: Anécdotas (9)</button>
                    <button type="button" class="btn-bloque-filter" onclick="setFiltroBloqueCuestionario('cierre', this)" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.15); color:#bbb; padding:4px 12px; border-radius:12px; font-size:0.75rem; cursor:pointer; white-space:nowrap;">Bloque 5: Cierre (2)</button>
                </div>
            </div>

            <!-- CONTENEDOR CON SCROLL DE LAS PREGUNTAS -->
            <div id="modal-cuest-body" style="flex:1; overflow-y:auto; padding:20px 24px; display:flex; flex-direction:column; gap:12px;">
                <!-- Se llena dinámicamente -->
            </div>

            <!-- FOOTER DEL MODAL -->
            <div style="padding:14px 24px; background:rgba(10,10,18,0.95); border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <button type="button" id="modal-cuest-btn-tracking" class="btn-neon" onclick="abrirTrackingDesdeModal()" style="font-size:0.8rem; padding:6px 14px; border-color:var(--neon-green); color:var(--neon-green); background:transparent; cursor:pointer; border-radius:8px;">
                        <i class="fa-solid fa-satellite-dish"></i> Ver Tracking en Vivo
                    </button>
                    <button type="button" class="btn-neon" onclick="copiarCuestionarioTexto()" style="font-size:0.8rem; padding:6px 14px; border-color:var(--neon-cyan); color:var(--neon-cyan); background:transparent; cursor:pointer; border-radius:8px;">
                        <i class="fa-solid fa-copy"></i> Copiar Todo
                    </button>
                </div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <button type="button" class="btn-neon" onclick="aprobarCuestionarioDesdeModal()" style="font-size:0.8rem; padding:6px 16px; border-color:#39FF14; color:#39FF14; background:rgba(57,255,20,0.12); cursor:pointer; border-radius:8px; font-weight:700;" title="Aprobar cuestionario completo y avanzar a fase de Escaleta">
                        <i class="fa-solid fa-circle-check"></i> Aprobar Cuestionario (Todo OK)
                    </button>
                    <button type="button" class="btn-neon" id="btn-enviar-correccion-invitado" onclick="enviarSolicitudCorreccionDesdeModal()" style="font-size:0.8rem; padding:6px 16px; border-color:#FF6600; color:#FF6600; background:rgba(255,102,0,0.12); cursor:pointer; border-radius:8px; font-weight:700;" title="Enviar solicitud de corrección al invitado con enlace y WhatsApp" disabled>
                        <i class="fa-solid fa-triangle-exclamation"></i> Solicitar Corrección (<span id="modal-cuest-marcadas-count">0</span>)
                    </button>
                    <button type="button" class="btn-neon" onclick="cerrarModalCuestionario()" style="font-size:0.8rem; padding:6px 18px; border-color:rgba(255,255,255,0.3); color:#fff; background:rgba(255,255,255,0.06); cursor:pointer; border-radius:8px;">
                        <i class="fa-solid fa-xmark"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DIÁLOGO: COMPARTIR ENLACE DE CORRECCIÓN CON INVITADO -->
    <div id="modalCompartirSolicitud" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); backdrop-filter:blur(8px); z-index:999999; justify-content:center; align-items:center; padding:15px; box-sizing:border-box;">
        <div style="background:#101020; border:2px solid #FF6600; border-radius:18px; max-width:550px; width:95%; padding:25px; box-shadow:0 0 35px rgba(255,102,0,0.35); text-align:left;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3 style="color:#FF6600; margin:0; font-size:1.15rem; display:flex; align-items:center; gap:8px;">
                    <i class="fa-solid fa-paper-plane"></i> Solicitud de Corrección Enviada
                </h3>
                <button onclick="document.getElementById('modalCompartirSolicitud').style.display='none'" style="background:none; border:none; color:#888; font-size:1.4rem; cursor:pointer;">&times;</button>
            </div>
            <p style="color:#ccc; font-size:0.85rem; margin-bottom:15px;">
                Las observaciones han quedado registradas en la base de datos y notificadas vía Webhook. Ahora puedes enviar el enlace directo al invitado para que entre con su mismo código y modifique únicamente las respuestas observadas:
            </p>
            <div style="background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1); border-radius:8px; padding:10px 14px; margin-bottom:15px;">
                <span style="font-size:0.75rem; color:#888; display:block; margin-bottom:4px;">Enlace Directo de Tracking / Corrección:</span>
                <input type="text" id="inputLinkCorreccion" readonly class="form-input" style="width:100%; font-size:0.85rem; padding:6px 10px; color:#00ffff; font-family:monospace; background:#080812;">
            </div>
            <div style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
                <button type="button" class="btn-neon" onclick="copiarLinkCorreccionDirecto()" style="border-color:#00ffff; color:#00ffff; font-size:0.8rem; padding:8px 14px;">
                    <i class="fa-solid fa-copy"></i> Copiar Enlace
                </button>
                <a id="btnWaCorreccionDirecto" href="#" target="_blank" class="btn-neon" style="border-color:#25D366; color:#25D366; font-size:0.8rem; padding:8px 14px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fa-brands fa-whatsapp"></i> Enviar por WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- CONTENEDOR FLOTANTE DE TOASTS PARA AVISOS EN VIVO -->
    <div id="toastAvisosContainer" style="position:fixed; top:20px; right:20px; z-index:999999; display:flex; flex-direction:column; gap:10px; max-width:380px; pointer-events:none;"></div>

    <!-- MODAL / DRAWER: CENTRO DE MONITOREO Y AVISOS EN VIVO (WEBHOOK) -->
    <div id="modalCentroAvisos" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(4,4,10,0.85); backdrop-filter:blur(10px); z-index:99999; justify-content:flex-end; align-items:stretch;">
        <div style="background:rgba(14,14,26,0.98); border-left:2px solid var(--neon-cyan); width:100%; max-width:460px; height:100vh; display:flex; flex-direction:column; box-shadow:-10px 0 35px rgba(0,0,0,0.8); position:relative;">
            
            <!-- Header -->
            <div style="padding:20px; border-bottom:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; align-items:center; gap:10px;">
                    <button type="button" class="btn-section-back" onclick="toggleCentroAvisos()" style="font-size:0.75rem; padding:4px 10px;" title="Volver al Hub">
                        <i class="fa-solid fa-arrow-left"></i> Volver
                    </button>
                    <div style="width:36px; height:36px; border-radius:50%; background:rgba(255,215,0,0.15); border:1px solid #FFD700; display:flex; align-items:center; justify-content:center; color:#FFD700;">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; color:#fff;">Avisos en Vivo</h3>
                        <span style="font-size:0.75rem; color:#aaa;"><i class="fa-solid fa-bolt" style="color:#39ff14;"></i> Monitoreo Webhook en Tiempo Real</span>
                    </div>
                </div>
                <button type="button" onclick="toggleCentroAvisos()" style="background:none; border:none; color:#fff; font-size:1.4rem; cursor:pointer;">&times;</button>
            </div>

            <!-- Webhook Config Toggle -->
            <div style="padding:12px 20px; background:rgba(0,0,0,0.4); border-bottom:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:0.78rem; color:#bbb;"><i class="fa-solid fa-plug" style="color:var(--neon-cyan);"></i> Webhook Externo (Discord / Slack / Make)</span>
                <button type="button" class="btn-neon" style="font-size:0.7rem; padding:3px 8px; border-color:var(--neon-cyan); color:var(--neon-cyan);" onclick="toggleConfigWebhook()"><i class="fa-solid fa-gear"></i> Configurar</button>
            </div>

            <!-- Formulario Config Webhook (Colapsable) -->
            <div id="boxConfigWebhook" style="display:none; padding:15px 20px; background:rgba(20,15,30,0.95); border-bottom:1px solid rgba(0,255,255,0.2);">
                <label style="display:block; font-size:0.78rem; color:var(--neon-cyan); margin-bottom:5px; font-weight:700;">URL del Webhook (Discord / Slack / Make / etc.):</label>
                <input type="url" id="inputWebhookUrl" class="form-input" placeholder="https://discord.com/api/webhooks/..." style="font-size:0.8rem; padding:8px; margin-bottom:10px; width:100%; box-sizing:border-box;">
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn-neon" style="font-size:0.72rem; padding:4px 10px; border-color:#FF00FF; color:#FF00FF;" onclick="probarWebhookTest()"><i class="fa-solid fa-paper-plane"></i> Probar Test</button>
                    <button type="button" class="btn-neon" style="font-size:0.72rem; padding:4px 12px; background:var(--neon-green); color:#000; border-color:var(--neon-green); font-weight:800;" onclick="guardarConfigWebhook()"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
                </div>
            </div>

            <!-- Feed de Avisos -->
            <div id="listaAvisosFeed" style="flex:1; overflow-y:auto; padding:15px 20px; display:flex; flex-direction:column; gap:10px;">
                <p style="color:#777; text-align:center; font-size:0.85rem; margin-top:20px;">Cargando avisos del sistema...</p>
            </div>

            <!-- Footer -->
            <div style="padding:12px 20px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center;">
                <button type="button" onclick="marcarAvisosLeidos()" style="background:none; border:none; color:#888; font-size:0.78rem; cursor:pointer;"><i class="fa-solid fa-check-double"></i> Marcar leídos</button>
                <button type="button" onclick="cargarAvisosEnVivo(true)" style="background:none; border:none; color:var(--neon-cyan); font-size:0.78rem; cursor:pointer;"><i class="fa-solid fa-rotate"></i> Actualizar</button>
            </div>
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

        // ============================================================
        // MESA DE TRABAJO - CONTROLADORES INTERACTIVOS DE AUDITORÍA
        // ============================================================

        // 1. Test de Conexión Base de Datos
        async function testDBConnection() {
            const resultDiv = document.getElementById("db-test-result");
            const badge = document.getElementById("db-status-badge");
            resultDiv.textContent = "> Conectando a PostgreSQL / Cloud SQL en vivo...";
            
            try {
                const response = await fetch("../api/api-db-test.php");
                const data = await response.json();
                
                if (data.success) {
                    badge.textContent = "Conectado";
                    badge.style.color = "#39FF14";
                    badge.style.borderColor = "#39FF14";
                    
                    let html = `Conexión: Éxito (Driver: ${data.driver})\nLatencia: ${data.latency_ms} ms\n\nTablas y Registros:\n`;
                    for (const table in data.details) {
                        html += `- ${table}: ${data.details[table].rows} filas [${data.details[table].status}]\n`;
                    }
                    resultDiv.textContent = html;
                } else {
                    throw new Error(data.error || "Error desconocido");
                }
            } catch(err) {
                badge.textContent = "Error";
                badge.style.color = "#ff4d4d";
                badge.style.borderColor = "#ff4d4d";
                resultDiv.textContent = `Error de conexión:\n${err.message}`;
            }
        }

        // 2. Simulador de Pasarela de Pagos Stripe
        let mockStripeKeys = null;
        function generateMockStripeTokens() {
            const resultDiv = document.getElementById("stripe-result");
            const badge = document.getElementById("stripe-status-badge");
            
            mockStripeKeys = {
                publishable_key: "pk_test_cueva_" + Math.random().toString(36).substring(2, 15),
                secret_key: "sk_test_cueva_" + Math.random().toString(36).substring(2, 15)
            };
            
            badge.textContent = "Simulación OK";
            badge.style.color = "#39FF14";
            badge.style.borderColor = "#39FF14";
            
            resultDiv.innerHTML = `Claves Generadas:\nPublicable: <span style="color:#00FFFF;">${mockStripeKeys.publishable_key}</span>\nSecret: <span style="color:#FF00FF;">${mockStripeKeys.secret_key}</span>`;
        }

        function simulateStripeCheckout() {
            const resultDiv = document.getElementById("stripe-result");
            if (!mockStripeKeys) {
                alert("Primero genera las API Keys de simulación.");
                return;
            }
            
            const numTarjeta = prompt("Simulador Stripe Checkout:\n\nIngresa número de tarjeta (4242 4242 4242):", "4242424242424242");
            if (!numTarjeta) return;
            
            resultDiv.textContent = "> Conectando con Stripe Gateway api.stripe.com...";
            
            setTimeout(() => {
                const chargeId = "ch_" + Math.random().toString(36).substring(2, 10);
                resultDiv.innerHTML = `Estado: <span style="color:#39FF14;">PAGO EXITOSO</span>\nCargo ID: ${chargeId}\nMonto: $5.00 USD\nPlan: Membresía VIP Cueva\nFecha: ${new Date().toLocaleString()}`;
                alert("¡Cobro Stripe Procesado Exitosamente (Simulación)!");
            }, 1500);
        }

        // 3. Calculadora de Leads y Conversión
        function calculateLeads() {
            const reach = parseInt(document.getElementById("calc-reach").value) || 0;
            const ctr = parseFloat(document.getElementById("calc-ctr").value) || 0;
            
            const leads = Math.round(reach * (ctr / 100));
            const val = leads * 5;
            
            document.getElementById("lead-output-num").textContent = leads.toLocaleString();
            document.getElementById("lead-output-val").textContent = "$" + val.toLocaleString();
        }

        function exportLeadsSimulator() {
            const reach = parseInt(document.getElementById("calc-reach").value) || 0;
            const ctr = parseFloat(document.getElementById("calc-ctr").value) || 0;
            const leadsCount = Math.round(reach * (ctr / 100));
            
            let csv = "ID,Nombre,Email,Origen,Fecha Sincronizacion\n";
            for (let i = 1; i <= Math.min(leadsCount, 50); i++) {
                csv += `${i},Lead_Simulado_${i},lead_${i}@cuevadelguero.com,Mesa Trabajo,${new Date().toISOString().split('T')[0]}\n`;
            }
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", `Leads_Simulados_Cueva.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            document.getElementById("leads-export-status").textContent = `Exportadas las primeras 50 de ${leadsCount} filas de Leads a CSV.`;
        }

        // 4. Mapa de flujo descriptivo
        function showFlowInfo(fase) {
            const display = document.getElementById("flow-info-display");
            
            switch(fase) {
                case 1:
                    display.innerHTML = `<strong>1. Fase de Captación (Lead Generator):</strong><br>Los leads ingresan mediante el widget Paw Agent. La conversación se registra de forma síncrona en la tabla <code>conversations</code> de PostgreSQL. Si el usuario ingresa un contacto, se genera un webhook interno.`;
                    display.style.borderLeftColor = "var(--neon-cyan)";
                    break;
                case 2:
                    display.innerHTML = `<strong>2. Fase de Curation (Evaluación de 3 Niveles):</strong><br>El sistema lee los datos de invitados estrella. Clasifica automáticamente según storytelling: Nivel Alto (Publicación completa en YouTube/Spotify), Nivel Medio (Contenido exclusivo VIP) y Nivel Bajo (Reevaluar perfil).`;
                    display.style.borderLeftColor = "var(--neon-magenta)";
                    break;
                case 3:
                    display.innerHTML = `<strong>3. Scripting Inteligente:</strong><br>Utiliza la API de Dify Workflow enlazada con la llave corporativa para generar guiones estructurados, cue cards para el set de grabación y escaletas técnicas guardadas en base de datos.`;
                    display.style.borderLeftColor = "#fff";
                    break;
                case 4:
                    display.innerHTML = `<strong>4. Postproducción y Canva:</strong><br>Se unifican los activos generados en el editor Canva PRO y en la línea de tiempo de video utilizando presets de formato rápido (YouTube, Instagram, TikTok) y limpiando el material usando comandos directos de FFmpeg e IA.`;
                    display.style.borderLeftColor = "#39FF14";
                    break;
            }
        }

        // 5. Kanban de Trabajo en Equipo en Vivo (Socios)
        let kanbanTasks = JSON.parse(localStorage.getItem("cueva_kanban_tasks")) || [
            { id: 1, text: "Alinear niveles de audio del Episodio 18 a -14 LUFS", done: true },
            { id: 2, text: "Cargar foto de portada para el post del blog de invitados", done: false },
            { id: 3, text: "Configurar API Real de Stripe para membresías VIP", done: false }
        ];

        function renderKanban() {
            const container = document.getElementById("kanban-list");
            if (!container) return;
            container.innerHTML = "";
            
            kanbanTasks.forEach(task => {
                const div = document.createElement("div");
                div.style.display = "flex";
                div.style.justifyContent = "space-between";
                div.style.alignItems = "center";
                div.style.background = "rgba(255,255,255,0.02)";
                div.style.padding = "8px 12px";
                div.style.borderRadius = "8px";
                div.style.border = task.done ? "1px solid rgba(57,255,20,0.2)" : "1px solid rgba(255,255,255,0.05)";
                div.style.margin = "3px 0";
                
                div.innerHTML = `
                    <div style="display:flex; align-items:center; gap:10px;">
                        <input type="checkbox" ${task.done ? 'checked' : ''} onchange="toggleKanbanTask(${task.id})" style="cursor:pointer;">
                        <span style="font-size:0.8rem; text-decoration:${task.done ? 'line-through' : 'none'}; color:${task.done ? '#888' : '#ccc'};">${task.text}</span>
                    </div>
                    <button onclick="deleteKanbanTask(${task.id})" style="background:none; border:none; color:#ff4d4d; cursor:pointer; font-size:0.85rem;"><i class="fa-solid fa-trash"></i></button>
                `;
                container.appendChild(div);
            });
            
            localStorage.setItem("cueva_kanban_tasks", JSON.stringify(kanbanTasks));
        }

        function addKanbanTask() {
            const input = document.getElementById("kanban-new-task");
            const text = input.value.trim();
            if (!text) return;
            
            kanbanTasks.push({
                id: Date.now(),
                text: text,
                done: false
            });
            input.value = "";
            renderKanban();

            // Disparar Webhook en tiempo real
            if (window.dispararEventoWebhook) {
                window.dispararEventoWebhook('task_kanban', {
                    accion: 'Nueva Tarea Agregada',
                    tarea: text,
                    estado: 'Pendiente'
                });
            }
        }

        function toggleKanbanTask(id) {
            const tareaAntes = kanbanTasks.find(t => t.id === id);
            const eraDone = tareaAntes ? tareaAntes.done : false;

            kanbanTasks = kanbanTasks.map(t => t.id === id ? { ...t, done: !t.done } : t);
            renderKanban();

            const tareaDespues = kanbanTasks.find(t => t.id === id);
            if (tareaDespues && window.dispararEventoWebhook) {
                window.dispararEventoWebhook('task_kanban', {
                    accion: tareaDespues.done ? 'Tarea Cumplida / Completada' : 'Tarea Reactivada',
                    tarea: tareaDespues.text,
                    estado: tareaDespues.done ? 'Completada 100%' : 'En progreso'
                });
            }
        }

        function deleteKanbanTask(id) {
            kanbanTasks = kanbanTasks.filter(t => t.id !== id);
            renderKanban();
        }

        // 6. Votación de Decisiones de Equipo y Producción (Sincronizado vía Webhook)
        let decisionesEquipo = JSON.parse(localStorage.getItem("cueva_decisiones_equipo")) || [
            { id: 1, titulo: "Lanzar episodio especial con La Pocha y El Gallo en set en vivo", autor: "El Güero", favor: 3, contra: 0, estado: "Aprobada", fecha: "Hoy" },
            { id: 2, titulo: "Migrar clips de TikTok a formato de pantalla dividida con gameplay", autor: "Editor", favor: 1, contra: 2, estado: "En debate", fecha: "Ayer" },
            { id: 3, titulo: "Publicar audio completo en Spotify antes del estreno en YouTube", autor: "Productor", favor: 2, contra: 1, estado: "En debate", fecha: "Esta semana" }
        ];

        function renderDecisiones() {
            const container = document.getElementById("decisiones-lista");
            if (!container) return;
            container.innerHTML = "";

            decisionesEquipo.forEach(d => {
                const totalVotos = (d.favor || 0) + (d.contra || 0);
                const pctFavor = totalVotos > 0 ? Math.round((d.favor / totalVotos) * 100) : 50;
                const statusColor = d.favor > d.contra ? '#39FF14' : (d.contra > d.favor ? '#FF4D4D' : '#FFD700');

                const div = document.createElement("div");
                div.style.cssText = "background:rgba(255,255,255,0.02); border:1px solid rgba(255,215,0,0.15); border-radius:10px; padding:12px; display:flex; flex-direction:column; gap:8px;";
                div.innerHTML = `
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px;">
                        <div>
                            <span style="font-size:0.68rem; color:#FFD700; font-weight:700; text-transform:uppercase;">Propuesto por ${d.autor || 'Equipo'}</span>
                            <h4 style="margin:2px 0 0 0; font-size:0.85rem; color:#fff;">${d.titulo}</h4>
                        </div>
                        <div style="display:flex; align-items:center; gap:8px;">
                            <span style="font-size:0.68rem; color:${statusColor}; border:1px solid ${statusColor}; padding:2px 6px; border-radius:8px; font-weight:bold;">
                                ${d.favor > d.contra ? 'Mayoría A Favor' : (d.contra > d.favor ? 'Mayoría En Contra' : 'Empate')}
                            </span>
                            <button onclick="borrarDecision(${d.id})" style="background:none; border:none; color:#666; cursor:pointer; font-size:0.8rem;"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                    
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="flex:1; background:rgba(255,255,255,0.08); height:6px; border-radius:3px; overflow:hidden; display:flex;">
                            <div style="background:#39FF14; width:${pctFavor}%; height:100%;"></div>
                            <div style="background:#FF4D4D; width:${100 - pctFavor}%; height:100%;"></div>
                        </div>
                        <span style="font-size:0.7rem; color:#aaa; font-family:monospace;">${d.favor} 👍 / ${d.contra} 👎</span>
                    </div>

                    <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:2px;">
                        <button class="btn-neon" onclick="votarDecision(${d.id}, 'favor')" style="font-size:0.68rem; padding:3px 10px; border-color:#39FF14; color:#39FF14;">
                            <i class="fa-solid fa-thumbs-up"></i> Votar A Favor
                        </button>
                        <button class="btn-neon" onclick="votarDecision(${d.id}, 'contra')" style="font-size:0.68rem; padding:3px 10px; border-color:#FF4D4D; color:#FF4D4D;">
                            <i class="fa-solid fa-thumbs-down"></i> Votar En Contra
                        </button>
                    </div>
                `;
                container.appendChild(div);
            });

            localStorage.setItem("cueva_decisiones_equipo", JSON.stringify(decisionesEquipo));
        }

        function proponerDecisionEquipo() {
            const input = document.getElementById("decision-nueva-input");
            const rolSelect = document.getElementById("decision-rol-select");
            const texto = input ? input.value.trim() : "";
            const rol = rolSelect ? rolSelect.value : "Equipo";

            if (!texto) {
                alert("Por favor escribe la decisión o propuesta que someterás a votación.");
                return;
            }

            const nueva = {
                id: Date.now(),
                titulo: texto,
                autor: rol,
                favor: 1,
                contra: 0,
                estado: "En debate",
                fecha: "Hoy"
            };

            decisionesEquipo.unshift(nueva);
            if (input) input.value = "";
            renderDecisiones();

            if (window.dispararEventoWebhook) {
                window.dispararEventoWebhook('decision_votada', {
                    accion: 'Nueva Decisión Propuesta',
                    titulo: texto,
                    proponente: rol,
                    voto_inicial: 'A favor',
                    votos_favor: 1,
                    votos_contra: 0
                });
            }
        }

        function votarDecision(id, tipo) {
            const rolSelect = document.getElementById("decision-rol-select");
            const votante = rolSelect ? rolSelect.value : "Socio / Equipo";

            const dec = decisionesEquipo.find(d => d.id === id);
            if (!dec) return;

            if (tipo === 'favor') {
                dec.favor = (dec.favor || 0) + 1;
            } else {
                dec.contra = (dec.contra || 0) + 1;
            }

            renderDecisiones();

            if (window.dispararEventoWebhook) {
                window.dispararEventoWebhook('decision_votada', {
                    accion: 'Voto Registrado',
                    decision: dec.titulo,
                    votante: votante,
                    voto: tipo === 'favor' ? 'A favor (👍)' : 'En contra (👎)',
                    total_favor: dec.favor,
                    total_contra: dec.contra
                });
            }
        }

        function borrarDecision(id) {
            decisionesEquipo = decisionesEquipo.filter(d => d.id !== id);
            renderDecisiones();
        }

        // Render inicializar Kanban y Decisiones al entrar
        document.addEventListener("DOMContentLoaded", () => {
            renderKanban();
            renderDecisiones();
            calculateLeads();
        });
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
    <script src="/js/dashboard-pro.js?v=<?= time() ?>"></script>
    <script src="/js/editor-canva.js?v=<?= time() ?>"></script>
    <script src="/js/avatar-engine.js?v=<?= time() ?>"></script>
    <script src="https://unpkg.com/wavesurfer.js@7/dist/wavesurfer.min.js"></script>
    <script src="/js/ffmpeg-wasm-helper.js?v=<?= time() ?>"></script>
    <script src="/js/video-editor.js?v=<?= time() ?>"></script>
</body>
</html>
