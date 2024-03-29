<?php


namespace Agp\Login\Model\Service;


use Agp\BaseUtils\Helper\Utils;
use Agp\Log\Jobs\LogJob;
use Agp\Log\Log;
use Agp\Login\Exceptions\LoginException;
use Agp\Login\Model\Repository\UsuarioRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use stdClass;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class UsuarioService
 * @package App\Model\Service
 *
 * Contem os servicos de login, registro e autenticacao de usuario.
 */
class UsuarioService
{
    /** Executa chamada para callback da aplicacao
     * @param int $user Id do usuário logado
     * @param string $acao Indica a ação executada sendo login, login_empresa ou registro
     * @throws ValidationException
     */
    private function callbackLogin($user, $acao)
    {
        try {
            $classCallback = config('login.user_login_callback');
            if (class_exists($classCallback)) {
                $obj = new $classCallback();
                if (method_exists($obj, 'loginCallback'))
                    $obj->loginCallback($acao, $user);
            }
        } catch (Exception $exception) {

        }
    }

    /** Verifica se retorno do login é válido
     * @param object $data Body de resposta da API
     * @param bool $registro Indica se é um registro de usuario
     * @throws ValidationException
     */
    private function validaTokenApi($data, $acao)
    {
        if ($data && $data->auth && $data->auth->token && $data->auth->token->access_token) {
            $aux = @auth()->setToken($data->auth->token->access_token);
            try {
                //Verifica possível erro de vinculo em banco de dados
                $payload = $aux->payload();
                $user = $payload['sub'];
                if ($user) {
                    if ($acao == 'registro')
                        $this->callbackLogin($user, 'registro');
                    $usuario = (new UsuarioRepository())->getById($user);
                    if (!$usuario) {
                        LogJob::dispatch(new Log(6, 'O usuário id ' . $user . ' do token não foi encontrado.'));
                        throw ValidationException::withMessages(['message' => 'O usuário não possui permissão de acesso neste sistema.']);
                    }
                }
                if (!auth()->check()) {
                    LogJob::dispatch(new Log(6, 'O servidor retornou um token válido mas sistema não pôde autenticar.'));
                    throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
                }
                $this->callbackLogin($user, 'login');
                // Comentado por conta da API
                //request()->session()->put('token', auth()->getToken()->get());
                if (config('login.use_empresa')) {
                    if (!is_array($data->auth->empresa) || (count($data->auth->empresa) <= 0)) {
                        LogJob::dispatch(new Log(6, 'O servidor não retornou uma listagem de empresas para login.'));
                        throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
                    }
                    // Comentado por conta da API
                    //request()->session()->put('empresas', $data->auth->empresa);
                    if (count($data->auth->empresa) == 1) {
                        if (!is_numeric($payload['empresaId'])) {
                            LogJob::dispatch(new Log(6, 'O servidor não retornou o ID da empresa no token.'));
                            throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
                        }
                        $this->callbackLogin($user, 'login_empresa');
                    }
                }
            } catch (Exception $exception) {
                Log::handleException($exception);
                throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
            }
        } else {
            LogJob::dispatch(new Log(6, 'O servidor retornou 200/201 mas dados em body são desconhecidos.'));
            throw ValidationException::withMessages(['message' => 'Ops. O servidor de autenticação está com probleminhas.']);
        }
    }

