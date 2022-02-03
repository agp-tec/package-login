<?php

namespace Agp\Login\Controller\Api;


use Agp\Login\Controller\Controller;
use Agp\Login\Model\Repository\UsuarioRepository;
use Agp\Login\Model\Resource\UsuarioResource;
use Agp\Login\Model\Service\UsuarioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /** Encontra usuário através do dado e-mail ou CPF
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function find(Request $request)
    {
        $rule = ['login' => 'required'];
        Validator::make($request->all(), $rule)->validate();

        return (new UsuarioService)->find($request->get('login'));
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
        Validator::make($request->all(), ['password' => 'required|string'])->validate();

        $usuario = (new UsuarioRepository())->getById($user);
        return (new UsuarioService)->login($usuario, $request->get('password'));
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

        return (new UsuarioService())->registrar($request->all());
    }

    /** Seleciona a empresa
     * @param Request $request
     * @param int $user ID do usuário
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function empresa(Request $request, $user)
    {
        if (auth()->user()->getKey() != $user) {
            (new UsuarioService())->logout();
            throw ValidationException::withMessages(['message' => 'Houve uma falha na assinatura. Tente novamente']);
        }

        $rule = [
            'empresa' => 'required|numeric',
        ];
        Validator::make($request->all(), $rule)->validate();

        return (new UsuarioService())->loginEmpresa($request->get('empresa'));
    }

    /** Envia e-mail de recuperação de senha
     * @param Request $request
     * @param int $user ID do usuário
     * @return RedirectResponse
     * @throws \Exception
     */
    public function recover(Request $request, $user)
    {
        $usuario = (new UsuarioRepository())->getById($user);
        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado.']);

        (new UsuarioService())->recuperaSenha($usuario);
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
            'senha' => 'required|min:1',
        ];
        Validator::make(\request()->all(), $rule)->validate();
        $usuario = (new UsuarioRepository)->encontraUsuarioByEmail($email);
        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário não encontrado.']);

        (new UsuarioService)->updatePassword($usuario, $token);
    }

    /** Remove usuario do cookie
     * @param Request $request
     * @param string $email
     * @return RedirectResponse
     */
    public function forget(Request $request, $email)
    {
        (new UsuarioService())->forget($email);
    }

    public function user()
    {
        return new UsuarioResource(auth()->user(), true);
    }
}
