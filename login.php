<?php
session_start();

if (!empty($_SESSION['user_id'])) {
  header('Location: index.php');
  exit;
}

if (empty($_SESSION['login_csrf'])) {
  $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

$error = '';
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login = trim((string) ($_POST['usuario'] ?? ''));
  $password = (string) ($_POST['contrasena'] ?? '');
  $csrf = (string) ($_POST['csrf'] ?? '');

  if (!hash_equals($_SESSION['login_csrf'], $csrf)) {
    $error = 'Recarga la página e intenta de nuevo.';
  } elseif ($login === '' || $password === '') {
    $error = 'Escribe tu usuario y contraseña.';
  } else {
    require __DIR__ . '/php/conexion.php';
    $consulta = $conexion->prepare('SELECT id_usuario, nombre, correo, `contraseña` AS clave FROM usuarios WHERE correo = ? OR nombre = ? LIMIT 1');
    $consulta->bind_param('ss', $login, $login);
    $consulta->execute();
    $usuario = $consulta->get_result()->fetch_assoc();
    $claveGuardada = (string) ($usuario['clave'] ?? '');
    $esHash = password_get_info($claveGuardada)['algo'] !== null;
    $valida = $usuario && ($esHash
      ? password_verify($password, $claveGuardada)
      : hash_equals($claveGuardada, $password));

    if ($valida) {
      if (!$esHash) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare('UPDATE usuarios SET `contraseña` = ? WHERE id_usuario = ?');
        $idUsuario = (int) $usuario['id_usuario'];
        $actualizar->bind_param('si', $hash, $idUsuario);
        $actualizar->execute();
      }
      session_regenerate_id(true);
      $_SESSION['user_id'] = (int) $usuario['id_usuario'];
      $_SESSION['user_name'] = (string) $usuario['nombre'];
      $_SESSION['user_email'] = (string) $usuario['correo'];
      unset($_SESSION['login_csrf']);
      header('Location: index.php');
      exit;
    }

    $error = 'Correo o contraseña incorrectos.';
  }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | GANGA Shoes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{min-height:100vh;background:#f5f6fa;display:grid;place-items:center;font-family:system-ui,sans-serif}
    .login-card{width:min(100% - 32px,400px);border:0;border-radius:16px;box-shadow:0 12px 40px #17233b14}
    .brand{color:#5b55e7;font-weight:800;letter-spacing:.04em}
    .form-control{min-height:46px}
    .btn-primary{background:#5b55e7;border-color:#5b55e7;min-height:46px;font-weight:600}
    .btn-primary:hover{background:#4841d1;border-color:#4841d1}
  </style>
</head>
<body>
  <main class="card login-card">
    <div class="card-body p-4 p-sm-5">
      <div class="brand mb-2">GANGA SHOES</div>
      <h1 class="h3 mb-1">Iniciar sesión</h1>
      <p class="text-secondary mb-4">Ingresa tus datos para abrir el inventario.</p>

      <?php if ($error !== ''): ?>
        <div class="alert alert-danger py-2" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <form method="post" action="login.php">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['login_csrf'], ENT_QUOTES, 'UTF-8') ?>">
        <div class="mb-3">
          <label class="form-label" for="usuario">Usuario o correo electrónico</label>
          <input class="form-control" type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($login, ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label" for="contrasena">Contraseña</label>
          <input class="form-control" type="password" id="contrasena" name="contrasena" autocomplete="current-password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Entrar</button>
      </form>
    </div>
  </main>
</body>
</html>
