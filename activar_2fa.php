<?php
session_start();
require 'vendor/autoload.php';
include("clases/mysql.inc.php");

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

// Solo usuarios ya autenticados
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== "SI") {
    header("Location: login.php");
    exit;
}

$db = new mod_db();
$pdo = $db->getConexion();
$usuario = $_SESSION['Usuario'];
$g = new GoogleAuthenticator();
$mensaje = '';

// Confirmar el primer código para activar 2FA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secret = $_SESSION['secret_temporal'] ?? '';
    $codigo = trim($_POST['codigo'] ?? '');

    if ($secret && $g->checkCode($secret, $codigo)) {
        $stmt = $pdo->prepare("UPDATE usuarios SET secret_2fa = ? WHERE Usuario = ?");
        $stmt->execute([$secret, $usuario]);
        unset($_SESSION['secret_temporal']);
        $mensaje = "<p class='msg ok'>2FA activado. Desde ahora se te pedirá el código al iniciar sesión.</p>";
    } else {
        $mensaje = "<p class='msg err'>Código incorrecto. Escribe el código actual de la app, sin recargar la página.</p>";
    }
}

// Generar secreto temporal (una vez por sesión)
if (!isset($_SESSION['secret_temporal'])) {
    $_SESSION['secret_temporal'] = $g->generateSecret();
}
$secret = $_SESSION['secret_temporal'];

// generate() ya devuelve la URL completa de la imagen del QR
$qr_url = GoogleQrUrl::generate($usuario, $secret, 'MiSistema');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activar verificación en dos pasos</title>
    <style>
/* Estilos para activar_2fa.php y verificar_2fa.php */
:root {
    --ink: #16313a;
    --muted: #5b747c;
    --line: #d3e1e4;
    --bg: #e9f1f2;
    --field: #f5f9fa;
    --accent: #0e7c86;
    --accent-dark: #0a6169;
    --ok-bg: #e3f4ea;
    --ok: #17653d;
    --err-bg: #fdeceb;
    --err: #a12d28;
}

* { box-sizing: border-box; }

body.tfa {
    margin: 0;
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 24px 16px;
    background: var(--bg);
    color: var(--ink);
    font-family: "Segoe UI", system-ui, -apple-system, Roboto, Arial, sans-serif;
    line-height: 1.5;
}

.tfa-card {
    width: 100%;
    max-width: 440px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    padding: 32px 28px;
    box-shadow: 0 14px 34px -18px rgba(14, 60, 70, .45);
}

.tfa-card h1 {
    margin: 0 0 8px;
    font-size: 1.5rem;
    line-height: 1.25;
}

.tfa-lead {
    margin: 0 0 24px;
    color: var(--muted);
}

.tfa-card label {
    display: block;
    margin-bottom: 8px;
    font-size: .92rem;
    font-weight: 600;
}

/* El código: el elemento protagonista */
.tfa-code {
    width: 100%;
    padding: 14px 8px 14px calc(8px + .45em);
    font-size: 2rem;
    font-family: ui-monospace, "Cascadia Mono", Consolas, monospace;
    font-variant-numeric: tabular-nums;
    letter-spacing: .45em;
    text-align: center;
    color: var(--ink);
    background: var(--field);
    border: 2px solid var(--line);
    border-radius: 10px;
}
.tfa-code:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(14, 124, 134, .22);
}
.tfa-code::placeholder { color: #b5c6ca; letter-spacing: .45em; }

.tfa-btn {
    width: 100%;
    margin-top: 16px;
    padding: 13px 16px;
    font: inherit;
    font-weight: 600;
    color: #fff;
    background: var(--accent);
    border: 0;
    border-radius: 10px;
    cursor: pointer;
    transition: background-color .15s;
}
.tfa-btn:hover { background: var(--accent-dark); }
.tfa-btn:focus-visible { outline: 3px solid rgba(14, 124, 134, .4); outline-offset: 2px; }

.tfa-link {
    display: block;
    margin-top: 18px;
    text-align: center;
    color: var(--accent-dark);
    font-size: .92rem;
}

/* Mensajes */
.msg {
    margin: 14px 0 0;
    padding: 10px 12px;
    font-size: .92rem;
    border-radius: 8px;
}
.msg.ok  { background: var(--ok-bg);  color: var(--ok); }
.msg.err { background: var(--err-bg); color: var(--err); }

/* Pantalla de activación */
.tfa-steps {
    margin: 0 0 8px;
    padding-left: 22px;
}
.tfa-steps > li {
    margin-bottom: 22px;
    padding-left: 4px;
    font-weight: 600;
}
.tfa-steps > li::marker { color: var(--accent); font-weight: 700; }
.tfa-steps > li > .tfa-body { font-weight: 400; margin-top: 12px; }

.tfa-qr { text-align: center; }
.tfa-qr img {
    width: 200px;
    height: 200px;
    padding: 10px;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 12px;
}

.tfa-manual { margin-top: 12px; font-size: .9rem; color: var(--muted); }
.tfa-manual summary { cursor: pointer; }
.tfa-key {
    display: block;
    margin-top: 8px;
    padding: 9px 12px;
    font-family: ui-monospace, "Cascadia Mono", Consolas, monospace;
    font-size: .95rem;
    letter-spacing: .08em;
    color: var(--ink);
    background: var(--field);
    border: 1px solid var(--line);
    border-radius: 8px;
    word-break: break-all;
    user-select: all;
}

@media (max-width: 420px) {
    .tfa-card { padding: 24px 18px; }
    .tfa-code { font-size: 1.6rem; }
}
@media (prefers-reduced-motion: reduce) {
    .tfa-btn { transition: none; }
}

    </style>
</head>
<body class="tfa">
    <main class="tfa-card">
        <h1>Activar verificación en dos pasos</h1>
        <p class="tfa-lead">Protege tu cuenta con un código temporal de Google Authenticator.</p>

        <ol class="tfa-steps">
            <li>
                Escanea el código QR con la app
                <div class="tfa-body">
                    <div class="tfa-qr">
                        <img src="<?= htmlspecialchars($qr_url) ?>" alt="Código QR para Google Authenticator">
                    </div>
                    <details class="tfa-manual">
                        <summary>¿No puedes escanear? Escribe la clave a mano</summary>
                        <span class="tfa-key"><?= htmlspecialchars($secret) ?></span>
                    </details>
                </div>
            </li>
            <li>
                Escribe el código de 6 dígitos
                <div class="tfa-body">
                    <form method="POST" autocomplete="off">
                        <input class="tfa-code" type="text" name="codigo" aria-label="Código de 6 dígitos"
                               inputmode="numeric" pattern="\d{6}" maxlength="6" placeholder="000000"
                               autocomplete="one-time-code" required>
                        <?= $mensaje ?>
                        <button class="tfa-btn" type="submit">Verificar y activar</button>
                    </form>
                </div>
            </li>
        </ol>

        <a class="tfa-link" href="formularios/PanelControl.php">Volver al panel</a>
    </main>
</body>
</html>