    /** Verifica se retorno do login é válido
     * @param object $data Body de resposta da API
     * @throws ValidationException
     */
    public function updateTokenSession($data)
    {
        try {
            request()->session()->put('token', auth()->getToken()->get());
            if (config('login.use_empresa'))
                request()->session()->put('empresas', $data->auth->empresa);

        } catch (Exception $exception) {
            Log::handleException($exception);
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
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/login';
        $data = [
            'app' => config('login.id_app'),
            'usuarioDispositivo' => $this->getUsuarioDispositivoCookie($usuario),
            'email' => $usuario->email,
            'password' => $senha,
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
            $this->validaTokenApi($res->object(), 'login');
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }
        $data = $res->json();
        if (array_key_exists('data',$data) && array_key_exists('usuarioDispositivo', $data['data']))
            $this->salvaCookieDevice($data['data']['usuarioDispositivo']);

        return $res->object();
    }

    /** Realiza login via API
     * @param string $email
     * @param string $doc
     * @param string $senha
     * @throws ValidationException
     */
    public function loginDirectApi($email, $doc, $senha)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/login';
        $data = [
            'app' => config('login.id_app'),
            'email' => $email,
            'doc' => $doc,
            'password' => $senha,
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
            $this->validaTokenApi($res->object(), 'login');
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }
        $data = $res->json();
        if (array_key_exists('data', $data) && array_key_exists('usuarioDispositivo', $data['data']))
            $this->salvaCookieDevice($data['data']['usuarioDispositivo']);

        return $res->json();
    }

    /** Realiza consulta do usuario via API
     * @param string $login
     * @throws ValidationException
     */
    public function findApi($login)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/find';
        $data = [
            'app' => config('login.id_app'),
            'login' => $login,
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];

