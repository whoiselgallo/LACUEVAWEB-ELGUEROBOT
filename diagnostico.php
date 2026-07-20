<?php
/**
 * ═════════════════════════════════════════════════════════════════════════════════
 * DIAGNÓSTICO - La Cueva del Güero
 * Verifica que todas las configuraciones estén correctas
 * Acceso: /diagnostico.php
 * ═════════════════════════════════════════════════════════════════════════════════
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico - La Cueva del Güero</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Courier New', monospace;
            background: #0a0a0a;
            color: #00ff00;
            padding: 20px;
            line-height: 1.6;
        }
        .container { max-width: 900px; margin: 0 auto; }
        h1 { 
            color: #ff00ff;
            text-shadow: 0 0 10px #ff00ff;
            margin-bottom: 30px;
            text-align: center;
        }
        .section {
            background: #111;
            border: 2px solid #00ffff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.3);
        }
        .section h2 {
            color: #00ffff;
            margin-bottom: 15px;
            font-size: 1.2em;
        }
        .check { 
            padding: 10px;
            margin-bottom: 10px;
            border-left: 4px solid;
            border-radius: 4px;
        }
        .check.success {
            background: rgba(0, 255, 0, 0.1);
            border-left-color: #00ff00;
            color: #00ff00;
        }
        .check.error {
            background: rgba(255, 0, 0, 0.1);
            border-left-color: #ff0000;
            color: #ff0000;
        }
        .check.warning {
            background: rgba(255, 255, 0, 0.1);
            border-left-color: #ffff00;
            color: #ffff00;
        }
        .status {
            font-weight: bold;
            margin-right: 10px;
        }
        code {
            background: #000;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: rgba(255, 0, 255, 0.1);
            color: #ff00ff;
        }
        table tr:hover {
            background: rgba(0, 255, 255, 0.05);
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #333;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐾 DIAGNÓSTICO - La Cueva del Güero</h1>

        <!-- CONFIGURACIÓN -->
        <div class="section">
            <h2>📋 Configuración</h2>
            <?php
                $checks = [];

                // 1. Archivo config
                if (file_exists(__DIR__ . '/config/config.php')) {
                    $checks[] = ['success', '✓ Archivo config.php existe'];
                    require_once __DIR__ . '/config/config.php';
                    
                    // 2. Constantes Dify
                    if (defined('DIFY_API_KEY')) {
                        $api_key_masked = substr(DIFY_API_KEY, 0, 10) . '***' . substr(DIFY_API_KEY, -5);
                        $checks[] = ['success', "✓ DIFY_API_KEY definida: <code>$api_key_masked</code>"];
                    } else {
                        $checks[] = ['error', '✗ DIFY_API_KEY NO definida'];
                    }

                    if (defined('DIFY_URL')) {
                        $checks[] = ['success', "✓ DIFY_URL: <code>" . DIFY_URL . "</code>"];
                    }

                    // 3. Constantes BD
                    if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
                        $checks[] = ['success', "✓ Configuración BD: <code>" . DB_USER . "@" . DB_HOST . "/" . DB_NAME . "</code>"];
                    }
                } else {
                    $checks[] = ['error', '✗ Archivo config.php NO EXISTE'];
                }

                foreach ($checks as [$type, $msg]) {
                    echo "<div class='check $type'><span class='status'></span>$msg</div>";
                }
            ?>
        </div>

        <!-- BASE DE DATOS -->
        <div class="section">
            <h2>🗄️ Base de Datos</h2>
            <?php
                $db_checks = [];
                try {
                    if (!function_exists('db_connect')) {
                        throw new Exception('Función db_connect no existe');
                    }
                    
                    $db = db_connect();
                    $db_checks[] = ['success', '✓ Conexión a BD exitosa'];

                    // Verificar tablas
                    $tables = $db->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'")->fetchAll(PDO::FETCH_COLUMN);
                    
                    if (in_array('conversations', $tables)) {
                        $count = $db->query("SELECT COUNT(*) FROM conversations")->fetchColumn();
                        $db_checks[] = ['success', "✓ Tabla 'conversations' existe ($count registros)"];
                    } else {
                        $db_checks[] = ['warning', '⚠ Tabla conversations no existe (crear con SQL)'];
                    }

                    if (in_array('knowledge_base', $tables)) {
                        $count = $db->query("SELECT COUNT(*) FROM knowledge_base")->fetchColumn();
                        $db_checks[] = ['success', "✓ Tabla 'knowledge_base' existe ($count registros)"];
                    } else {
                        $db_checks[] = ['warning', '⚠ Tabla knowledge_base no existe'];
                    }

                    // Listar todas las tablas
                    $db_checks[] = ['success', "✓ Tablas en BD: " . implode(', ', $tables)];

                } catch (Exception $e) {
                    $db_checks[] = ['error', "✗ Error de conexión: " . $e->getMessage()];
                }

                foreach ($db_checks as [$type, $msg]) {
                    echo "<div class='check $type'><span class='status'></span>$msg</div>";
                }
            ?>
        </div>

        <!-- APIS -->
        <div class="section">
            <h2>🔌 APIs</h2>
            <?php
                $api_checks = [];

                // Verificar archivos de API
                $apis = [
                    '/api/api-el-guero-bot.php',
                    '/api/api-escaleta.php',
                    '/api/api-cuecards.php'
                ];

                foreach ($apis as $api) {
                    if (file_exists(__DIR__ . $api)) {
                        $api_checks[] = ['success', "✓ Archivo <code>$api</code> existe"];
                    } else {
                        $api_checks[] = ['warning', "⚠ Archivo <code>$api</code> NO existe"];
                    }
                }

                foreach ($api_checks as [$type, $msg]) {
                    echo "<div class='check $type'><span class='status'></span>$msg</div>";
                }
            ?>
        </div>

        <!-- FRONTEND -->
        <div class="section">
            <h2>🎨 Frontend (PAW Agent)</h2>
            <?php
                $frontend_checks = [];

                $files = [
                    '/paw-agent/paw-core.js',
                    '/paw-agent/paw-agent.js',
                    '/paw-agent/paw-api.js',
                    '/paw-agent/paw-chat.js',
                    '/css/paw-agent.css'
                ];

                foreach ($files as $file) {
                    if (file_exists(__DIR__ . $file)) {
                        $size = filesize(__DIR__ . $file);
                        $frontend_checks[] = ['success', "✓ <code>$file</code> (" . round($size/1024, 2) . " KB)"];
                    } else {
                        $frontend_checks[] = ['error', "✗ <code>$file</code> NO existe"];
                    }
                }

                foreach ($frontend_checks as [$type, $msg]) {
                    echo "<div class='check $type'><span class='status'></span>$msg</div>";
                }
            ?>
        </div>

        <!-- SEGURIDAD -->
        <div class="section">
            <h2>🔐 Seguridad</h2>
            <?php
                $security_checks = [];

                if (file_exists(__DIR__ . '/.htaccess')) {
                    $security_checks[] = ['success', '✓ Archivo .htaccess existe'];
                } else {
                    $security_checks[] = ['warning', '⚠ No hay .htaccess (recomendado en Apache)'];
                }

                if (file_exists(__DIR__ . '/.gitignore')) {
                    $security_checks[] = ['success', '✓ Archivo .gitignore existe'];
                } else {
                    $security_checks[] = ['warning', '⚠ No hay .gitignore'];
                }

                if (file_exists(__DIR__ . '/config/config.php')) {
                    if (is_readable(__DIR__ . '/config/config.php')) {
                        $security_checks[] = ['warning', '⚠ config.php es legible desde web (verificar permisos)'];
                    }
                }

                foreach ($security_checks as [$type, $msg]) {
                    echo "<div class='check $type'><span class='status'></span>$msg</div>";
                }
            ?>
        </div>

        <!-- INFORMACIÓN DEL SERVIDOR -->
        <div class="section">
            <h2>🖥️ Servidor</h2>
            <table>
                <tr>
                    <th>Parámetro</th>
                    <th>Valor</th>
                </tr>
                <tr>
                    <td>PHP Version</td>
                    <td><code><?php echo phpversion(); ?></code></td>
                </tr>
                <tr>
                    <td>Sistema Operativo</td>
                    <td><code><?php echo php_uname(); ?></code></td>
                </tr>
                <tr>
                    <td>Servidor Web</td>
                    <td><code><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></code></td>
                </tr>
                <tr>
                    <td>URL Raíz</td>
                    <td><code><?php echo $scheme = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']; ?></code></td>
                </tr>
                <tr>
                    <td>Directorio Base</td>
                    <td><code><?php echo __DIR__; ?></code></td>
                </tr>
            </table>
        </div>

        <!-- EXTENSIONES PHP -->
        <div class="section">
            <h2>📦 Extensiones PHP</h2>
            <?php
                $required_extensions = ['pdo', 'pdo_mysql', 'curl', 'json'];
                $installed = get_loaded_extensions();

                foreach ($required_extensions as $ext) {
                    if (in_array($ext, $installed)) {
                        echo "<div class='check success'><span class='status'>✓</span> Extensión <code>$ext</code> instalada</div>";
                    } else {
                        echo "<div class='check error'><span class='status'>✗</span> Extensión <code>$ext</code> NO INSTALADA</div>";
                    }
                }
            ?>
        </div>

        <div class="footer">
            <p>🐾 La Cueva del Güero - Diagnóstico v1.0</p>
            <p style="margin-top: 10px; color: #999; font-size: 0.9em;">
                Fecha: <?php echo date('Y-m-d H:i:s'); ?> | 
                IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
            </p>
        </div>
    </div>
</body>
</html>
