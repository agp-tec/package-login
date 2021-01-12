<?php


namespace Agp\Login\Model\Service;


use Agp\Login\Utils\LoginResponseUtils;
use App\Mail\LoginFailMail;
use App\Mail\LoginMail;
use App\Model\Entity\Aplicativo;
use App\Utils\ApiCodeUtils;
use Facades\App\Model\Repository\UsuarioRepository;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
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

    public static function getContaAtual()
    {
        $data = request()->session()->get('data');
        $data = json_decode($data);
        foreach ($data as $conta) {
            if (Auth::user()->getAdmEmpresaId() == $conta->e && Auth::user()->email == $conta->mail)
                return $conta;
        }
        return null;
    }

    /** Realiza login via API. Empresas do usuário é retornado em LoginResponseUtils->getData().
     * @param $email
     * @param $senha
     * @return LoginResponseUtils
     */
    public function login($email, $senha, $empresa)
    {
        $data = [
            'app' => config('config.id_app'),
            'email' => $email,
            'password' => base64_encode($senha),
            'empresa' => $empresa,
            'client' => [
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]
        ];
        $body = json_encode($data);

        return $this->makeRequest('POST', config('config.api_login'), $body);
    }

    /**
     * Realiza conexão à API
     *
     * @param $method
     * @param $url
     * @param $body
     * @param string $token
     * @return LoginResponseUtils
     */
    private function makeRequest($method, $url, $body, $token = '')
    {
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
        ];
        if ($token)
            $headers['Authorization'] = 'bearer ' . $token;

        $request = new \GuzzleHttp\Psr7\Request($method, $url, $headers, $body);
        $client = new Client([
            'verify' => true,
            'http_errors' => false
        ]);

        $ret = $client->send($request);
        $body = $ret->getBody()->getContents();
        $apiRet = @json_decode($body);
        $response = new LoginResponseUtils($ret->getStatusCode(), $apiRet);

        if (($response->getHttpReturnCode() == 200) || ($response->getHttpReturnCode() == 201)) {
            @auth()->setToken($response->getToken());
            //dd(@auth()->setToken($response->getToken())->payload()); //Utiliza para disparar exception e debutar o pq retornou ok mas com token invalido
        }
        return $response;
    }

    public function loginEmpresa($empresa)
    {
        $data = [
            'app' => config('config.id_app'),
            'empresa' => $empresa,
            'client' => [
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]
        ];
        $body = json_encode($data);

        return $this->makeRequest('POST', config('config.api_login_empresa'), $body, auth()->getToken()->get());
    }

    /** Realiza registro de novo usuário via api. Cria uma empresa vinculada ao usuário. Retorna a empresa(s) em LoginResponseUtils->getData().
     * @param $nome
     * @param $email
     * @param $password
     * @param $cpf
     * @return LoginResponseUtils
     */
    public function registrar($nome, $email, $password, $cpf_cnpj)
    {
        $data = [
            'app' => config('config.id_app'),
            'nome' => $nome,
            'usuario' => [
                'email' => $email,
                'senha' => $password,
            ],
            'empresa' => [
                'nome' => $nome,
                'cpf_cnpj' => $cpf_cnpj,
            ],
            'client' => [
                'user_agent' => request()->userAgent(),
                'ip' => request()->ip(),
            ]
        ];
        $body = json_encode($data);

        return $this->makeRequest('POST', config('config.api_registrar'), $body);
    }

    public function logoutWeb()
    {
        //TODO Invalidar token da conta atual
        //TODO Fazer funcionalidade para sair de todos os dispositivos
    }

    /** Invalida todos os tokens das contas conectadas e invalida a sessão.
     * @param boolean $allDevices
     */
    public function logoutForgetWeb($allDevices = false)
    {
        //TODO Percorrer tokens jwt das contas vinculadas e invalidar todas
        if ($allDevices)
            DB::connection('mysql-session')
                ->delete('DELETE FROM log_sessao WHERE user_id = ' . auth()->user()->getKey());
        request()->session()->invalidate();
    }

    /** Adiciona a conta na sessão do usuario e registra log e envia e-mail de notificação de novo login realizado via WEB
     * @param string $token Token da autenticacao
     * @param object $empresa Entidade Empresa
     * @return int
     */
    public function novoLoginWeb($token, $empresa)
    {
        $email = auth()->user()->email;
        $pos = $this->updateSession($email, $empresa);
        $this->updateCookie($email, $empresa);
        return $pos;
    }

    /** Atualiza sessão do usuario com os dados da nova conexao
     * @param string $email Email do usuario logado
     * @param object $empresa Entidade Empresa
     * @return int
     */
    private function updateSession($email, $empresa)
    {
        $data = request()->session()->get('data');
        if (($data == '') || ($data == false) || ($data == null)) {
            $data = array();
            $pos = 0;
        } else {
            $data = json_decode($data);
            $pos = count($data);
            foreach ($data as $i => $conta) {
                if ($conta->mail == $email)
                    if ($conta->e == $empresa->id) {
                        $pos = $i;
                        break;
                    }
            }
        }
        $data[$pos] = new stdClass;
        $data[$pos]->mail = $email;
        $data[$pos]->e = $empresa->id;
        $data[$pos]->en = $empresa->nome;
        $data[$pos]->t = auth()->getToken()->get();
        $data[$pos]->exp = auth()->payload()->get('exp');
        $data[$pos]->i = $pos;
        request()->session()->put('data', json_encode($data));
        return $pos;
    }

    /** Adiciona a conta conectada ao cookie do cliente
     * @param string $email Email do usuario logado
     * @param object $empresa Entidade Empresa
     */
    private function updateCookie($email, $empresa)
    {
        $data = @json_decode(request()->cookie('accounts'));
        if (($data == '') || ($data == false) || ($data == null)) {
            $data = array();
            $pos = 0;
        } else {
            $pos = count($data);
            foreach ($data as $i => $conta) {
                if ($conta->email == $email)
                    if ($conta->empresaId == $empresa->id) {
                        $pos = $i;
                        break;
                    }
            }
        }
        $data[$pos] = new stdClass;
        $data[$pos]->email = $email;
        $data[$pos]->nome = auth()->user()->nome . ' ' . auth()->user()->sobrenome;
        $data[$pos]->empresaId = $empresa->id;
        $data[$pos]->empresa = $empresa->nome;
        Cookie::queue(Cookie::make('accounts', json_encode($data), 0));
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

        return $data;
    }

    public static function getContas()
    {
        $accounts = array();
        $data = request()->session()->get(config('login.session_data'));
        if ($data == '')
            return $accounts;
        //TODO Recuperar status do token jwt para mostrar conta Conectada ou Desconectada
        $data = json_decode($data);
        foreach ($data as $conta) {
            //TODO Mostrar data de expiração ou status do token
            //TODO Por enquanto data de expiracao eh salva fora do token, nao recupera a data atualizada do token
//            $conta->s = date_create()->setTimestamp($conta->exp)->format('h:i:s');
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

    /** Atualiza todas as contas
     * @param Array $contas
     */
    public function removeAccountCookie($email, $empresa)
    {
        $data = @json_decode(request()->cookie('accounts'));
        $newData = array();
        if ($data == true) {
            foreach ($data as $conta) {
                if (($conta->email != $email) || ($conta->empresaId != $empresa)) {
                    $newData[] = $conta;
                }
            }
        }
        Cookie::queue(Cookie::make('accounts', json_encode($newData), 0));
        return $newData;
    }
}
