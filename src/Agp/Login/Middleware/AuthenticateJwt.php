<?php

namespace Agp\Login\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticateJwt
{

    public function handle(Request $request, Closure $next)
    {
        dump(Route::current()->uri());
        $data = request()->session()->get(config('login.session_data'));
        dump(config('login.session_data'));
        dump($data);
        //Rotas abertas
        if ($this->rotaAberta($request))
            return $next($request);

        //Rotas semi abertas
        if ($this->rotaSemiAberta($request)) {
            $token = request()->session()->get('token');
            if ($token) {
                $request->headers->set('Authorization', 'Bearer ' . $token);
                return $next($request);
            }
        }

        if (!is_array($data))
            die('sem data');

        if (config('login.use_conta_id')) {
            $contaId = $request->contaId;
            if ($contaId == '')
                return redirect()->route("web.home", ['contaId' => '0']);

            $data = request()->session()->get(config('login.session_data'));
            $data = @json_decode($data);
            if (($data == '') || ($data == false) || ($data == null)) {
                request()->session()->put('goto', $request->getRequestUri());
                return redirect()->route("web.login.index")->with('error', 'Sessão expirada. Acesse novamente.');
            }

            $count = count($data);
            if ($count <= 0)
                return redirect()->route("web.login.logout");
            if ($contaId >= $count)
                return redirect()->route("web.home", ['contaId' => '0']);

            $conta = $data[$contaId];
            if (!$conta)
                return redirect()->route("web.login.logout");

            try {
                $token = $data[$contaId]->t;
                $debug = config('app.debug', false);
                if (!$debug) {
                    try {
                        //Realiza a renovação do token
                        JWTAuth::setToken($token);
                        //Força expection de token expirado - Ao utilizar JWTAuth::refresh(), o sistema de autenticacao não valida a expiração do token automaticamente
                        JWTAuth::payload();
                    } catch (\Exception $e) {
                        request()->session()->put('goto', $request->getRequestUri());
//                    request()->session()->put('contaexpirada',$data[$contaId]); TODO Enviar dados da conta atual pro login, ou apenas pedir input de senha na tela
//                    request()->session()->put('gotodata',$request->input());
                        //TODO Acrescentar método da rota (POST, PUT, GET) para não perder dados de um input sendo salvo
                        return redirect()->route("web.login.index")->with('error', 'Sessão expirada. Acesse novamente.');
                    }
                    $token = JWTAuth::refresh();
                    JWTAuth::setToken($token);
                    $data[$contaId]->t = $token;
                    request()->session()->put(config('login.session_data'), json_encode($data));
                }
            } catch (\Exception $e) {
                return redirect()->route("web.login.logout");
            }

            $request->headers->set('Authorization', 'Bearer ' . $token);
            URL::defaults(['contaId' => $contaId]);
            $request->route()->forgetParameter('contaId');
            return $next($request);
        }

        return redirect()->route("web.login.index")->with('error', 'Sessão expirada. Acesse novamente.');
    }

    private function rotaAberta(Request $request)
    {
        if (Route::current()->uri() == 'login')
            return true;
        if (Route::current()->uri() == 'login/{user}')
            return true;
        if (Route::current()->uri() == 'registrar')
            return true;
        if (Route::current()->uri() == 'logout')
            return true;
        if (Route::current()->uri() == 'forget/{email}/{empresa?}')
            return true;
        return false;
    }

    private function rotaSemiAberta(Request $request)
    {
        if (Route::current()->uri() == 'login/{user}/company')
            return true;
        return false;
    }
}
