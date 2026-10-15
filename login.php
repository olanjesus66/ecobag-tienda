<?php
require_once 'includes/conexion.php';
require_once 'includes/funciones.php';

// A dónde ir después de entrar (solo archivos .php de este proyecto)
$volver = $_POST['volver'] ?? $_GET['volver'] ?? '';
if (!preg_match('/^[a-z0-9_\-]+\.php$/i', $volver)) {
    $volver = 'index.php';
}

// Si ya tiene sesión, no tiene caso mostrar el login
if (isset($_SESSION['usuario_id'])) {
    header("Location: $volver");
    exit;
}

$errores = [];
$identificador = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $identificador = trim($_POST['identificador'] ?? '');
    $pass          = $_POST['password'] ?? '';

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errores[] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';

    } elseif (time() < ($_SESSION['bloqueo'] ?? 0)) {
        $minutos = (int)ceil(($_SESSION['bloqueo'] - time()) / 60);
        $errores[] = "Demasiados intentos. Espera $minutos minuto(s) e inténtalo de nuevo.";

    } elseif ($identificador === '' || $pass === '') {
        $errores[] = 'Escribe tu correo o usuario y tu contraseña.';

    } else {
        // Buscar por correo O por usuario
        $stmt = $pdo->prepare("SELECT id, nombre, password FROM usuarios WHERE correo = ? OR usuario = ? LIMIT 1");
        $stmt->execute([strtolower($identificador), $identificador]);
        $u = $stmt->fetch();

        // Si no existe, verificamos contra un hash falso para tardar lo mismo
        $hash = $u['password'] ?? password_hash('relleno', PASSWORD_DEFAULT);

        if ($u && password_verify($pass, $hash)) {

            unset($_SESSION['intentos'], $_SESSION['bloqueo']);
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = (int)$u['id'];
            $_SESSION['usuario_nombre'] = $u['nombre'];

            // Si PHP mejora su algoritmo en el futuro, actualizamos el hash
            if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
                $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?")
                    ->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
            }

            if (!empty($_POST['recordar'])) {
                crearRecordatorio($pdo, (int)$u['id']);
            }

            header("Location: $volver");
            exit;
        }

        // Falló: contamos el intento
        $_SESSION['intentos'] = ($_SESSION['intentos'] ?? 0) + 1;
        if ($_SESSION['intentos'] >= 5) {
            $_SESSION['bloqueo']  = time() + 300;     // 5 minutos
            $_SESSION['intentos'] = 0;
            $errores[] = 'Demasiados intentos. Espera 5 minutos e inténtalo de nuevo.';
        } else {
            $errores[] = 'Correo, usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Iniciar sesión | EcoBag</title>
  <link rel="stylesheet" href="css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<main class="auth">

  <div class="auth-img auth-img-login">
    <a href="index.php" class="auth-logo"><i class="fa-solid fa-leaf"></i> EcoBag</a>
    <div>
      <h2>Bienvenido de nuevo</h2>
      <p>Inicia sesión para continuar con tus compras y disfrutar de todas nuestras novedades.</p>
    </div>
  </div>

  <div class="auth-form">
    <div class="auth-caja">
      <a href="index.php" class="logo logo-movil"><i class="fa-solid fa-leaf"></i> EcoBag</a>
      <h1>Iniciar sesión</h1>
      <p class="auth-sub">Ingresa tus datos para acceder a tu cuenta.</p>

      <?php if ($errores): ?>
        <div class="errores">
          <ul>
            <?php foreach ($errores as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" id="form-login">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">
        <input type="hidden" name="volver" value="<?= htmlspecialchars($volver) ?>">

        <div class="campo">
          <i class="fa-regular fa-user"></i>
          <input type="text" name="identificador" placeholder="Correo electrónico o usuario"
                 value="<?= htmlspecialchars($identificador) ?>"
                 required maxlength="120" autocomplete="username">
        </div>

        <div class="campo">
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" id="password" placeholder="Contraseña"
                 required autocomplete="current-password">
          <button type="button" class="ver-pass" data-target="password" aria-label="Mostrar contraseña">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>

        <div class="fila-opciones">
          <label class="opcion-check">
            <input type="checkbox" name="recordar" value="1"> Recordarme
          </label>
          <a href="#">¿Olvidaste tu contraseña?</a>
        </div>

        <button type="submit" class="btn btn-bloque">Iniciar sesión</button>
      </form>

      <p class="auth-pie">¿No tienes una cuenta? <a href="registro.php">Regístrate</a></p>
    </div>
  </div>

</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>