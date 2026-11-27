<?php
session_start();
require_once __DIR__ . '/clases/CsrfToken.php';
require_once __DIR__ . '/clases/SanitizarEntrada.php';
require_once __DIR__ . '/clases/GestorHash.php';

$csrfToken = CsrfToken::generar();
$gestorHash = new GestorHash(PASSWORD_DEFAULT);

$hashGenerado = '';
$resultadoVerificacion = null;
$errorMsg = '';
$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CsrfToken::validar($_POST['csrf_token'] ?? null)) {
        $errorMsg = 'Token CSRF inválido. Recarga la página e intenta de nuevo.';
    } elseif ($accion === 'generar') {
        $texto = $_POST['texto_generar'] ?? '';
        if ($texto === '') {
            $errorMsg = 'Ingresa un texto o contraseña para generar el hash.';
        } else {
            $hashGenerado = $gestorHash->generar($texto);
        }
    } elseif ($accion === 'verificar') {
        $texto = $_POST['texto_verificar'] ?? '';
        $hashIngresado = trim($_POST['hash_verificar'] ?? '');
        if ($texto === '' || $hashIngresado === '') {
            $errorMsg = 'Ingresa tanto el texto como el hash a validar.';
        } else {
            $resultadoVerificacion = $gestorHash->verificar($texto, $hashIngresado);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herramienta POO de Hash - 2FA</title>
    <link rel="stylesheet" href="css/registro.css">
    <style>
        .tool-container {
            width: 100%;
            max-width: 640px;
            background: #ffffff;
            border-radius: 16px;
            padding: 32px 28px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28);
        }
        .section-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            background: #f8fafc;
        }
        .section-box h3 {
            margin-bottom: 12px;
            font-size: 1.05rem;
            color: #0f172a;
        }
        .hash-output {
            margin-top: 12px;
            padding: 12px;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 8px;
            font-family: Consolas, Monaco, monospace;
            font-size: 0.85rem;
            word-break: break-all;
        }
        .badge-ok {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            font-weight: 600;
        }
        .badge-fail {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 14px;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <main class="tool-container">
        <div class="auth-header">
            <span class="auth-badge">POO Criptografía</span>
            <h2>Generador y Validador de Hash</h2>
            <p class="auth-subtitle">Prueba con <code>password_hash()</code> y <code>password_verify()</code></p>
        </div>

        <?php if ($errorMsg !== ''): ?>
            <div class="alert alert-error">
                <p><?= SanitizarEntrada::texto($errorMsg) ?></p>
            </div>
        <?php endif; ?>

        <section class="section-box">
            <h3>1. Generar Hash</h3>
            <form method="POST" action="hash_tool.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="accion" value="generar">

                <div class="form-group">
                    <label for="texto_generar">Contraseña o texto</label>
                    <input type="text" id="texto_generar" name="texto_generar" required
                           value="<?= htmlspecialchars($_POST['texto_generar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <button type="submit" class="btn-primary">Generar Hash</button>
            </form>

            <?php if ($hashGenerado !== ''): ?>
                <div class="hash-output"><?= htmlspecialchars($hashGenerado, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
        </section>

        <section class="section-box">
            <h3>2. Validar Hash</h3>
            <form method="POST" action="hash_tool.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="accion" value="verificar">

                <div class="form-group">
                    <label for="texto_verificar">Contraseña o texto a comprobar</label>
                    <input type="text" id="texto_verificar" name="texto_verificar" required
                           value="<?= htmlspecialchars($_POST['texto_verificar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <div class="form-group">
                    <label for="hash_verificar">Hash almacenado</label>
                    <input type="text" id="hash_verificar" name="hash_verificar" required
                           value="<?= htmlspecialchars($_POST['hash_verificar'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>

                <button type="submit" class="btn-primary">Verificar Coincidencia</button>
            </form>

            <?php if ($resultadoVerificacion === true): ?>
                <div class="badge-ok">Válido: el texto coincide con el hash.</div>
            <?php elseif ($resultadoVerificacion === false): ?>
                <div class="badge-fail">No válido: el texto no coincide con el hash.</div>
            <?php endif; ?>
        </section>

        <p class="auth-footer">
            <a href="registro.php">Registro</a> · <a href="login_form.php">Login</a> · <a href="activar_2fa.php">2FA QR</a>
        </p>
    </main>
</body>
</html>