<?php

namespace Agp\Login\Controller\Web;


use Agp\BaseUtils\Helper\Utils;
use Agp\Login\Controller\Controller;
use Agp\Login\Model\Repository\UsuarioRepository;
use Agp\Login\Model\Service\UsuarioService;
use Agp\Login\Utils\LoginUtils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function find(Request $request)
    {
        if ($request->get('id') != '') {
            $user = (new UsuarioRepository())->getById($request->get('id'));
        } else {
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
                ];
            } elseif (array_key_exists('e-mail', $rule)) {
                $request->merge(['e-mail' => $request['login']]);
                $rule = [
                    'e-mail' => $rule['e-mail'],
                ];
            }
            Validator::make($request->all(), $rule)->validate();
            if ($isCpf)
                $user = (new UsuarioService)->encontraUsuarioByCpf($cpf);
            else
                $user = (new UsuarioService)->encontraUsuarioByEmail($request->get('e-mail'));
        }

        if (!$user) {
            $redirect = config('login.user_notfound_route');
            if ($redirect == 'web.login.index')
                return redirect()->route($redirect)
                    ->withInput($request->all())
                    ->with('error', 'Usuário não encontrado. Deseja registrar?');
            return redirect()->route($redirect)->withInput($request->all());
        }
        if ($request->get('onsuccess'))
            return redirect()->to($request->get('onsuccess'));
        return redirect()->to(URL::temporarySignedRoute('web.login.pass', now()->addMinutes(15), ['user' => $user->getKey()]));
    }

    /** Retorna o formulario de login
     * @param Request $request
     * @param int $user ID do usuário
     * @return RedirectResponse|View
     * @throws ValidationException
     */
    public function pass(Request $request, $user)
    {
        if (!$request->hasValidSignature())
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');

        $usuario = (new UsuarioRepository())->getById($user);
        if (!$usuario) {
            if ($user > 0)
                return redirect()->route('web.login.index')->withErrors('Usuário não encontrado');
            return redirect()->route('web.login.index');
        }
        return view(config('login.view_login'), ['user' => $usuario]);
    }

    /**
     * Realiza o login
     * @param Request $request
     * @param int $user ID do usuário
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function login(Request $request, $user)
    {
        if (!$request->hasValidSignature())
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');

        $usuario = (new UsuarioRepository())->getById($user);
        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado.']);

        $rule = [
            'password' => 'required|string',
        ];
        Validator::make($request->all(), $rule)->validate();

        (new UsuarioService)->login($usuario, $request->get('password'));
        if (config('login.use_empresa')) {
            $payload = auth()->payload();
            if ($payload && $payload['empresaId'])
                return redirect()->route(config('login.pos_login_route'));
            return redirect()->to(URL::temporarySignedRoute('web.login.empresaForm', now()->addMinutes(15), ['user' => $user]));
        }

        return redirect()->route(config('login.pos_login_route'));
    }

    /**
     * Registra um novo usuário
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $rule = [
            'nome' => 'required|string',
            'cpf' => 'required|cpf|formato_cpf',
            'e-mail' => 'required|email',
            'usuario.senha' => 'required|string',
            'empresa' => 'nullable',
            'empresa.id' => 'nullable|integer',
            'empresa.nome' => 'nullable|string',
            'empresa.cpf_cnpj' => 'nullable|cpf_cnpj|formato_cpf_cnpj',
        ];
        Validator::make($request->all(), $rule)->validate();

        (new UsuarioService())->registrar($request->all());

        return redirect()->route(config('login.pos_login_route'));
    }

    /** Mostra formulario de selecao de empresa
     * @param Request $request
     * @param int $user ID do usuário
     * @return RedirectResponse|View
     */
    public function selecionaEmpresa(Request $request, $user)
    {
        if (!auth()->check())
            return redirect()->route('web.login.index')->withErrors('Você deve se logar para acessar a página');

        if (!$request->hasValidSignature()) {
            (new UsuarioService())->logout();
            return redirect()->to(URL::temporarySignedRoute('web.login.pass', now()->addMinutes(15), ['user' => $user]))->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        if (auth()->user()->getKey() != $user) {
            auth()->logout();
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        $empresas = request()->session()->get('empresas');
        return view(config('login.view_empresa'), ['empresas' => $empresas]);
    }

    /** Seleciona a empresa
     * @param Request $request
     * @param int $user ID do usuário
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function empresa(Request $request, $user)
    {
        if (!auth()->check())
            return redirect()->route('web.login.index')->withErrors('Você deve se logar para acessar a página');

        if (!$request->hasValidSignature()) {
            (new UsuarioService())->logout();
            return redirect()->to(URL::temporarySignedRoute('web.login.pass', now()->addMinutes(15), ['user' => $user]))->withErrors('Houve uma falha na assinatura. Tente novamente');
        }

        if (auth()->user()->getKey() != $user) {
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
     * @param int $user ID do usuário
     * @return RedirectResponse
     * @throws \Exception
     */
    public function recover(Request $request, $user)
    {
        if (!$request->hasValidSignature())
            return redirect()->route('web.login.index')->withErrors('Houve uma falha na assinatura. Tente novamente');

        $usuario = (new UsuarioRepository())->getById($user);
        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado.']);

        if ((new UsuarioService())->recuperaSenha($usuario))
            return redirect()->route('web.login.index')->with('success', 'Um e-mail foi enviado. Siga as instruções nele contidas.');
        return redirect()->route('web.login.index')->with('error', 'Não foi possível concluir a solicitação. Tente novamente.');
    }

    /**
     * Abre formulario para insercao da senha
     *
     * @param string $token
     * @param string $email
     * @return View
     * @throws \Exception
     */
    public function reset($token, $email)
    {
        $user = (new UsuarioRepository)->encontraUsuarioByEmail($email);
        return view(config('login.view_recuperar_senha'), compact('user', 'token'));
    }

    /**
     * Atualiza senha do usuario
     *
     * @param string $token
     * @param string $email
     * @return RedirectResponse
     * @throws \Exception
     */
    public function update($token, $email)
    {
        $rule = [
            'senha' => 'required|confirmed|min:1',
        ];
        Validator::make(\request()->all(), $rule)->validate();
        $usuario = (new UsuarioRepository)->encontraUsuarioByEmail($email);
        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado.']);

        (new UsuarioService)->updatePassword($usuario, $token);

        if (config('login.use_empresa')) {
            $payload = auth()->payload();
            if ($payload && $payload['empresaId'])
                return redirect()->route(config('login.pos_login_route'));
            return redirect()->to(URL::temporarySignedRoute('web.login.empresaForm', now()->addMinutes(15), ['user' => $usuario->getKey()]));
        }
        return redirect()->route(config('login.pos_login_route'));
    }

    /** Desloga usuário
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        (new UsuarioService())->logout();
        return redirect()->route('web.login.index');
    }

    /** Desloga usuário de todos os dispositivos e aplicativos
     * @param Request $request
     * @return RedirectResponse
     */
    public function logoutAll(Request $request)
    {
        if (auth()->check() || $request->get('user')) {
            if (!auth()->check())
                if (!$request->hasValidSignature())
                    return redirect()->route('web.login.index');
            (new UsuarioService())->logoutAll($request->get('user'));
        }
        return redirect()->route('web.login.index');
    }

    /** Remove usuario do cookie
     * @param Request $request
     * @param string $email
     * @return RedirectResponse
     */
    public function forget(Request $request, $email)
    {
        (new UsuarioService())->forget($email);
        return redirect()->route('web.login.index');
    }
}
