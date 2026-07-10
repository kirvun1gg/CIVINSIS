<?php
// auth-handler.php — Puente para auth.js (recibe FormData, procesa login/registro)
require_once 'php/config.php';
iniciarSesion();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'login':    handleLogin();    break;
    case 'register': handleRegister(); break;
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        exit;
}

function handleLogin(): void {
    $email    = sanitizar($_POST['email']    ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'errors' => ['Por favor completa todos los campos']]);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'errors' => ['Formato de correo inválido']]);
        exit;
    }

    $conn = getDBConnection();
    // JOIN con roles para obtener el nombre del rol
    $stmt = $conn->prepare("
        SELECT u.id, u.nombre, u.apellido, u.email, u.password,
               r.nombre AS rol, u.activo
        FROM   usuarios u
        JOIN   roles r ON r.id = u.rol_id
        WHERE  u.email = ?
        LIMIT  1
    ");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !$user['activo']) {
        echo json_encode(['success' => false, 'errors' => ['Credenciales incorrectas']]);
        exit;
    }
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'errors' => ['Credenciales incorrectas']]);
        exit;
    }

    $upd = $conn->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = ?");
    $upd->bind_param('i', $user['id']); $upd->execute(); $upd->close();

    $_SESSION['usuario_id']     = $user['id'];
    $_SESSION['usuario_nombre'] = $user['nombre'];
    $_SESSION['usuario_email']  = $user['email'];
    $_SESSION['usuario_rol']    = $user['rol'];
    session_regenerate_id(true);

    echo json_encode(['success' => true, 'message' => 'Bienvenido, ' . $user['nombre'], 'redirect' => 'dashboard.php']);
    exit;
}

function handleRegister(): void {
    $nombre   = sanitizar($_POST['nombre']   ?? '');
    $apellido = sanitizar($_POST['usuario']  ?? $nombre);
    $email    = sanitizar($_POST['email']    ?? '');
    $password = $_POST['password']           ?? '';
    $confirm  = $_POST['confirm_password']   ?? '';

    if (empty($nombre) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'errors' => ['Por favor completa todos los campos']]);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'errors' => ['Formato de correo inválido']]);
        exit;
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'errors' => ['La contraseña debe tener al menos 8 caracteres']]);
        exit;
    }
    if ($password !== $confirm) {
        echo json_encode(['success' => false, 'errors' => ['Las contraseñas no coinciden']]);
        exit;
    }

    $conn  = getDBConnection();
    $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $check->bind_param('s', $email); $check->execute(); $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        echo json_encode(['success' => false, 'errors' => ['Este correo ya está registrado']]);
        exit;
    }
    $check->close();

    // Obtener id del rol 'usuario'
    $rolRow = $conn->query("SELECT id FROM roles WHERE nombre = 'usuario' LIMIT 1")->fetch_assoc();
    $rolId  = $rolRow ? (int)$rolRow['id'] : 3;

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, password, rol_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('ssssi', $nombre, $apellido, $email, $hash, $rolId);
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'errors' => ['Error al crear la cuenta']]);
        exit;
    }
    $newId = $stmt->insert_id; $stmt->close();

    $_SESSION['usuario_id']     = $newId;
    $_SESSION['usuario_nombre'] = $nombre;
    $_SESSION['usuario_email']  = $email;
    $_SESSION['usuario_rol']    = 'usuario';
    session_regenerate_id(true);

    echo json_encode(['success' => true, 'message' => '¡Cuenta creada!', 'redirect' => 'dashboard.php']);
    exit;
}
?>
