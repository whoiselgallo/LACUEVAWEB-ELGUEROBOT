<?php
/**
 * 🔒 LA CUEVA DEL GÜERO - API DE AUTENTICACIÓN CORPORATIVA & ONBOARDING
 * Endpoint: /api/api-auth.php
 * Métodos: POST
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = sanitize_input($input['action'] ?? 'login');

try {
    $db = db_connect();

    // -----------------------------------------------------------------------------
    // ACCIÓN: CHECK SESSION
    // -----------------------------------------------------------------------------
    if ($action === 'check') {
        if (!empty($_SESSION['admin_user'])) {
            json_response([
                'logged_in' => true,
                'user'      => $_SESSION['admin_user'],
                'name'      => $_SESSION['admin_name'] ?? 'Administrador'
            ], 200);
        } else {
            json_response(['logged_in' => false], 200);
        }
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: LOGOUT
    // -----------------------------------------------------------------------------
    if ($action === 'logout') {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        json_response(['success' => true, 'message' => 'Sesión cerrada correctamente'], 200);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: LOGIN
    // -----------------------------------------------------------------------------
    if ($action === 'login') {
        $userOrEmail = trim(sanitize_input($input['user'] ?? $input['email'] ?? ''));
        $password = trim($input['password'] ?? '');

        if (empty($userOrEmail) || empty($password)) {
            json_response(['error' => 'Por favor ingresa usuario/correo y contraseña'], 400);
            exit();
        }

        // 1. VALIDAR SI INGRESA CON LA LLAVE MAESTRA DE SETUP
        if (($userOrEmail === ADMIN_USER || $userOrEmail === 'admin') && $password === ADMIN_PASS) {
            json_response([
                'success'            => true,
                'require_onboarding' => true,
                'message'            => 'Llave maestra aceptada. Registra tu correo corporativo para continuar.'
            ], 200);
            exit();
        }

        // 2. BUSCAR USUARIO CORPORATIVO REGISTRADO EN BD
        $stmt = $db->prepare("SELECT * FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$userOrEmail]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && password_verify($password, $userData['password_hash'])) {
            $_SESSION['admin_user'] = $userData['email'];
            $_SESSION['admin_name'] = $userData['nombre'] ?? $userData['email'];

            // Actualizar último ingreso
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$userData['id']]);

            json_response([
                'success'            => true,
                'require_onboarding' => false,
                'user'               => $userData['email'],
                'name'               => $userData['nombre'] ?? $userData['email']
            ], 200);
            exit();
        }

        json_response(['error' => 'Credenciales inválidas. Verifica tu correo corporativo y contraseña.'], 401);
        exit();
    }

    // -----------------------------------------------------------------------------
    // ACCIÓN: REGISTER (ONBOARDING)
    // -----------------------------------------------------------------------------
    if ($action === 'register') {
        $email = trim(sanitize_input($input['email'] ?? ''));
        $password = trim($input['password'] ?? '');
        $nombre = trim(sanitize_input($input['nombre'] ?? ''));

        if (empty($email) || empty($password)) {
            json_response(['error' => 'Correo corporativo y contraseña son obligatorios'], 400);
            exit();
        }

        // VALIDAR DOMINIO CORPORATIVO OBLIGATORIO
        if (!preg_match('/@(lacuevadelguero\.com|tsolutionsipidd\.com)$/i', $email)) {
            json_response([
                'error' => 'Dominio no autorizado. Solo se permiten correos corporativos @lacuevadelguero.com y @tsolutionsipidd.com'
            ], 400);
            exit();
        }

        if (strlen($password) < 6) {
            json_response(['error' => 'La contraseña debe tener al menos 6 caracteres'], 400);
            exit();
        }

        // VERIFICAR SI YA EXISTE EL CORREO
        $checkStmt = $db->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?)");
        $checkStmt->execute([$email]);
        if ($checkStmt->fetch()) {
            json_response(['error' => 'Este correo ya se encuentra registrado. Ingresa directamente con tu clave personal.'], 400);
            exit();
        }

        // REGISTRAR NUEVO USUARIO EN NEON POSTGRESQL
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $insertStmt = $db->prepare("
            INSERT INTO users (email, password_hash, nombre, role, last_login)
            VALUES (?, ?, ?, 'admin', NOW())
        ");
        $insertStmt->execute([$email, $hash, $nombre ?: $email]);

        // ESTABLECER SESIÓN PHP
        $_SESSION['admin_user'] = $email;
        $_SESSION['admin_name'] = $nombre ?: $email;

        json_response([
            'success' => true,
            'message' => 'Cuenta corporativa registrada correctamente',
            'user'    => $email
        ], 200);
        exit();
    }

    json_response(['error' => 'Acción no válida'], 400);

} catch (PDOException $e) {
    error_log('Auth DB Error: ' . $e->getMessage());
    json_response(['error' => 'Error de base de datos en autenticación: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    error_log('Auth Error: ' . $e->getMessage());
    json_response(['error' => 'Error de servidor: ' . $e->getMessage()], 500);
}
