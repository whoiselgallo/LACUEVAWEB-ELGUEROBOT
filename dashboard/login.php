<?php
/**
 * Login Administrador & Onboarding Corporativo - La Cueva del Güero
 * Endpoint: /dashboard/login.php
 */

session_start();
require_once __DIR__ . '/../config/config.php';

$error = '';
$show_onboarding = false;
$user_input = '';

// Si ya inició sesión, redirigir al dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: index.php");
    exit();
}

$db = db_connect();

// Auto-crear tabla de usuarios si no existe aún (Self-healing)
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            email VARCHAR(255) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            nombre VARCHAR(100),
            role VARCHAR(50) DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_login TIMESTAMP
        )
    ");
} catch (Exception $e) {
    error_log("Users table auto-create error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'login';

    if ($action === 'login') {
        $user = trim(sanitize_input($_POST['username'] ?? ''));
        $pass = trim($_POST['password'] ?? '');
        $user_input = $user;

        // 1. LLAVE MAESTRA -> REGISTRO OBLIGATORIO DE CORREO CORPORATIVO
        if (($user === ADMIN_USER || strtolower($user) === 'admin') && $pass === ADMIN_PASS) {
            $show_onboarding = true;
        } else {
            // 2. VALIDAR EN BASE DE DATOS NEON POSTGRESQL
            try {
                $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
                $stmt->execute([$user]);
                $userData = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($userData && password_verify($pass, $userData['password_hash'])) {
                    $_SESSION['admin_logged'] = true;
                    $_SESSION['admin_user']   = $userData['email'];
                    $_SESSION['admin_name']   = $userData['nombre'] ?? $userData['email'];

                    // Actualizar último ingreso
                    $up = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $up->execute([$userData['id']]);

                    header("Location: index.php");
                    exit();
                } else {
                    $error = 'Credenciales inválidas. Verifica tu correo corporativo y contraseña.';
                }
            } catch (Exception $e) {
                $error = 'Error en el sistema de autenticación: ' . $e->getMessage();
            }
        }
    }

    if ($action === 'register') {
        $email = trim(sanitize_input($_POST['email'] ?? ''));
        $password = trim($_POST['new_password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');
        $nombre = trim(sanitize_input($_POST['nombre'] ?? ''));

        if (empty($email) || empty($password) || empty($confirm)) {
            $error = 'Todos los campos son obligatorios.';
            $show_onboarding = true;
        } elseif ($password !== $confirm) {
            $error = 'Las contraseñas no coinciden. Por favor coloca ambas contraseñas iguales.';
            $show_onboarding = true;
        } elseif (!preg_match('/@(lacuevadelguero\.com|tsolutionsipidd\.com)$/i', $email)) {
            $error = 'Dominio no autorizado. Solo se permiten correos corporativos @lacuevadelguero.com y @tsolutionsipidd.com';
            $show_onboarding = true;
        } elseif (strlen($password) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
            $show_onboarding = true;
        } else {
            try {
                // Verificar si ya existe
                $check = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
                $check->execute([$email]);

                if ($check->fetch()) {
                    $error = 'Este correo corporativo ya está registrado. Ingresa directamente con tu contraseña personal.';
                    $show_onboarding = false;
                } else {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $ins = $db->prepare("INSERT INTO users (email, password_hash, nombre, role, last_login) VALUES (?, ?, ?, 'admin', NOW())");
                    $ins->execute([$email, $hash, $nombre ?: $email]);

                    $_SESSION['admin_logged'] = true;
                    $_SESSION['admin_user']   = $email;
                    $_SESSION['admin_name']   = $nombre ?: $email;

                    header("Location: index.php");
                    exit();
                }
            } catch (Exception $e) {
                $error = 'Error registrando cuenta: ' . $e->getMessage();
                $show_onboarding = true;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrador - La Cueva del Güero</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        body {
            background: #080808;
            color: #fff;
            font-family: 'Outfit', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 0, 255, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 255, 255, 0.15) 0px, transparent 50%);
        }
        .login-container {
            background: rgba(15, 15, 15, 0.85);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 0, 255, 0.4);
            box-shadow: 0 0 35px rgba(255, 0, 255, 0.25);
            padding: 40px;
            border-radius: 20px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h2 {
            color: #FF00FF;
            text-shadow: 0 0 12px #FF00FF;
            margin-bottom: 25px;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #00FFFF;
            text-shadow: 0 0 5px #00FFFF;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input {
            width: 100%;
            padding: 12px;
            background: rgba(20, 20, 20, 0.9);
            border: 1px solid rgba(0, 255, 255, 0.4);
            color: #fff;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        input:focus {
            outline: none;
            border-color: #00FFFF;
            box-shadow: 0 0 12px rgba(0, 255, 255, 0.4);
            background: #000;
        }
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #FF00FF;
            border: none;
            color: #fff;
            font-weight: bold;
            font-size: 1rem;
            border-radius: 8px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.2s ease;
            margin-top: 10px;
        }
        .btn-login:hover {
            box-shadow: 0 0 20px #FF00FF;
            background: #e600e6;
            transform: translateY(-1px);
        }
        .error-msg {
            color: #ff4d4d;
            background: rgba(255, 77, 77, 0.1);
            border: 1px solid rgba(255, 77, 77, 0.3);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            line-height: 1.4;
        }
        .badge-domain {
            display: inline-block;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid #00FFFF;
            color: #00FFFF;
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 12px;
            margin: 2px;
        }
        .footer-link {
            display: block;
            margin-top: 25px;
            color: #888;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .footer-link:hover {
            color: #00FFFF;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🐾 LA CUEVA ADMIN</h2>

        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!$show_onboarding): ?>
            <!-- FORMULARIO 1: LOGIN HABITUAL O LLAVE MAESTRA -->
            <form method="POST" action="login.php">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="username">Usuario o Correo Corporativo</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_input); ?>" placeholder="usuario@lacuevadelguero.com" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="btn-login">Ingresar al Dashboard</button>
            </form>
            <div style="margin-top: 15px;">
                <span style="font-size: 0.75rem; color: #aaa;">Dominios autorizados:</span><br>
                <span class="badge-domain">@lacuevadelguero.com</span>
                <span class="badge-domain">@tsolutionsipidd.com</span>
            </div>
        <?php else: ?>
            <!-- FORMULARIO 2: REGISTRO / ONBOARDING CORPORATIVO DE PRIMER INGRESO -->
            <div style="background: rgba(0,255,255,0.05); border: 1px solid #00FFFF; padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: left;">
                <h3 style="margin:0 0 5px; color:#00FFFF; font-size:1.05rem;">🔑 ¡Llave Maestra Aceptada!</h3>
                <p style="margin:0; font-size:0.85rem; color:#ccc;">Registra tu correo corporativo y crea tu contraseña personal para acceder al Dashboard PRO.</p>
            </div>

            <form method="POST" action="login.php">
                <input type="hidden" name="action" value="register">
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: Marcos Pérez" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Corporativo</label>
                    <input type="email" id="email" name="email" placeholder="tu-nombre@lacuevadelguero.com" required>
                    <small style="font-size: 0.75rem; color: #aaa; display: block; margin-top: 4px;">Debes usar <strong>@lacuevadelguero.com</strong> o <strong>@tsolutionsipidd.com</strong></small>
                </div>
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña Personal</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Mínimo 6 caracteres" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite tu contraseña" required minlength="6">
                </div>
                <button type="submit" class="btn-login" style="background:#00FFFF; color:#000;">Registrar Cuenta e Ingresar</button>
            </form>
        <?php endif; ?>

        <a href="../index.html" class="footer-link">← Volver al sitio principal</a>
    </div>
</body>
</html>
