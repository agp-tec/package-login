<?php

Route::group(['as' => 'web.', 'namespace' => 'Agp\Login\Controller\Web'], function () {
    Route::get('/login', 'AuthController@index')->name('login.index'); //Form para coletar e-mail ou cpf
//    Route::post('/login', 'AuthController@find')->name('login.find'); //Encontra usuário
//    Route::get('/login/{user}', 'AuthController@pass')->name('login.pass'); //Form para informar senha
//    Route::post('/login/{user}', 'AuthController@login')->name('login.do'); //Realiza login (usuario + senha)
//    Route::get('/login/{user}/company', 'AuthController@selecionaEmpresa')->name('login.empresaForm'); //Form para selecionar empresa
//    Route::post('/login/{user}/company', 'AuthController@empresa')->name('login.empresa'); //Realiza login (usuario + empresa)
//    Route::get('/registrar', 'AuthController@create')->name('login.create'); //Form para registrar
//    Route::post('/registrar', 'AuthController@store')->name('login.store'); //Salva usuario
//    Route::get('/logout', 'AuthController@logout')->name('login.logout'); //Deslogar
//    Route::get('/forget', 'AuthController@forget')->name('login.forget'); //Esquecer cookies
//    Route::get('/login/{user}/recover', 'AuthController@recovering')->name('login.recoverForm'); //Form para recuperar senha
//    Route::post('/login/{user}/recover', 'AuthController@recover')->name('login.recover'); //Recupera senha
});

?>
