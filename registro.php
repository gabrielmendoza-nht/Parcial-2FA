<?php
session_start();
require_once __DIR__ . '/clases/CsrfToken.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/RegistroUsuario.php';

// Conexión usando el usuario con privilegios mínimos
$mysqli = new mysqli('localhost', 'user', 'Clave123', 'company_info');
if ($mysqli->connect_errno) {
    die('Error de conexión a la base de datos.');
}
$mysqli->set_charset('utf8mb4');

$csrfToken = CsrfToken::generar();
$mensajesError = [];
$mensajeExito = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfToken::validar($_POST['csrf_token'] ?? null)) {
        $mensajesError[] = 'Token CSRF inválido. Recarga la página e intenta de nuevo.';
    } else {
        $nombreLimpio   = SanitizarEntrada::texto($_POST['nombre'] ?? '');
        $apellidoLimpio = SanitizarEntrada::texto($_POST['apellido'] ?? '');
        $usuarioLimpio  = SanitizarEntrada::usuario($_POST['usuario'] ?? '');
        $correoLimpio   = SanitizarEntrada::correo($_POST['correo'] ?? '');
        $passwordRaw    = $_POST['password'] ?? '';
        $confirmarRaw   = $_POST['confirmar_password'] ?? '';

        $registro = new RegistroUsuario($mysqli);
        $mensajesError = $registro->validarDatos(
            $nombreLimpio,
            $apellidoLimpio,
            $usuarioLimpio,
            $correoLimpio,
            $passwordRaw,
            $confirmarRaw
        );

        if (empty($mensajesError) && $registro->existeUsuarioOCorreo($usuarioLimpio, $correoLimpio)) {
            $mensajesError[] = 'El nombre de usuario o correo ya está registrado.';
        }

        if (empty($mensajesError)) {
            $hashMagic = $registro->crearHashPassword($passwordRaw);
            if ($registro->guardarUsuario($nombreLimpio, $apellidoLimpio, $usuarioLimpio, $correoLimpio, $hashMagic)) {
                $_SESSION['qr_solicitud_exitosa'] = true;
                $_SESSION['usuario_pendiente_2fa'] = $usuarioLimpio;
                $mensajeExito = 'Usuario creado correctamente. Ya puedes continuar a la configuración del código QR (2FA).';
            } else {
                $mensajesError[] = 'No se pudo guardar el usuario en la base de datos.';
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
    <title>Registro de Usuario - 2FA</title>
    <link rel="stylesheet" href="css/registro.css">
</head>
<body>
    <main class="auth-container">
        <div class="auth-header">
            <span class="auth-badge">Seguridad 2FA</span>
            <h2>Crear Cuenta</h2>
            <p class="auth-subtitle">Registra tus datos para activar la verificación en dos pasos</p>
        </div>

        <?php if (!empty($mensajesError)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($mensajesError as $err): ?>
                        <li><?= SanitizarEntrada::texto($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($mensajeExito !== ''): ?>
            <div class="alert alert-success">
                <p><?= SanitizarEntrada::texto($mensajeExito) ?></p>
                <a href="activar_2fa.php">Configurar Código QR</a>
            </div>
        <?php endif; ?>

        <form id="formRegistro" method="POST" action="registro.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-grid-2">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required
                           value="<?= htmlspecialchars($_POST['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" required
                           value="<?= htmlspecialchars($_POST['apellido'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required minlength="4"
                       value="<?= htmlspecialchars($_POST['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" required
                       value="<?= htmlspecialchars($_POST['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="confirmar_password">Confirmar contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" required minlength="8">
            </div>

            <button type="submit" class="btn-primary">Registrarse</button>
            <p class="auth-footer">¿Ya tienes cuenta? <a href="login_form.php">Inicia sesión aquí</a></p>
        </form>
    </main>

    <script>
    document.getElementById('formRegistro').addEventListener('submit', function (e) {
        const nombre = document.getElementById('nombre').value.trim();
        const apellido = document.getElementById('apellido').value.trim();
        const usuario = document.getElementById('usuario').value.trim();
        const correo = document.getElementById('correo').value.trim();
        const pass = document.getElementById('password').value;
        const conf = document.getElementById('confirmar_password').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (nombre.length === 0 || apellido.length === 0) {
            alert('Nombre y apellido son obligatorios.');
            e.preventDefault();
            return;
        }
        if (usuario.length < 4) {
            alert('El usuario debe tener al menos 4 caracteres.');
            e.preventDefault();
            return;
        }
        if (!emailRegex.test(correo)) {
            alert('Ingresa un correo electrónico válido.');
            e.preventDefault();
            return;
        }
        if (pass.length < 8) {
            alert('La contraseña debe tener al menos 8 caracteres.');
            e.preventDefault();
            return;
        }
        if (pass !== conf) {
            alert('Las contraseñas no coinciden.');
            e.preventDefault();
        }
    });
    </script>
</body>
</html>