<?php

return [
    'base' => env('LOGIN_BASE', 'api'), //"api" para login via API, ou "entity" para login via models (Banco de dados)
    'find_base' => env('LOGIN_FIND_BASE', 'api'), //"api" para pesquisa de usuarios via API, ou "entity" para pesquisa de usuarios via models (Banco de dados)
    'api' => env('LOGIN_API'),
    'id_app' => env('ID_APP'),
    'accounts_cookie' => env('LOGIN_ACCOUNTS_COOKIE', 'accounts'),
    'session_data' => env('LOGIN_SESSION_DATA', 'data'),
    'user_entity' => env('LOGIN_USER_ENTITY', 'App\Model\Entity\Usuario.php'),
    'use_empresa' => true, //Indica se sistema espera um token com empresaId
    'use_conta_id' => true, //Indica se sistema espera o parametro contaId nas rodas

    'view_index' => env('LOGIN_VIEW_INDEX', 'Login::view.login.index'),
    'view_login' => env('LOGIN_VIEW_LOGIN', 'Login::view.login.login'),
    'view_empresa' => env('LOGIN_VIEW_EMPRESA', 'Login::view.login.empresa'),
    'view_registrar' => env('LOGIN_VIEW_REGISTRAR', 'Login::view.login.create'),
    'view_recuperar_senha' => env('LOGIN_VIEW_RECUPERAR_SENHA', 'Login::view.login.recuperar'),
];
