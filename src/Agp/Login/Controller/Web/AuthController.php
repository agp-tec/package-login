<?php

namespace Agp\Login\Controller\Web;


use Agp\BaseUtils\Helper\Utils;
use Agp\Login\Controller\Controller;
use Agp\Login\Model\Service\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Retorna o formulario de login
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $contas = (new UsuarioService)->getAccountsForLogin();
        return view(config('login.view_index'), compact('contas'));
    }

    /** Retorna o formulario de registro
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view(config('login.view_registrar'));
    }

    /** Encontra usuário através do dado e-mail ou CPF
     * @return \Illuminate\View\View
     */
    public function find(Request $request)
    {
        $cpf = str_replace(['.', '-', ' '], '', $request['email_cpf']);
        $isCpf = is_numeric($cpf);
        if ($isCpf) {
            $cpf = Utils::mask('###.###.###-##', $cpf);
            $request->merge(['email_cpf' => $cpf]);
            $rule = [
                'onsuccess' => 'nullable|string',
                'email_cpf' => 'required|cpf|formato_cpf',
            ];
        } else {
            $rule = [
                'email_cpf' => 'required|email',
                'onsuccess' => 'nullable|string',
            ];
        }
        Validator::make($request->all(), $rule)->validate();
        if (!$isCpf)
            $email = $request->get('email_cpf');
        if ($isCpf)
            $user = (new UsuarioService)->encontraUsuarioByCpf($cpf);
        else
            $user = (new UsuarioService)->encontraUsuarioByEmail($email);
        if (!$user)
            throw ValidationException::withMessages(['message' => 'Usuário "' . $request->get('email_cpf') . '" não encontrado.']);
        if ($request->get('onsuccess'))
            return redirect()->to($request->get('onsuccess'));
        return redirect()->to(URL::signedRoute('web.login.pass', ['user' => $user->email]));
    }

    /** Retorna o formulario de login
     * @return \Illuminate\View\View
     */
    public function pass(Request $request, $user)
    {
        if (!$request->hasValidSignature())
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');

        $isCpf = is_numeric($user);
        if ($isCpf)
            $usuario = (new UsuarioService)->encontraUsuarioByCpf($user);
        else
            $usuario = (new UsuarioService)->encontraUsuarioByEmail($user);
        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário "' . $user . '" não encontrado.']);
        return view(config('login.view_login'), ['user' => $usuario]);
    }

    /** Realiza o login
     * @return \Illuminate\View\View
     */
    public function login(Request $request, $user)
    {
        if (!$request->hasValidSignature())
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        $rule = [
            'password' => 'required|string',
            'email' => 'required|email',
        ];
        Validator::make($request->all(), $rule)->validate();

        if ($request->get('email') != $user)
            throw ValidationException::withMessages(['message' => 'Houve uma falha na assinatura. Tente novamente.']);

        (new UsuarioService)->login($user, $request->get('password'));

        if (config('login.use_empresa'))
            return redirect()->to(URL::signedRoute('web.login.empresaForm', ['user' => $user]));

        (new UsuarioService())->novoLoginWeb();

        return redirect()->route('web.home');
    }

    /**
     * Registra um novo usuário
     * @param Request $request
     *
     */
    public function store(Request $request)
    {
        $nome = $request['nome'];
        $cpf_cnpj = $request['cpf_cnpj'];
        $email = $request['usuario']['email'];
        $senha = $request['usuario']['senha'];

        $rule = [
            'nome' => 'required|string',
            'cpf_cnpj' => 'required|cpf_cnpj|formato_cpf_cnpj',
            'usuario.email' => 'required|email',
            'usuario.senha' => 'required|string',
        ];
        Validator::make($request->all(), $rule)->validate();

        (new UsuarioService())->registrar($request->all());

        $res = (new UsuarioService())->novoLoginWeb();
        dd($res);
        if (config('login.use_conta_id'))
            return redirect()->route('web.home', ['contaId' => $res['pos']])->withCookie($res['cookie']);
        return redirect()->route('web.home')->withCookie($res['cookie']);
    }

    /** Mostra formulario de selecao de empresa
     * @return \Illuminate\View\View
     */
    public function selecionaEmpresa(Request $request, $user)
    {
        if (!auth()->check())
            return redirect()->route('web.login.index')->withErrors('Você deve se logar para acessar a página');

        if (!$request->hasValidSignature()) {
            auth()->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        if (auth()->user()->email != $user) {
            auth()->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        $empresas = request()->session()->get('empresas');
        return view(config('login.view_empresa'), ['empresas' => $empresas]);
    }

    /** Seleciona a empresa
     * @return \Illuminate\View\View
     */
    public function empresa(Request $request, $user)
    {
        if (!auth()->check())
            return redirect()->route('web.login.index')->withErrors('Você deve se logar para acessar a página');

        if (!$request->hasValidSignature()) {
            auth()->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        if (auth()->user()->email != $user) {
            auth()->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        $rule = [
            'empresa' => 'required|numeric',
            'nome' => 'required|string',
        ];
        Validator::make($request->all(), $rule)->validate();

        (new UsuarioService())->loginEmpresa($request->get('empresa'));

        $res = (new UsuarioService())->novoLoginWeb($request->get('empresa'), $request->get('nome'));
        dump($res);
//        die('redirecting');
        if (config('login.use_conta_id'))
            return redirect()->route('web.home', ['contaId' => $res['pos']])->withCookie($res['cookie']);
        return redirect()->route('web.home')->withCookie($res['cookie']);
    }

    public function logout(Request $request)
    {
        (new UsuarioService())->logout();
        return redirect()->route('web.login.index');
    }

    public function forget(Request $request, $email, $empresa = null)
    {
        (new UsuarioService())->forget($email, $empresa);
        return redirect()->route('web.login.index');
    }
}
