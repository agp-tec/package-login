<?php

return [
    'base' => env('LOGIN_BASE', 'api'), //"api" para login via API, ou "entity" para login via models (Banco de dados)
    'find_base' => env('LOGIN_FIND_BASE', 'entity'), //"api" para pesquisa de usuarios via API, ou "entity" para pesquisa de usuarios via models (Banco de dados)
    'api' => env('LOGIN_API'),
    'id_app' => env('ID_APP'),
    'device_cookie' => env('LOGIN_DEVICE_COOKIE', 'device'),
    'accounts_cookie' => env('LOGIN_ACCOUNTS_COOKIE', 'accounts'),
    'session_data' => env('LOGIN_SESSION_DATA', 'data'),
    'user_entity' => env('LOGIN_USER_ENTITY', 'App\Model\Entity\Usuario'),
    'user_login_callback' => env('LOGIN_LOGIN_CALLBACK', 'App\Model\Service\UsuarioService'),
    'pos_login_route' => env('LOGIN_POS_LOGIN_ROUTE', 'pos-login'),
    'user_notfound_route' => env('LOGIN_USER_NOT_FOUND_ROUTE', 'web.login.index'),
    'forget_route' => env('LOGIN_FORGET_ROUTE', 'forget'),
    'recover_route' => env('LOGIN_RECOVER_ROUTE', null), //Rota para formulario de recuperacao de senha. Se null, utiliza do adm
    'use_empresa' => true, //Indica se sistema espera um token com empresaId e envia chave empresa em registro
    'use_conta_id' => true, //Indica se sistema espera o parametro contaId nas rodas
    'search_serpro' => false, //Indica se sistema deve consultar a pessoa na base de dados da serpro;
    'login_accept' => [
        'cpf' => 'required|cpf|formato_cpf',
        'e-mail' => 'required|email',
    ],

    'view_index' => env('LOGIN_VIEW_INDEX', 'Login::view.login.index'),
    'view_login' => env('LOGIN_VIEW_LOGIN', 'Login::view.login.pass'),
    'view_empresa' => env('LOGIN_VIEW_EMPRESA', 'Login::view.login.empresa'),
    'view_registrar' => env('LOGIN_VIEW_REGISTRAR', 'Login::view.login.create'),
    'view_recuperar_senha' => env('LOGIN_VIEW_RECUPERAR_SENHA', 'Login::view.login.recover'),
];
