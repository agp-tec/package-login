<?php


namespace Agp\Login\Utils;


use Agp\Log\Jobs\LogJob;
use Agp\Log\Log;
use App\Model\Entity\Usuario;
use Facades\App\Model\Service\UsuarioService;

class LoginResponseUtils
{
    /** Código de retorno
     * @var Integer
     */
    private $httpReturnCode;
    /** Contem os dados da resposta da autenticacao (token e empresa(s))
     * @var object
     */
    private $auth;
    /** Contém os erros retornados pela api
     * @var
     */
    private $errors;
    /** Mensagem de resposta
     * @var string
     */
    private $message;
    /** Contém dados adicionais de retorno para API como array de empresas
     * @var object
     */
    private $data;

    /**
     * LoginResponseUtils constructor.
     * @param $httpReturnCode Integer Código de retorno da API
     * @param object $jsonResponse Mensagem recebida da api
     */
    public function __construct($httpReturnCode, $jsonResponse)
    {
        $this->httpReturnCode = $httpReturnCode;
        $this->message = $jsonResponse->message;
        $this->errors = isset($jsonResponse->errors) ? $jsonResponse->errors : null;
        if (($this->httpReturnCode == HttpCodeUtils::HTTP_OK) || ($this->httpReturnCode == HttpCodeUtils::HTTP_CREATED)) {
            $this->auth = $jsonResponse->auth;
            $this->data = $jsonResponse->data;
        }
    }

    public static function newInstance($httpReturnCode, $jsonResponse)
    {
        return new LoginResponseUtils($httpReturnCode, $jsonResponse);
    }

    /** Retorna os erros retornados pela api
     * @return mixed
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return int
     */
    public function getHttpReturnCode(): int
    {
        return $this->httpReturnCode;
    }

    /**
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }

    /** Retorna a rota para a reposta de login recebida.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function getResponseWeb()
    {
        if (auth()->check()) {
            if (auth()->user()->getAdmEmpresaId()) {
                $idConta = UsuarioService::novoLoginWeb(auth()->getToken()->get(), $this->auth->empresa[0]);

                $goto = request()->session()->get('goto');
//                $gotodata = request()->session()->get('gotodata');
                if ($goto) {
                    request()->session()->pull('goto');
//                    request()->session()->pull('gotodata');
                    return redirect($goto);//->withInput($gotodata);;
                }
                return redirect()->route('web.home', ['contaId' => $idConta]);
            } else {
                request()->session()->put('token', auth()->getToken()->get());
                request()->session()->put('empresas', $this->auth->empresa);
                return redirect()->route('web.login.empresaList');
            }
        } else {
            if (($this->httpReturnCode == HttpCodeUtils::HTTP_OK) || ($this->httpReturnCode == HttpCodeUtils::HTTP_CREATED)) {
                // Invalid value provided for claim [iat] - Hora do servidor
                $this->message = 'O servidor retornou um token inválido.';
                try {
                    //Verifica possível erro de vinculo em banco de dados
                    $data = @auth()->payload();
                    $user = $data['sub'];
                    if ($user) {
                        $usuario = Usuario::query()->where(['id' => $user])->get()->first();
                        if (!$usuario) {
                            LogJob::dispatch(new Log(4, 'O usuário id ' . $user . ' do token não foi encontrado.'));
                            $this->message = 'O usuário não possui permissão aos dados desse sistema.';
                        }
                    }
                } catch (\Exception $exception) {
                    LogJob::dispatch(new Log(4, 'O servidor retornou um token inválido'));
                    $this->message = 'O servidor retornou um token inválido.';
                }

            }
            return redirect()->back()->withErrors(($this->errors != null) ? $this->errors : $this->message)->withInput(request()->input());
        }
    }

    /** Retorna o json de autenticacao (tokem e empresas)
     * @return object
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /** Retorna token da resposta
     * @return string
     */
    public function getToken()
    {
        if ($this->auth)
            if ($this->auth->token)
                return $this->auth->token->access_token;
        return '';
    }
}
