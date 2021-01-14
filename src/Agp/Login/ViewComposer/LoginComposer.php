<?php


namespace Agp\Login\ViewComposer;


use Agp\Login\Model\Service\UsuarioService;
use Agp\Notification\Model\Entity\Usuario;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
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
        return view('Login::login.index', compact('contas'));
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
}
