# Parcial 2FA: Sistema de Login con Autenticación de Dos Factores
Estudiantes Gabriel Mendoza 8-1038-1624 
Samuel Orocú 8-1039-1344
Aplicación web en **PHP** que implementa registro de usuarios, inicio de sesión con registro de sesiones y verificación en dos pasos (2FA), aplicando buenas prácticas de seguridad como hashing de contraseñas, protección CSRF y sanitización de entradas.

## Características

- Registro de usuarios
- Inicio y cierre de sesión
- Activación de autenticación de dos factores (2FA)
- Protección contra ataques CSRF
- Sanitización de datos de entrada
- Gestión de hashes de contraseñas
- Soporte para bases de datos MySQL y SQL Server (MSSQL)
- Generación de documentos PDF

## ¿Cómo funciona?

El sistema protege el acceso con **dos factores**: algo que el usuario sabe (su contraseña) y algo que tiene (su celular con una app autenticadora como Google Authenticator). Sin ambos, no se puede ingresar.

### Flujo general

```
Registro → hash de contraseña → Base de datos
              ↓
        Activar 2FA → clave secreta → QR → app del celular
              ↓
Login → valida CSRF → valida contraseña → pide código 2FA → crea sesión → index.php
```

### 1. Registro (`registro.php`, `RegistroUsuario.php`)

El usuario completa el formulario de registro. Antes de guardar cualquier dato, `SanitizarEntrada.php` limpia las entradas para prevenir XSS e inyección SQL. Luego `GestorHash.php` convierte la contraseña en un hash, de modo que **nunca se almacena en texto plano**. Finalmente, los datos se guardan en la base de datos mediante `conexion.php` y `mysql.inc.php` o `mssql.inc.php`.

### 2. Activación del 2FA (`activar_2fa.php`)

Una vez registrado, el usuario activa el segundo factor:

1. El sistema genera una **clave secreta** única para el usuario.
2. La clave se muestra (normalmente como código QR) para escanearla con una app autenticadora.
3. La app comienza a generar un **código de 6 dígitos que cambia cada 30 segundos** (TOTP).
4. La clave secreta queda guardada en la base de datos asociada al usuario.

### 3. Inicio de sesión (`login_form.php`, `login.php`)

1. El formulario incluye un **token CSRF** oculto generado por `CsrfToken.php`.
2. Al enviarlo, el servidor verifica que el token coincida. Esto evita que un sitio externo envíe el formulario en nombre del usuario.
3. Se compara el hash de la contraseña ingresada con el almacenado.
4. Si es correcta y el usuario tiene 2FA activo, se solicita el **código de 6 dígitos** de la app.
5. El servidor calcula el código esperado con la clave secreta y lo compara con el ingresado.
6. Si coincide, se crea la sesión.

### 4. Sesión activa (`objLoginAdmin.php`, `index.php`)

Al autenticarse, se guarda la sesión de PHP (`$_SESSION`) y se registra el inicio de sesión. `index.php` verifica que exista una sesión válida antes de mostrar el contenido; de lo contrario, redirige al login.

### 5. Cierre de sesión (`salir.php`)

Destruye la sesión y devuelve al usuario al formulario de login.

## Estructura del proyecto

```
Parcial-2FA/
├── index.php              # Página principal (requiere sesión activa)
├── login_form.php         # Formulario de inicio de sesión
├── login.php              # Procesa el inicio de sesión
├── registro.php           # Formulario de registro de usuarios
├── RegistroUsuario.php    # Clase/lógica de registro de usuarios
├── activar_2fa.php        # Activación del segundo factor de autenticación
├── salir.php              # Cierre de sesión
├── objLoginAdmin.php      # Objeto de login/administración de sesiones
├── cEmpleado.php          # Clase de empleado
├── CsrfToken.php          # Generación y validación de tokens CSRF
├── GestorHash.php         # Gestión de hashing de contraseñas
├── hash_tool.php          # Herramienta auxiliar para generar/probar hashes
├── SanitizarEntrada.php   # Limpieza y validación de datos de entrada
├── conexion.php           # Conexión a la base de datos
├── mysql.inc.php          # Capa de acceso a MySQL
├── mssql.inc.php          # Capa de acceso a SQL Server
├── setting.inc.php        # Configuración general (credenciales, parámetros)
├── Ejemplo1_pdf.php       # Ejemplo de generación de PDF
├── composer.json          # Dependencias del proyecto
└── composer.lock          # Versiones bloqueadas de dependencias
```

## Requisitos

- PHP 7.4 o superior
- [Composer](https://getcomposer.org/)
- Servidor web (Apache, Nginx o XAMPP/WAMP)
- MySQL o SQL Server
- App de autenticación en el celular (Google Authenticator, Authy, etc.)

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/gabrielmendoza-nht/Parcial-2FA.git
   cd Parcial-2FA
   ```

2. Instala las dependencias:
   ```bash
   composer install
   ```

3. Configura la conexión a la base de datos en `setting.inc.php` (host, usuario, contraseña y nombre de la base de datos).

4. Crea la base de datos y las tablas necesarias (usuarios, sesiones, etc.).

5. Coloca el proyecto en la carpeta de tu servidor web (por ejemplo `htdocs` en XAMPP) y abre en el navegador:
   ```
   http://localhost/Parcial-2FA/
   ```

## Seguridad

| Medida | Archivo |
|---|---|
| Tokens CSRF en formularios | `CsrfToken.php` |
| Hashing de contraseñas | `GestorHash.php`, `hash_tool.php` |
| Sanitización de entradas | `SanitizarEntrada.php` |
| Segundo factor (2FA) | `activar_2fa.php` |

> ⚠️ **Importante:** no subas credenciales reales de base de datos a GitHub. Revisa que `setting.inc.php` no contenga contraseñas reales antes de hacer commit. Si `hash_tool.php` es solo una herramienta de pruebas, no debería quedar accesible en producción.

## Autor

**Gabriel Mendoza**: [@gabrielmendoza-nht](https://github.com/gabrielmendoza-nht)

## Licencia

Proyecto académico con fines educativos.
