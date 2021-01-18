<?php

namespace Agp\Login\Controller\Web;


use Agp\BaseUtils\Helper\Utils;
use Agp\Login\Controller\Controller;
use Agp\Login\Model\Service\UsuarioService;
use Agp\Login\Utils\LoginUtils;
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
        $accept = LoginUtils::getAcceptLoginTitle();
        return view(config('login.view_index'), compact('contas', 'accept'));
    }

    /** Retorna o formulario de registro
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view(config('login.view_registrar'));
    }

    /** Encontra usuário através do dado e-mail ou CPF
     * @return \Illuminate\Http\RedirectResponse
     * @throws ValidationException
     */
    public function find(Request $request)
    {
        $rule = config('login.login_accept');
        $cpf = str_replace(['.', '-', ' '], '', $request['login']);
        $isCpf = is_numeric($cpf);
        //TODO Fazer login via telefone se não for cpf
        //   $isCpf = LoginUtils::validaCpf($request);
        if ($isCpf && array_key_exists('cpf', $rule)) {
            $cpf = Utils::mask('###.###.###-##', $cpf);
            $request->merge(['cpf' => $cpf]);
            $rule = [
                'cpf' => $rule['cpf'],
                'onsuccess' => 'nullable|string',
            ];
        } elseif (array_key_exists('e-mail', $rule)) {
            $request->merge(['e-mail' => $request['login']]);
            $rule = [
                'e-mail' => $rule['e-mail'],
                'onsuccess' => 'nullable|string',
            ];
        }
        Validator::make($request->all(), $rule)->validate();
        if ($isCpf)
            $user = (new UsuarioService)->encontraUsuarioByCpf($cpf);
        else
            $user = (new UsuarioService)->encontraUsuarioByEmail($request->get('e-mail'));

        if (!$user) {
            $redirect = config('login.user_notfound_route');
            if ($redirect == 'web.login.index')
                return redirect()->route()
                    ->withInput($request->all())
                    ->with('error', 'Usuário "' . ($isCpf ? $cpf : $request->get('e-mail')) . '" não encontrado.');
            return redirect()->route()->withInput($request->all());
        }
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
        if (config('login.use_empresa')) {
            if (auth()->payload() && auth()->payload()['empresaId'])
                return redirect()->route(config('login.pos_login_route'));
            return redirect()->to(URL::signedRoute('web.login.empresaForm', ['user' => $user]));
        }

        return redirect()->route(config('login.pos_login_route'));
    }

    /**
     * Registra um novo usuário
     * @param Request $request
     *
     */
    public function store(Request $request)
    {
        $rule = [
            'nome' => 'required|string',
            'cpf' => 'required|cpf|formato_cpf',
            'e-mail' => 'required|email',
            'usuario.senha' => 'required|string',
        ];
        Validator::make($request->all(), $rule)->validate();

        (new UsuarioService())->registrar($request->all());

        return redirect()->route(config('login.pos_login_route'));
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
            (new UsuarioService())->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        if (auth()->user()->email != $user) {
            (new UsuarioService())->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        $rule = [
            'empresa' => 'required|numeric',
            'nome' => 'required|string',
        ];
        Validator::make($request->all(), $rule)->validate();

        (new UsuarioService())->loginEmpresa($request->get('empresa'));

        return redirect()->route(config('login.pos_login_route'));
    }

    /** Envia e-mail de recuperação de senha
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function recover(Request $request, $user)
    {
        if (!$request->hasValidSignature())
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');

        if ((new UsuarioService())->recuperaSenha($user))
            return redirect()->route('web.login.index')->with('success', 'Um e-mail foi enviado. Siga as instruções nele contidas.');
        return redirect()->route('web.login.index')->with('error', 'Não foi possível concluir a solicitação. Tente novamente.');
    }

    /** Desloga usuário
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        (new UsuarioService())->logout();
        return redirect()->route('web.login.index');
    }

    /** Desloga usuário de todos os dispositivos e aplicativos
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logoutAll(Request $request)
    {
        (new UsuarioService())->logoutAll();
        return redirect()->route('web.login.index');
    }

    /** Remove usuario do cookie
     * @param Request $request
     * @param string $email
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forget(Request $request, $email)
    {
        (new UsuarioService())->forget($email);
        return redirect()->route('web.login.index');
    }
}
