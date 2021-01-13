<?php


namespace Agp\Login\Model\Service;


use Agp\BaseUtils\Helper\Utils;
use Agp\Log\Jobs\LogJob;
use Agp\Log\Log;
use Agp\Login\Model\Repository\UsuarioRepository;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use stdClass;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class UsuarioService
 * @package App\Model\Service
 *
 * Contem os servicos de usuario.
 * Json salvo em sessao:
 * N {
 *      'mail': 'email do usuario',
 *      'e': 'id da empresa logada',
 *      't': 'token de autenticacao',
 *  }, ...
 *
 */
class UsuarioService
{

    /** Verifica se retorno do login é válido
     * @param object $data Body de resposta da API
     * @throws ValidationException
     */
    private function validaTokenApi($data)
    {
        dump($data);
        if ($data && $data->auth && $data->auth->token && $data->auth->token->access_token) {
            $aux = @auth()->setToken($data->auth->token->access_token);
            try {
                //Verifica possível erro de vinculo em banco de dados
                $payload = $aux->payload();
                dump($payload);
                $user = $payload['sub'];
                if ($user) {
                    $usuarioEntity = config('login.user_entity');
                    $usuario = $usuarioEntity::query()->where(['id' => $user])->get()->first();
                    if (!$usuario) {
                        LogJob::dispatch(new Log(4, 'O usuário id ' . $user . ' do token não foi encontrado.'));
                        throw ValidationException::withMessages(['message' => 'O usuário não possui permissão de acesso neste sistema.']);
                    }
                }
                if (!auth()->check()) {
                    LogJob::dispatch(new Log(4, 'O servidor retornou um token válido mas sistema não pôde autenticar.'));
                    throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
                }
                if (config('login.use_empresa')) {
                    if (!is_array($data->auth->empresa) || (count($data->auth->empresa) <= 0)) {
                        LogJob::dispatch(new Log(4, 'O servidor não retornou uma listagem de empresas para login.'));
                        throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
                    }
                    if (count($data->auth->empresa) > 1) {
                        request()->session()->put('token', auth()->getToken()->get());
                        request()->session()->put('empresas', $data->auth->empresa);
                    } else {
                        if (!is_numeric($payload['empresaId'])) {
                            LogJob::dispatch(new Log(4, 'O servidor não retornou o ID da empresa no token.'));
                            throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
                        }
                    }
                }
            } catch (\Exception $exception) {
                Log::handleException($exception);
                throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
            }
        } else {
            LogJob::dispatch(new Log(4, 'O servidor retornou 200/201 mas dados em body são desconhecidos.'));
            throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
        }
    }

    /** Realiza login via API
     * @param Model|object $usuario
     * @param string $senha
     * @throws ValidationException
     */
    public function loginApi($usuario, $senha)
    {
        $url = config('login.api');
        if ($url == '')
            throw new \Exception('Parametro "login.api" não informado.');
        $url = $url . '/login';
        $data = [
            'app' => config('login.id_app'),
            'email' => $usuario->email,
            'password' => base64_encode($senha),
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $res = Http::withHeaders($headers)->post($url, $data);
        if (($res->status() == 200) || ($res->status() == 201)) {
            $this->validaTokenApi($res->object());
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }
    }

    /** Realiza login na empresa via API
     * @param int $empresa
     * @throws ValidationException
     */
    public function loginEmpresaApi($empresa)
    {
        $url = config('login.api');
        if ($url == '')
            throw new \Exception('Parametro "login.api" não informado.');
        $url = $url . '/loginEmpresa';
        $data = [
            'app' => config('login.id_app'),
            'empresa' => $empresa,
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];
        $body = json_encode($data);
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . auth()->getToken()->get(),
        ];
        $res = Http::withHeaders($headers)->post($url, $data);
        if (($res->status() == 200) || ($res->status() == 201)) {
            $this->validaTokenApi($res->object());
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }
    }

    /** Realiza login
     * @param Model|object $user
     * @param string $senha
     * @throws ValidationException
     */
    public function login($user, $senha)
    {
        $isCpf = is_numeric($user);
        if ($isCpf)
            $usuario = (new UsuarioService)->encontraUsuarioByCpf($user);
        else
            $usuario = (new UsuarioService)->encontraUsuarioByEmail($user);

        if (!$usuario)
            throw ValidationException::withMessages(['message' => 'Usuário "' . $user . '" não encontrado.']);

        if (config('login.base') == 'api') {
            $this->loginApi($usuario, $senha);
        }
        //TODO Fazer login via Model

    }

