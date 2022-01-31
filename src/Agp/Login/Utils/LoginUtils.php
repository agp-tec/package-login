<?php

namespace Agp\Login\Utils;


use Illuminate\Support\Facades\Validator;

class LoginUtils
{
    /**
     * Retorna o texto dos campos aceitos para login
     * @return string
     */
    public static function getAcceptLoginTitle()
    {
        $accept = config('login.login_accept', []);
        $title = '';
        $c = count($accept);
        if ($c == 0)
            return 'Login';
        $keys = array_keys($accept);
        for ($i = 0; $i < $c; $i++) {
            $title .= $keys[$i] == 'cpf' ? strtoupper($keys[$i]) : ucwords($keys[$i]);
            if (($i + 2) < $c)
                $title .= ', ';
            elseif (($i + 2) == $c)
                $title .= ' ou ';
        }
        return $title;
    }

    /**
     * Valida se $cpf é um cpf válido
     * @param string $cpf CPF sem mascara
     * @return boolean
     */
    public static function validaCpf($cpf)
    {
        try {
            Validator::make(['cpf' => $cpf], ['cpf' => 'required|cpf'])->validate();
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }
}
