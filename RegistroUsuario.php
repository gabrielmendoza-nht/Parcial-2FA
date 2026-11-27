<?php
class RegistroUsuario {
    private mysqli $db;

    public function __construct(mysqli $db) {
        $this->db = $db;
    }

    public function validarDatos(
        string $nombre,
        string $apellido,
        string $usuario,
        string $correo,
        string $password,
        string $confirmar
    ): array {
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if ($apellido === '') {
            $errores[] = 'El apellido es obligatorio.';
        }
        if ($usuario === '' || strlen($usuario) < 4) {
            $errores[] = 'El usuario debe tener al menos 4 caracteres.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electronico valido.';
        }
        if (strlen($password) < 8) {
            $errores[] = 'La contrasena debe tener al menos 8 caracteres.';
        }
        if ($password !== $confirmar) {
            $errores[] = 'Las contrasenas no coinciden.';
        }

        return $errores;
    }

    public function existeUsuarioOCorreo(string $usuario, string $correo): bool {
        $sql = "SELECT id FROM usuarios WHERE Usuario = ? OR Correo = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ss', $usuario, $correo);
        $stmt->execute();
        $stmt->store_result();
        $existe = $stmt->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    public function crearHashPassword(string $password): string {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function guardarUsuario(
        string $nombre,
        string $apellido,
        string $usuario,
        string $correo,
        string $hashMagic
    ): bool {
        $sql = "INSERT INTO usuarios (Nombre, Apellido, Usuario, Correo, HashMagic) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('sssss', $nombre, $apellido, $usuario, $correo, $hashMagic);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }
}