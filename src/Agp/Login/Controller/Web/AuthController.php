<?php

namespace App\Controller\Web;


use App\Controller\Controller;
use App\Form\PessoaForm;
use App\Mail\LoginFailMail;
use App\Model\Entity\Pessoa;
use Facades\App\Model\Repository\UsuarioRepository;
use Facades\App\Model\Service\UsuarioService;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Kris\LaravelFormBuilder\Facades\FormBuilder;

class AuthController extends Controller
{
    /** Retorna o formulario de login
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $contas = UsuarioService::getAccountsForLogin();
        return view('login.index', compact('contas'));
    }

    /** Realiza login com os parametros enviados via post (email, password, empresa (opcional)) Se empresa não é enviada, é direcionado para lista de empresas.
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $res = UsuarioService::login($request['email'], $request['password'], $request['empresa']);
        return $res->getResponseWeb();
    }

    public function empresaList(Request $request)
    {
        $empresas = request()->session()->get('empresas');
        return view('login.empresa', compact('empresas'));
    }

    public function empresa(Request $request)
    {
        request()->session()->pull('token');
        request()->session()->pull('empresas');
        $res = UsuarioService::loginEmpresa($request->empresa);
        return $res->getResponseWeb();
    }

    public function store(Request $request)
    {
        $nome = $request['nome'];
        $cpf_cnpj = $request['cpf_cnpj'];
        $email = $request['usuario']['email'];
        $senha = $request['usuario']['senha'];

        $res = UsuarioService::registrar($nome, $email, $senha, $cpf_cnpj);

        return $res->getResponseWeb();
    }

    public function forget(Request $request)
    {
        $email = $request->conta;
        $empresa = $request->empresa;
        UsuarioService::removeAccountCookie($email, $empresa);
        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        UsuarioService::logoutForgetWeb();
        return redirect()->route('login');
    }

    public function logoutAll(Request $request)
    {
        UsuarioService::logoutForgetWeb(true);
        return redirect()->route('login');
    }

    public function getForm(Pessoa $pessoa)
    {
        $form = FormBuilder::create(PessoaForm::class, [
            'method' => 'POST',
            'url' => route('login.store'),
            'model' => $pessoa
        ]);
        $form->validate($pessoa->getRules());
        return $form;
    }

}
