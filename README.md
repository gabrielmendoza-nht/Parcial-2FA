# Parcial-2FA
Estudiantes: Gabriel Mendoza Samuel Orocú
# Sistema de Autenticación 2FA en PHP (POO)

Proyecto de autenticación con verificación en dos pasos (2FA) mediante código QR (`Sonata\GoogleAuthenticator`), programación orientada a objetos (POO), control de sesiones, protección Anti-CSRF, sanitización estática, hashing seguro de contraseñas y gestión de base de datos con privilegios mínimos en MySQL.

## Características principales

* **Principio de privilegios mínimos en BD:** Conexión a la base de datos `company_info` usando un usuario dedicado (`'user'@'localhost'`) restringido a `SELECT`, `INSERT` y `UPDATE` (sin acceso de superusuario).
* **Registro y control de duplicados:** Formulario de registro (`registro.php`) con validaciones en cliente y servidor, detección de usuario o correo ya registrados, y clase `RegistroUsuario` con métodos de única responsabilidad (`validarDatos()`, `existeUsuarioOCorreo()`, `crearHashPassword()`, `guardarUsuario()`).
* **Sanitización estática:** Clase `SanitizarEntrada` con métodos estáticos para limpiar y escapar entradas y salidas (`texto()`, `usuario()`, `correo()`, etc.).
* **Hashing POO:** Clase `GestorHash` (`generar()`, `verificar()`, `necesitaRehash()`) respaldada por `password_hash()` y `password_verify()`, junto con la interfaz interactiva en `hash_tool.php` y el almacenamiento del hash en la columna `HashMagic`.
* **Autenticación 2FA con Código QR:** Generación y validación de códigos QR con `Sonata\GoogleAuthenticator`, control de variables de sesión intermedias (`$_SESSION['qr_solicitud_exitosa']`, `$_SESSION['usuario_pendiente_2fa']`) y elevación de sesión autenticada (`$_SESSION['autenticado'] = "SI"`).
* **Protección Anti-CSRF:** Clase `CsrfToken` (`generar()` y `validar()`) aplicada en formularios `POST` mediante campos ocultos y validación previa al procesamiento.

## Estructura del proyecto

```text
├── clases/
│   ├── CsrfToken.php
│   ├── GestorHash.php
│   ├── RegistroUsuario.php
│   └── SanitizarEntrada.php
├── css/
│   └── registro.css
├── activar_2fa.php
├── hash_tool.php
├── login.php
├── login_form.php
├── registro.php
└── verificar_2fa.php
