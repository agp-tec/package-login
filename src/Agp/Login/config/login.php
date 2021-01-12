<?php

return [
    'base' => env('LOGIN_BASE', 'api'), //"api" para login via API, ou "base" para login via models (Banco de dados)
    'api' => env('LOGIN_API'),
    'accounts_cookie' => env('LOGIN_ACCOUNTS_COOKIE', 'accounts'),
    'session_data' => env('LOGIN_SESSION_DATA', 'data'),

    'view_index' => env('LOGIN_VIEW_INDEX', 'Login::view.index'),
    'view_login' => env('LOGIN_VIEW_LOGIN', 'Login::view.login'),
    'view_empresa' => env('LOGIN_VIEW_EMPRESA', 'Login::view.empresa'),
    'view_registrar' => env('LOGIN_VIEW_REGISTRAR', 'Login::view.registrar'),
    'view_recuperar_senha' => env('LOGIN_VIEW_RECUPERAR_SENHA', 'Login::view.recuperar'),
];