    /**
     * Realiza login na empresa selecionada
     * @param int $empresa ID da empresa
     */
    public function loginEmpresa($empresa)
    {
        if (config('login.base') == 'api') {
            $this->loginEmpresaApi($empresa);
        }
        //TODO Fazer login via Model
    }

    /**
     * Realiza registro de novo usuario
     * @param array $data Dados do formulario
     */
    public function registrar($data)
    {
        $url = config('login.api');
        if ($url == '')
            throw new \Exception('Parametro "login.api" não informado.');
        $url = $url . '/registrar';
        $data = [
            'app' => config('config.id_app'),
            'nome' => $data['nome'],
            'usuario' => [
                'email' => $data['usuario']['email'],
                'senha' => $data['usuario']['password'],
            ],
            'empresa' => [
                'nome' => $data['nome'],
                'cpf_cnpj' => $data['cpf_cnpj'],
            ],
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $res = Http::withHeaders($headers)->post($url, $data);
        if (($res->status() == 200) || ($res->status() == 201)) {
            $this->validaTokenApi($res->object());
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }
    }

    /**
     * Realiza logout desabilitando token
     * @param int $empresa ID da empresa
     */
    public function logout()
    {
        //try {
        auth()->parseToken();
        //} catch (\Exception $exception) {

        //}
        //if (auth()->check()) {
        auth()->logout();
        //}
    }

    /**
     * Remove conta do cookie
     */
    public function forget($email, $empresa = 0)
    {
        $this->removeAccountCookie($email, $empresa);
    }

    /**
     * Encontra o usuário pelo CPF informado
     * @param string $data CPF ou e-mail para consulta
     * @return object|null
     */
    private function encontraUsuarioApi($data)
    {
        $url = config('login.api');
        if ($url == '')
            throw new \Exception('Parametro "login.api" não informado.');
        $url = $url . '/find/' . $data;
        $res = Http::get($url);
        if ($res->status() != 200)
            return null;
        $data = $res->json();
        if (!is_array($data))
            return null;
        if (count($data) != 1)
            return null;
        return (object)$data[0];
    }

    /**
     * Encontra o usuário pelo CPF informado
     * @param string $cpf CPF para consulta
     * @return array|Usuario|Model|null
     */
    public function encontraUsuarioByCPF($cpf)
    {
        if (config('login.find_base') == 'api')
            return $this->encontraUsuarioApi($cpf);
        return (new UsuarioRepository)->encontraUsuarioByCPF($cpf);
    }

    /**
     * Encontra o usuário pelo email informado
     * @param string $email E-mail para consulta
     * @return array|Usuario|Model|null
     */
    public function encontraUsuarioByEmail($email)
    {
        if (config('login.find_base') == 'api')
            return $this->encontraUsuarioApi($email);
        return (new UsuarioRepository)->encontraUsuarioByEmail($email);
    }

    /** Adiciona dados da conta logada em cookie e sessao. Retorna id da conta
     * @param int $empresaId
     * @param string $empresaNome
     * @return int
     */
    public function novoLoginWeb($empresaId = 0, $empresaNome = '')
    {
        $email = auth()->user()->email;
        $empresa = new stdClass();
        $empresa->id = $empresaId;
        $empresa->nome = $empresaNome;
        $pos = $this->updateSession($email, $empresa);
        $cookie = $this->updateCookie($email, $empresa);
        return [
            'pos' => $pos,
            'cookie' => $cookie,
        ];
    }

    /** Atualiza sessão do usuario com os dados da nova conexao
     * @param string $email Email do usuario logado
     * @param object $empresa Entidade Empresa
     * @return int
     */
    private function updateSession($email, $empresa)
    {
        dump(config('login.session_data'));
        $data = request()->session()->get(config('login.session_data'));
        dump($data);
        if (($data == '') || ($data == false) || ($data == null)) {
            $data = array();
            $pos = 0;
        } else {
            $data = json_decode($data);
            $pos = count($data);
            foreach ($data as $i => $conta) {
                if ($conta->mail == $email)
                    if (config('login.use_empresa') && ($conta->e == $empresa->id)) {
                        $pos = $i;
                        break;
                    }
            }
        }
        $data[$pos] = new stdClass;
        $data[$pos]->mail = $email;
        $data[$pos]->e = config('login.use_empresa') ? $empresa->id : 0;
        $data[$pos]->en = config('login.use_empresa') ? $empresa->nome : '';
        $data[$pos]->t = auth()->getToken()->get();
        $data[$pos]->exp = auth()->payload()->get('exp');
        $data[$pos]->i = $pos;
        dump($data);
        request()->session()->put(config('login.session_data'), json_encode($data));
        return $pos;
    }

    /** Adiciona a conta conectada ao cookie do cliente
     * @param string $email Email do usuario logado
     * @param object $empresa Entidade Empresa
     */
    private function updateCookie($email, $empresa)
    {
        dump(config('login.accounts_cookie'));
        $data = @json_decode(request()->cookie(config('login.accounts_cookie')));
        dump($data);
        if (($data == '') || ($data == false) || ($data == null)) {
            $data = array();
            $pos = 0;
        } else {
            $pos = count($data);
            foreach ($data as $i => $conta) {
                if ($conta->email == $email)
                    if (config('login.use_empresa') && ($conta->empresaId == $empresa->id)) {
                        $pos = $i;
                        break;
                    }
            }
        }
        $data[$pos] = new stdClass;
        $data[$pos]->email = $email;
        $data[$pos]->nome = auth()->user()->nome . ' ' . auth()->user()->sobrenome;
        $data[$pos]->empresaId = config('login.use_empresa') ? $empresa->id : 0;
        $data[$pos]->empresa = config('login.use_empresa') ? $empresa->nome : '';
        dump($data);
//        Cookie::queue(Cookie::make(config('login.accounts_cookie'), json_encode($data), 0));
        return Cookie::make(config('login.accounts_cookie'), json_encode($data), 0);
    }

    /** Atualiza todas as contas
     * @param $email
     * @param $empresaId
     * @return array
     */
    public function removeAccountCookie($email, $empresaId)
    {
        $data = @json_decode(request()->cookie(config('login.accounts_cookie')));
        $newData = array();
        if ($data == true) {
            foreach ($data as $conta) {
                if ($conta->email != $email) {
                    $newData[] = $conta;
                    continue;
                }
                if (config('login.use_empresa') && ($empresaId != 0)) {
                    if (($conta->empresaId != $empresaId)) {
                        $newData[] = $conta;
                        continue;
                    }
                }
            }
        }
        Cookie::queue(Cookie::make(config('login.accounts_cookie'), json_encode($newData), 0));
        return $newData;
    }

    /** Retorna as contas salvas no navegador do cliente
     * @return array
     */
    public function getAccountsForLogin()
    {
        $data = @json_decode(request()->cookie(config('login.accounts_cookie')));
        if (($data == '') || ($data == false) || ($data == null))
            return [];
        $count = count($data);
        $contas = $this->getContas();

        for ($i = 0; $i < $count; $i++) {
            $data[$i]->conectado = false;
            foreach ($contas as $conta)
                if (($conta->mail == $data[$i]->email) && ($conta->e == $data[$i]->empresaId)) {
                    $data[$i]->conectado = $this->validaContaConectada($conta);
                    $data[$i]->contaId = $conta->i;
                    break;
                }
        }

        //Envia apenas usuarios, sem empresas, para tela de login
        $result = array();
        foreach ($data as $item)
            $result[$item->email] = $item;
        return $result;
    }

    /** Retorna dados da conta salva na sessão
     * @return array
     */
    public static function getContas()
    {
        $accounts = array();
        $data = request()->session()->get(config('login.session_data'));
        if ($data == '')
            return $accounts;
        $data = json_decode($data);
        foreach ($data as $conta) {
            $accounts[] = $conta;
        }
        return $accounts;
    }

    /**
     * Verifica se conta do usuario está com token ativo e válido
     *
     * @param $conta
     * @return bool
     */
    public static function validaContaConectada($conta)
    {
        try {
            JWTAuth::setToken($conta->t)->payload();
            return true;
        } catch (JWTException $e) { //general JWT exception
            return false;
        }
    }
}
