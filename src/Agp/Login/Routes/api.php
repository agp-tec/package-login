<?php

Route::group(['as' => 'api.', 'prefix' => 'api', 'middleware' => 'api', 'namespace' => 'Agp\Login\Controller\Api'], function () {
    Route::post('find', 'AuthController@find')->name('login.find'); //Encontra usuário
    Route::post('login-direct', 'AuthController@loginDirect')->name('login.direct'); //Realiza login um apenas um passo (doc ou email + senha);
    Route::post('login/{user}', 'AuthController@login')->name('login.do'); //Realiza login (usuario + senha);
    Route::post('login/{user}/company', 'AuthController@empresa')->name('login.empresa'); //Realiza login empresa;
    Route::post('registrar', 'AuthController@store')->name('login.store'); //Salva usuario

    Route::post('password/recover/{user}', 'AuthController@recover');
    Route::put('password/reset/{token}/{email}', 'AuthController@update');

    Route::get('get-user', 'AuthController@user');
});
?>
