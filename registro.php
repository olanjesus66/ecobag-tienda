<?php
require_once 'includes/conexion.php';

// Si ya inició sesión, no tiene sentido registrarse otra vez
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

// Token anti-CSRF: evita que otro sitio envíe el formulario por el usuario
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errores = [];
$nombre = $correo = $usuario = '';   // para conservar lo escrito si hay error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errores[] = 'La sesión expiró. Recarga la página e inténtalo de nuevo.';
    }

    $nombre  = trim($_POST['nombre'] ?? '');
    $correo  = strtolower(trim($_POST['correo'] ?? ''));
    $usuario = trim($_POST['usuario'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $pass2   = $_POST['password2'] ?? '';

    // ---------- Validaciones ----------
    if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
        $errores[] = 'El nombre debe tener entre 3 y 100 caracteres.';
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 120) {
        $errores[] = 'Escribe un correo electrónico válido.';
    }
    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $usuario)) {
        $errores[] = 'El usuario debe tener de 4 a 20 caracteres: letras, números o guion bajo.';
    }
    if (strlen($pass) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    } elseif (strlen($pass) > 72) {
        $errores[] = 'La contraseña no puede tener más de 72 caracteres.';
    } elseif ($pass !== $pass2) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    // ---------- ¿Ya existe el correo o el usuario? ----------
    if (!$errores) {
        $stmt = $pdo->prepare("SELECT correo, usuario FROM usuarios WHERE correo = ? OR usuario = ?");
        $stmt->execute([$correo, $usuario]);
        foreach ($stmt->fetchAll() as $fila) {
            if (strcasecmp($fila['correo'], $correo) === 0) {
                $errores[] = 'Ese correo ya está registrado.';
            }
            if (strcasecmp($fila['usuario'], $usuario) === 0) {
                $errores[] = 'Ese nombre de usuario ya está en uso.';
            }
        }
    }

    // ---------- Guardar ----------
    if (!$errores) {
        try {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO usuarios (nombre, correo, usuario, password) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$nombre, $correo, $usuario, $hash]);

            // Iniciar sesión automáticamente
            session_regenerate_id(true);
            $_SESSION['usuario_id']     = (int)$pdo->lastInsertId();
            $_SESSION['usuario_nombre'] = $nombre;

            header('Location: index.php');
            exit;

        } catch (PDOException $e) {
            // 23000 = violación de UNIQUE (alguien se registró justo al mismo tiempo)
            $errores[] = ($e->getCode() == 23000)
                ? 'Ese correo o usuario ya está registrado.'
                : 'No pudimos crear tu cuenta. Inténtalo más tarde.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Crear cuenta | EcoBag</title>
  <link rel="stylesheet" href="css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<main class="auth">

  <!-- ===== Lado izquierdo: imagen ===== -->
  <div class="auth-img auth-img-registro">
    <a href="index.php" class="auth-logo"><i class="fa-solid fa-leaf"></i> EcoBag</a>
    <div>
      <h2>Únete a EcoBag</h2>
      <p>Crea tu cuenta y sé parte de una comunidad que cuida el planeta.</p>
    </div>
  </div>

  <!-- ===== Lado derecho: formulario ===== -->
  <div class="auth-form">
    <div class="auth-caja">
    <a href="index.php" class="logo logo-movil"><i class="fa-solid fa-leaf"></i> EcoBag</a>
      <h1>Crear cuenta</h1>
      <p class="auth-sub">Completa tus datos para registrarte.</p>

      <?php if ($errores): ?>
        <div class="errores">
          <ul>
            <?php foreach ($errores as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <div id="error-js" class="errores" style="display:none;"></div>

      <form method="post" id="form-registro" autocomplete="on">
        <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

        <div class="campo">
          <i class="fa-regular fa-user"></i>
          <input type="text" name="nombre" placeholder="Nombre completo"
                 value="<?= htmlspecialchars($nombre) ?>"
                 required minlength="3" maxlength="100" autocomplete="name">
        </div>

        <div class="campo">
          <i class="fa-regular fa-envelope"></i>
          <input type="email" name="correo" placeholder="Correo electrónico"
                 value="<?= htmlspecialchars($correo) ?>"
                 required maxlength="150" autocomplete="email">
        </div>

        <div class="campo">
          <i class="fa-solid fa-at"></i>
          <input type="text" name="usuario" placeholder="Usuario"
                 value="<?= htmlspecialchars($usuario) ?>"
                 required minlength="4" maxlength="20" pattern="[a-zA-Z0-9_]+"
                 title="Letras, números o guion bajo" autocomplete="username">
        </div>

        <div class="campo">
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password" id="password" placeholder="Contraseña (mínimo 8 caracteres)"
                 required minlength="8" maxlength="72" autocomplete="new-password">
          <button type="button" class="ver-pass" data-target="password" aria-label="Mostrar contraseña">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>

        <div class="campo">
          <i class="fa-solid fa-lock"></i>
          <input type="password" name="password2" id="password2" placeholder="Confirmar contraseña"
                 required minlength="8" maxlength="72" autocomplete="new-password">
          <button type="button" class="ver-pass" data-target="password2" aria-label="Mostrar contraseña">
            <i class="fa-regular fa-eye"></i>
          </button>
        </div>

        <button type="submit" class="btn btn-bloque">Registrarse</button>
      </form>

      <p class="auth-pie">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
    </div>
  </div>

</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>