        if(config('login.search_serpro')){
            $data['search_serpro'] = config('login.search_serpro');
        }

        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];

        $res = Http::withHeaders($headers)->post($url, $data);

        if (($res->status() != 200) and ($res->status() != 201)) {
            throw new LoginException($res->object(), $res->status());
        }

        return $res->object();
    }

    /** Realiza login na empresa via API
     * @param int $empresa
     * @throws ValidationException
     */
    public function loginEmpresaApi($empresa)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/loginEmpresa';
        $data = [
            'app' => config('login.id_app'),
            'empresa' => $empresa,
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . auth()->getToken()->get(),
        ];
        $res = Http::withHeaders($headers)->post($url, $data);
        if (($res->status() == 200) || ($res->status() == 201)) {
            $this->validaTokenApi($res->object(), 'login_empresa');
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }

        return $res->object();
    }

    /** Realiza login
     * @param Model|object $usuario
     * @param string $senha
     * @throws ValidationException
     */
    public function login($usuario, $senha)
    {
        if (config('login.base') == 'api') {
            return $this->loginApi($usuario, $senha);
        } else
            throw new Exception('Método login não implementado para login_base=entity');
        //TODO Fazer login via Model

    }

    /** Realiza a consulta do usuario
     * @param string $login\
     * @throws ValidationException
     */
    public function find($login)
    {
        if (config('login.base') == 'api') {
            return $this->findApi($login);
        } else
            throw new Exception('Método find não implementado para login_base=entity');
        //TODO Fazer login via Model

    }

    /**
     * Realiza login na empresa selecionada
     * @param int $empresa ID da empresa
     * @throws ValidationException
     */
    public function loginEmpresa($empresa)
    {
        if (config('login.base') == 'api') {
            return $this->loginEmpresaApi($empresa);
        } else
            throw new Exception('Método loginEmpresa não implementado para login_base=entity');
        //TODO Fazer login via Model
    }

    /** Realiza requisicao de recuperacao de senha via API
     * @param Model|object $usuario
     * @return bool
     * @throws Exception
     */
    public function recuperaSenhaApi($usuario)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/password/recover';
        $data = [
            'app' => config('login.id_app'),
            'email' => $usuario->email,
            'recover_route' => config('login.recover_route'),
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
            return true;
        } else {
            return false;
        }
    }

    /** Realiza requisicao de recuperacao de senha
     * @param Model|object $usuario
     * @return bool
     * @throws Exception
     */
    public function recuperaSenha($usuario)
    {
        if (config('login.base') == 'api')
            return $this->recuperaSenhaApi($usuario);
        else
            throw new Exception('Método recuperaSenha não implementado para login_base=entity');
        //TODO Fazer login via Model
    }

    /**
     * Realiza requisicao de atualizacao de senha via API
     *
     * @param Model|object $usuario
     * @return bool
     * @throws Exception
     */
    public function updatePasswordApi($usuario, $token)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/password/reset/' . $token . '/' . $usuario->email . '';
        $data = [
            'app' => config('login.id_app'),
            'senha' => base64_encode(request()->get('senha')),
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $res = Http::withHeaders($headers)->put($url, $data);
        if (($res->status() == 200) || ($res->status() == 201)) {
            $this->validaTokenApi($res->object(), 'login');
            return $res->object();
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }
    }

    /**
     * Atualiza senha do usuário
     *
     * @param Model|object $usuario
     * @param string $token
     * @return bool
     * @throws Exception
     */
    public function updatePassword($usuario, $token)
    {
        if (config('login.base') == 'api')
            return $this->updatePasswordApi($usuario, $token);
        else
            throw new Exception('Método updatePassowrd não implementado para login_base=entity');
        //TODO Fazer login via Model
    }

    /**
     * Realiza registro de novo usuario
     * @param array $data Dados do formulario
     * @throws ValidationException
     */
    public function registrar($data)
    {
        if (config('login.base') == 'api') {
            return $this->registrarApi($data);
        } else
            throw new Exception('Método registrar não implementado para login_base=entity');
        //TODO Fazer login via Model
    }

    /**
     * Realiza registro de novo usuario via API
     * @param array $dados Dados do formulario
     * @throws ValidationException
     */
    public function registrarApi($dados)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
        $url = $url . '/registrar';
        $data = [
            'app' => config('login.id_app'),
            'nome' => $dados['nome'],
            'email' => $dados['e-mail'],
            'tipo_doc' => '1',
            'doc' => $dados['cpf'],
            'usuario' => [
                'email' => $dados['e-mail'],
                'senha' => $dados['usuario']['senha'],
            ],
            'client' => [
                'user_agent' => Utils::getUserAgent(),
                'ip' => Utils::getIpRequest(),
            ]
        ];
        if (config('login.use_empresa')) {
            if (array_key_exists('empresa', $dados)) {
                $data['empresa'] = $dados['empresa'];
            } else {
                $data['empresa'] = [
                    'nome' => $data['nome'],
                    'cpf_cnpj' => $data['doc'],
                ];
            }
        }
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $res = Http::withHeaders($headers)->post($url, $data);
        if (($res->status() == 200) || ($res->status() == 201)) {
            $this->validaTokenApi($res->object(), 'registro');
        } else {
            $data = $res->json();
            if ($data && array_key_exists('errors', $data))
                throw ValidationException::withMessages($data['errors']);
            throw ValidationException::withMessages(['message' => $data['message']]);
        }

        return $res->object();
    }

    /**
     * Realiza logout desabilitando token
     */
    public function logout()
    {
        try {
            $payload = auth()->payload();
            auth()->logout();
            $email = auth()->user()->email;
            $empresa = $payload['empresaId'] ?? 0;
            $this->removeAccountSessao($email, $empresa);
        } catch (Exception $exception) {
        }
    }

    /**
     * Realiza logout desabilitando token de todos os dispositivos
     * @param int $userId ID do usuario a ser deslogado
     */
    public function logoutAll($userId)
    {
        DB::connection('mysql-session')
            ->delete('DELETE FROM log_sessao WHERE user_id = ' . (auth()->check() ? auth()->user()->getKey() : $userId));
        if (auth()->check()) {
            $email = auth()->user()->email;
            auth()->logout();
            $this->removeAccountSessao($email, false);
        } else {
            $this->removeAccountSessao($userId, false);
        }
    }

    /**
     * Remove conta do cookie
     */
    public function forget($email)
    {
        $this->removeAccountCookie($email);
    }

    /**
     * Encontra o usuário pelo CPF informado
     * @param string $data CPF ou e-mail para consulta
     * @return object|null
     * @throws Exception
     */
    private function encontraUsuarioApi($data)
    {
        $url = config('login.api');
        if ($url == '')
            throw new Exception('Parametro "login.api" não informado.');
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
     * @return object|Model|null
     * @throws Exception
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
     * @return object|Model|null
     * @throws Exception
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
        $this->updateCookie();
        return $pos;
    }

    /** Atualiza sessão do usuario com os dados da nova conexao
     * @param string $email Email do usuario logado
     * @param object $empresa Entidade Empresa com atributos id e nome
     * @return int
     */
    private function updateSession($email, $empresa)
    {
        $data = [];
        if (config('login.use_conta_id'))
            $data = $this->getContas();

        $pos = count($data);
        foreach ($data as $i => $conta) {
            if ($conta->email == $email)
                if (config('login.use_empresa') && ($conta->empresaId == $empresa->id)) {
                    $pos = $i;
                    break;
                }
        }
        //TODO Transformar em classe serializavel
        $data[$pos] = new stdClass;
        $data[$pos]->id = auth()->user()->getKey();
        $data[$pos]->nome = auth()->user()->nome;
        $data[$pos]->email = $email;
        $data[$pos]->empresaId = $empresa->id ?? 0;
        $data[$pos]->empresa = $empresa->nome ?? '';
        $data[$pos]->token = auth()->getToken()->get();
        $data[$pos]->contaId = $pos;
        request()->session()->put(config('login.session_data'), json_encode($data));
        return $pos;
    }

    /**
     * Adiciona a conta conectada ao cookie do cliente
     */
    private function updateCookie()
    {
        $data = @json_decode(request()->cookie(config('login.accounts_cookie')));
        if (!is_array($data))
            $data = array();

        $pos = count($data);
        foreach ($data as $i => $conta) {
            if ($conta->email == auth()->user()->email) {
                $pos = $i;
                break;
            }
        }
        $data[$pos] = new stdClass;
        $data[$pos]->id = auth()->user()->getKey();
        $data[$pos]->email = auth()->user()->email;
        $data[$pos]->nome = auth()->user()->nome . ' ' . auth()->user()->sobrenome;
        Cookie::queue(Cookie::make(config('login.accounts_cookie'), json_encode($data), 0));
    }

    /** Atualiza todas as contas
     * @param int $userId
     * @param int|false $empresaId Id da empresa, 0 ou false para remover todos as contas do dado e-mail
     * @return array
     */
    public function removeAccountSessao($userId, $empresaId = 0)
    {
        $data = $this->getContas();
        $newData = array();
        foreach ($data as $conta) {
            if ($conta->id != $userId)
                continue;
            if ($empresaId === false)
                continue;
            if ($conta->empresaId != $empresaId)
                continue;
            $newData[] = $conta;
        }
        request()->session()->put(config('login.session_data'), json_encode($newData));
        return $newData;
    }

    /** Atualiza todas as contas
     * @param string $email
     * @return array
     */
    public function removeAccountCookie($email)
    {
        $data = @json_decode(request()->cookie(config('login.accounts_cookie')));
        $newData = array();
        if ($data == true) {
            foreach ($data as $conta) {
                if ($conta->email != $email) {
                    $newData[] = $conta;
                    continue;
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
        $data = json_decode(request()->cookie(config('login.accounts_cookie')));
        if (!is_array($data))
            return [];
        return $data;
    }

    /**
     * Retorna dados da conta salva na sessão
     * @return array
     */
    public function getContas()
    {
        $data = json_decode(request()->session()->get(config('login.session_data')));
        if (!is_array($data))
            return [];
        $data = $this->validaContasSessao($data);
        foreach ($data as $i => $conta)
            $data[$i]->conectado = $this->validaContaConectada($conta);
        return $data;
    }

    /**
     * Valida se dados da conta da sessao estao corretos
     * @param array $data Array de contas retornadas da sessao
     * @return array
     */
    private function validaContasSessao($data)
    {
        $res = array();
        foreach ($data as $i => $conta) {
            if (!isset($conta->id))
                continue;
            if (!isset($conta->nome))
                continue;
            if (!isset($conta->email))
                continue;
            if (!isset($conta->empresaId))
                continue;
            if (!isset($conta->empresa))
                continue;
            if (!isset($conta->token))
                continue;
            if (!isset($conta->contaId))
                continue;
            $res[] = $conta;
        }
        return $res;
    }

    /**
     * Retorna a conta atual do usuario
     * @return array|null
     */
    public function getContaAtual()
    {
        if (!auth()->check())
            return null;
        $data = $this->getContas();
        $email = auth()->user()->email;
        $payload = auth()->payload();
        $empresa = $payload['empresaId'] ?? 0;
        foreach ($data as $conta) {
            if (($conta->email == $email) && ($conta->empresaId == $empresa))
                return $conta;
        }
        return null;
    }

    /**
     * Verifica se conta do usuario está com token ativo e válido
     *
     * @param $conta
     * @return bool
     */
    public function validaContaConectada($conta)
    {
        try {
            JWTAuth::setToken($conta->token ?? '')->payload();
            return true;
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) { //general JWT exception
            return false;
        }
    }

    /**
     * Salva dados da requisição para redirecionar usuário no momento de re-login
     *
     * @param Request $request Dados da requisição
     * @param int $contaId Id da conta
     */
    public function salvaDadosUrl(Request $request, $contaId = 0)
    {
        $goto = array();
        if (($request->method() == 'POST') || ($request->method() == 'PUT') || ($request->method() == 'PATCH') || ($request->method() == 'DELETE'))
            $goto['url'] = URL::previous();
        else
            $goto['url'] = config('app.url') . $request->getRequestUri();
        $goto['input'] = $request->input();
        $goto['contaId'] = $contaId;
        request()->session()->put('goto', json_encode($goto));
    }

    /**
     * @param object $usuario
     * @return array
     * @throws Exception
     */
    private function getUsuarioDispositivoCookie($usuario)
    {
        if (($usuario->id ?? null) != null) {
            $adm_pessoa_id = $usuario->id;
        } elseif (($usuario->adm_pessoa_id ?? null) != null) {
            $adm_pessoa_id = $usuario->adm_pessoa_id;
        } elseif (($usuario->email ?? null) != null) {
            $usuario = $this->encontraUsuarioByEmail($usuario->email);
            $adm_pessoa_id = $usuario->adm_pessoa_id;
        } else
            return [];

        $data = @json_decode(request()->cookie(config('login.device_cookie')),true);
        if (!is_array($data))
            return null;
        if (!isset($data[$adm_pessoa_id]))
            return null;
        $data = $data[$adm_pessoa_id];
        if (!array_key_exists('id',$data))
            return null;
        if (!array_key_exists('adm_pessoa_id',$data))
            return null;
        if (!array_key_exists('adm_aplicativo_id',$data))
            return null;
        $data = (object)$data;
        if ($data->adm_pessoa_id != $adm_pessoa_id)
            return [];
        return [
            'id' => $data->id,
        ];
    }

    /**
     * Cria cookie com o ID do dispositivo e ID do usuario
     *
     * @param object $usuarioDispositivo
     */
    private function salvaCookieDevice($usuarioDispositivo)
    {
        $data = @json_decode(request()->cookie(config('login.device_cookie')), true);
        if (!is_array($data))
            $data = array();
        $data[$usuarioDispositivo['adm_pessoa_id']] = $usuarioDispositivo;
        Cookie::queue(Cookie::make(config('login.device_cookie'), json_encode($data), 0));
    }

    /**
     * Retorna os dados do dispositivoUsuario do usuário logado salvo no cookie
     *
     * @return array|null
     */
    public static function getDispositivoCookie($userId = null)
    {
        if (!auth()->check() && !$userId)
            return null;
        if (!$userId)
            $userId = auth()->user()->getKey();
        $data = @json_decode(request()->cookie(config('login.device_cookie')), true);
        if (!is_array($data))
            return null;
        if (!isset($data[$userId]))
            return null;
        $data = $data[$userId];
        if (!array_key_exists('id', $data))
            return null;
        if (!array_key_exists('adm_pessoa_id', $data))
            return null;
        if (!array_key_exists('adm_aplicativo_id', $data))
            return null;
        if ($data['adm_pessoa_id'] != $userId)
            return null;
        return $data;
    }
}
