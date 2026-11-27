<?php
class SanitizarEntrada {
    public static function limpiarCadena($valor): string {
        if (!is_string($valor)) {
            return '';
        }
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }

    public static function texto(string $valor): string {
        return htmlspecialchars(trim($valor), ENT_QUOTES, 'UTF-8');
    }

    public static function usuario(string $valor): string {
        $limpio = preg_replace('/[^a-zA-Z0-9_.-]/', '', trim($valor));
        return strtolower((string)$limpio);
    }

    public static function correo(string $valor): string {
        return filter_var(trim($valor), FILTER_SANITIZE_EMAIL) ?: '';
    }
}