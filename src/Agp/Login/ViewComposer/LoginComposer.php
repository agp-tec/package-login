<?php


namespace Agp\Login\ViewComposer;


use Agp\Login\Model\Service\UsuarioService;
use Agp\Login\Utils\LoginUtils;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginComposer
{
    /** Retorna o formulario de entrada de usuario
     * @return Application|Factory|View
     */
    public static function index()
    {
        $contas = (new UsuarioService)->getAccountsForLogin();
        $accept = LoginUtils::getAcceptLoginTitle();
        return view('Login::login.index', compact('contas', 'accept'));
    }

    /** Retorna o formulario de registro de usuario
     * @return Application|Factory|View
     */
    public static function create()
    {
        return view('Login::login.create');
    }

    /** Retorna o formulario de login de usuario
     * @return Application|Factory|View
     * @throws ValidationException
     */
    public static function pass($user)
    {
        if (!$user)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado']);
        return view('Login::login.pass', compact('user'));
    }

    /** Retorna o formulario de selecao de empresa
     * @return Application|Factory|View
     */
    public static function empresa($empresas)
    {
        return view('Login::login.empresa', compact('empresas'));
    }

    /** Retorna o formulario de insercao de senha
     * @param Model|object $usuario
     * @param string $token
     * @return Application|Factory|View
     */
    public static function recover($usuario, $token)
    {
        return view('Login::login.recover', compact('usuario', 'token'));
    }
}
