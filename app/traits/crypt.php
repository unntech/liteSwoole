<?php

namespace App\traits;

use LiPhp\LiCrypt;

trait crypt
{
    /**
     * @var LiCrypt
     */
    protected LiCrypt $trait_jwt;

    public function construct(): void
    {
        $this->trait_jwt = new LiCrypt(DT_KEY);
    }

    public function getToken(array $jwt, int $exp = 0, bool|string $salt = false): bool|string
    {
        if (empty($this->trait_jwt)) {
            $this->construct();
        }
        return $this->trait_jwt->getToken($jwt, $exp, $salt);
    }

    public function verifyToken(string $Token, bool|string|null $salt = null): bool|array
    {
        if (empty($this->trait_jwt)) {
            $this->construct();
        }
        return $this->trait_jwt->verifyToken($Token, $salt);
    }

    public function jencrypt($arr, $key = '', $iv = ''): bool|string
    {
        if (empty($this->trait_jwt)) {
            $this->construct();
        }
        return $this->trait_jwt->jencrypt($arr, $key, $iv);
    }

    public function jdecrypt($ciphertext, $key = '', $iv = '')
    {
        if (empty($this->trait_jwt)) {
            $this->construct();
        }
        return $this->trait_jwt->jdecrypt($ciphertext, $key, $iv);
    }
}