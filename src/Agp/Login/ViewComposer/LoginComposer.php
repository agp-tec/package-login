<?php


namespace Agp\Login\ViewComposer;


use Agp\Login\Model\Service\UsuarioService;
use Agp\Notification\Model\Entity\Usuario;
use Illuminate\Validation\ValidationException;

class LoginComposer
{
    /** Retorna o formulario de entrada de usuario
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function index()
    {
        $contas = (new UsuarioService)->getAccountsForLogin();
        return view('Login::login.index', compact('contas'));
    }

    /** Retorna o formulario de registro de usuario
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function create()
    {
        return view('Login::login.create');
    }

    /** Retorna o formulario de login de usuario
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function pass($user)
    {
        if (!$user)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado']);
        return view('Login::login.pass', compact('user'));
    }

    /** Retorna o formulario de selecao de empresa
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public static function empresa($empresas)
    {
        return view('Login::login.empresa', compact('empresas'));
    }
}
