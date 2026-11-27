<?php
class GestorHash {
    private string $algo;

    public function __construct(string $algo = PASSWORD_DEFAULT) {
        $this->algo = $algo;
    }

    public function generar(string $textoPlano): string {
        return password_hash($textoPlano, $this->algo);
    }

    public function verificar(string $textoPlano, string $hashExistente): bool {
        if ($textoPlano === '' || $hashExistente === '') {
            return false;
        }
        return password_verify($textoPlano, $hashExistente);
    }

    public function necesitaRehash(string $hashExistente): bool {
        return password_needs_rehash($hashExistente, $this->algo);
    }
}