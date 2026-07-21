<?php
/**
 * Login Administrador - La Cueva del Güero
 * Endpoint: /dashboard/login.php
 */

session_start();
require_once __DIR__ . '/../config/config.php';

$error = '';

// Si ya inició sesión, redirigir al dashboard
if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
        header("Location: index.php");
        exit();
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador - La Cueva del Güero</title>
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
            height: 100vh;
            margin: 0;
            overflow: hidden;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 0, 255, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(0, 255, 255, 0.1) 0px, transparent 50%);
        }
        .login-container {
            background: rgba(15, 15, 15, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 0, 255, 0.4);
            box-shadow: 0 0 30px rgba(255, 0, 255, 0.2);
            padding: 40px;
            border-radius: 16px;
            width: 100%;
            max-width: 360px;
            text-align: center;
        }
        h2 {
            color: #FF00FF;
            text-shadow: 0 0 10px #FF00FF;
            margin-bottom: 25px;
            font-size: 1.8rem;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 22px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #00FFFF;
            text-shadow: 0 0 5px #00FFFF;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        input {
            width: 100%;
            padding: 12px;
            background: rgba(20, 20, 20, 0.8);
            border: 1px solid rgba(0, 255, 255, 0.4);
            color: #fff;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 1rem;
            transition: all 0.2s ease-in-out;
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
        .btn-login:active {
            transform: translateY(1px);
        }
        .error-msg {
            color: #ff4d4d;
            background: rgba(255, 77, 77, 0.1);
            border: 1px solid rgba(255, 77, 77, 0.3);
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .footer-link {
            display: block;
            margin-top: 25px;
            color: #888;
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.2s;
        }
        .footer-link:hover {
            color: #00FFFF;
            text-shadow: 0 0 5px #00FFFF;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔒 LA CUEVA ADMIN</h2>
        <?php if ($error): ?>
            <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        <a href="../index.html" class="footer-link">← Volver al sitio principal</a>
    </div>
</body>
</html>
