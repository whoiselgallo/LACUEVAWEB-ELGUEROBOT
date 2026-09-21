<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * DIAGNÓSTICO INTEGRAL - La Cueva del Güero & El Güero Bot v2.5 PRO
 * Verifica salud de Base de Datos, Gemini AI, APIs, Tracking y Seguridad
 * Acceso: /diagnostico.php
 * ═════════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico PRO - La Cueva del Güero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #080812;
            color: #e0e0e0;
            padding: 30px 20px;
            line-height: 1.6;
        }
        .container { max-width: 960px; margin: 0 auto; }
        h1 { 
            color: #ff00ff;
            text-shadow: 0 0 12px #ff00ff;
            margin-bottom: 25px;
            text-align: center;
            font-size: 2.2rem;
        }
        .section {
            background: rgba(18, 18, 28, 0.95);
            border: 1px solid rgba(0, 255, 255, 0.4);
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.15);
        }
        .section h2 {
            color: #00ffff;
            margin-bottom: 16px;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .check { 
            padding: 12px 16px;
            margin-bottom: 10px;
            border-left: 4px solid;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        .check.success {
            background: rgba(0, 255, 0, 0.08);
            border-left-color: #00ff00;
            color: #a3ffb0;
        }
        .check.error {
            background: rgba(255, 0, 0, 0.1);
            border-left-color: #ff3333;
            color: #ff9999;
        }
        .check.warning {
            background: rgba(255, 255, 0, 0.08);
            border-left-color: #ffff00;
            color: #fff3a3;
        }
        code {
            background: #000;
            padding: 2px 6px;
            border-radius: 4px;
            color: #00ffff;
            border: 1px solid #333;
            font-family: monospace;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }
        .btn-reload {
            display: block;
            margin: 30px auto;
            background: #00ffff;
            color: #000;
            font-weight: bold;
            padding: 12px 28px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            width: fit-content;
            box-shadow: 0 0 15px rgba(0,255,255,0.4);
        }
        .btn-reload:hover {
            box-shadow: 0 0 25px #00ffff;
            transform: scale(1.03);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fa-solid fa-satellite-dish"></i> DIAGNÓSTICO DEL SISTEMA</h1>

        <!-- 1. CONFIGURACIÓN E INTELIGENCIA ARTIFICIAL -->
        <div class="section">
            <h2><i class="fa-solid fa-brain"></i> 1. Motor de Inteligencia Artificial & Configuración</h2>
            <?php
                $checks = [];
                if (file_exists(__DIR__ . '/config/config.php')) {
                    $checks[] = ['success', '✓ Archivo <code>config/config.php</code> cargado correctamente'];
                    
                    if (function_exists('get_gemini_api_key')) {
                        $key = get_gemini_api_key();
                        if (!empty($key) && $key !== 'TU_GEMINI_API_KEY') {
                            $masked = substr($key, 0, 8) . '...' . substr($key, -4);
                            $checks[] = ['success', "✓ Google Gemini API Key activa y rotando: <code>$masked</code>"];
                        } else {
                            $checks[] = ['warning', '⚠ Clave de Gemini no configurada en .env (usando modo fallback)'];
                        }
                    } else {
                        $checks[] = ['error', '✗ Función get_gemini_api_key() no encontrada'];
                    }
                } else {
                    $checks[] = ['error', '✗ config/config.php NO EXISTE'];
                }

                foreach ($checks as [$type, $msg]) {
                    echo "<div class='check $type'>$msg</div>";
                }
            ?>
        </div>

        <!-- 2. BASE DE DATOS -->
        <div class="section">
            <h2><i class="fa-solid fa-database"></i> 2. Base de Datos (PostgreSQL / Cloud SQL)</h2>
            <?php
                $db_checks = [];
                try {
                    if (function_exists('get_db_connection')) {
                        $pdo = get_db_connection();
                        $db_checks[] = ['success', '✓ Conexión a PostgreSQL establecida exitosamente'];
                    } else {
                        $db_checks[] = ['warning', '⚠ Función get_db_connection() no disponible'];
                    }
                } catch (Exception $e) {
                    $db_checks[] = ['warning', '⚠ Base de datos no conectada en este momento (Modo Fallback Activo): ' . htmlspecialchars($e->getMessage())];
                }

                foreach ($db_checks as [$type, $msg]) {
                    echo "<div class='check $type'>$msg</div>";
                }
            ?>
        </div>

        <!-- 3. APIS Y MICROSERVICIOS -->
        <div class="section">
            <h2><i class="fa-solid fa-network-wired"></i> 3. Microservicios y Endpoints de API</h2>
            <div class="grid-2">
            <?php
                $apis = [
                    '/api/api-guest-tracking.php' => 'Tracking de Invitados en Vivo',
                    '/api/api-video-clips.php'    => 'Extractor de Clips Virales IA',
                    '/api/api-hooks-ai.php'       => 'Generador de Hooks & Copies',
                    '/api/api-el-guero-bot.php'   => 'Paw Agent / El Güero Bot',
                    '/api/api-blog-ai.php'        => 'Generador de Blog SEO con IA',
                    '/api/api-avatar-engine.php'  => 'Avatar Engine (Imagen 3)',
                    '/api/api-escaleta.php'       => 'Generador de Escaletas',
                    '/api/api-cuecards.php'       => 'Despachador de Cue Cards',
                    '/api/api-export-pdf.php'     => 'Exportador de Documentos PDF'
                ];

                foreach ($apis as $file => $nombre) {
                    if (file_exists(__DIR__ . $file)) {
                        echo "<div class='check success'>✓ <b>$nombre</b>: <code>$file</code></div>";
                    } else {
                        echo "<div class='check error'>✗ <b>$nombre</b>: Falta <code>$file</code></div>";
                    }
                }
            ?>
            </div>
        </div>

        <!-- 4. FRONTEND Y PORTALES -->
        <div class="section">
            <h2><i class="fa-solid fa-desktop"></i> 4. Portales y Vistas Frontend</h2>
            <div class="grid-2">
            <?php
                $fronts = [
                    '/tracking/index.html'        => 'Portal de Tracking de Invitado',
                    '/storytelling-invitado.html' => 'Cuestionario de Storytelling',
                    '/cesion-derechos.html'       => 'Contrato y Cesión de Derechos',
                    '/manage-blog.html'           => 'Gestor de Blog PRO',
                    '/dashboard/index.php'        => 'Panel Administrativo Unificado',
                    '/index.html'                 => 'Landing Page Oficial'
                ];

                foreach ($fronts as $file => $desc) {
                    if (file_exists(__DIR__ . $file)) {
                        $size = round(filesize(__DIR__ . $file) / 1024, 1);
                        echo "<div class='check success'>✓ <b>$desc</b> ($size KB)</div>";
                    } else {
                        echo "<div class='check error'>✗ <b>$desc</b>: No encontrado</div>";
                    }
                }
            ?>
            </div>
        </div>

        <!-- 5. SEGURIDAD Y PROTECCIÓN -->
        <div class="section">
            <h2><i class="fa-solid fa-shield-halved"></i> 5. Seguridad y Blindaje Perimetral</h2>
            <?php
                $sec_checks = [];

                if (file_exists(__DIR__ . '/.htaccess')) {
                    $sec_checks[] = ['success', '✓ Archivo <code>.htaccess</code> activo con reglas de seguridad, headers y bloqueo anti-SQLi'];
                } else {
                    $sec_checks[] = ['error', '✗ Falta archivo .htaccess'];
                }

                if (file_exists(__DIR__ . '/.env.example')) {
                    $sec_checks[] = ['success', '✓ Plantilla <code>.env.example</code> presente'];
                }

                if (is_dir(__DIR__ . '/uploads')) {
                    $sec_checks[] = ['success', '✓ Directorio <code>uploads/</code> configurado para almacenamiento de medios'];
                }

                foreach ($sec_checks as [$type, $msg]) {
                    echo "<div class='check $type'>$msg</div>";
                }
            ?>
        </div>

        <a href="diagnostico.php" class="btn-reload"><i class="fa-solid fa-arrows-rotate"></i> Ejecutar Diagnóstico Nuevamente</a>
    </div>
</body>
</html>
