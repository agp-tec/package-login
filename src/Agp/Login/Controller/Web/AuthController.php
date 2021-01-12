<?php

namespace App\Controller\Web;


use Agp\Login\Controller\Controller;
use Agp\Login\Model\Service\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** Retorna o formulario de login
     * @return View
     */
    public function index(Request $request)
    {
        $contas = (new UsuarioService)->getAccountsForLogin();
        return view(config('login.view_index'), compact('contas'));
    }
}
