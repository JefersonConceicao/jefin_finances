<?php

Route::post('/login', 'Auth\LoginController@authenticateUserAPI')->name('api.login');
Route::get('/logout', 'Auth\LoginController@apiLogout')->name('api.logout');

Route::group(['middleware' => ['verifyApi'], 'prefix' => 'auth' ], function(){
    Route::group(['prefix' => 'users'], function(){
        Route::get('/', 'Api\UserController@index')->name('api.user.index');
        Route::post('/store', 'Api\UserController@store')->name('api.user.store');
        Route::put('/update/{id}', 'Api\UserController@update')->name('api.user.update');
        Route::delete('/delete/{id}', 'Api\UserController@delete')->name('api.user.delete');
        Route::get('/refreshToken', 'Api\UserController@refreshToken')->name('api.user.refreshToken');
    });

    Route::group(['prefix' => 'tiposDespesas'],function(){
        Route::get('/', 'Api\TiposDespesasController@index')->name('api.tiposDespesas.index');
        Route::post('/store', 'Api\TiposDespesasController@store')->name('api.tiposDespesas.store');
        Route::put('/update/{id}', 'Api\TiposDespesasController@update')->name('api.tiposDespesas.update');
        Route::delete('/delete/{id}', 'Api\TiposDespesasController@delete')->name('api.tiposDespesas.delete');
    });

    Route::group(['prefix' => 'lancamentos'], function(){
        Route::get('/', 'Api\LancamentosController@index')->name('api.lancamento.index');
    });

    Route::group(['prefix' => 'tiposDespesas'], function(){
        Route::get('/', 'Api\TiposDespesasController@index')->name('api.tiposDespesa.index');
        Route::post('/store', 'Api\TiposDespesasController@store')->name('api.tiposDespesa.store');
        Route::put('/update', 'Api\TiposDespesasController@update')->name('api.tiposDespesa.update');
        Route::delete('/delete', 'Api\TiposDespesasController@delete')->name('api.tiposDespesa.delete');
    });

    Route::group(['prefix' => 'proventos'], function(){
        Route::get('/', 'Api\ProventosController@index')->name('api.proventos.index');
        Route::post('/store', 'Api\ProventosController@store')->name('api.proventos.store');
        Route::put('/update/{id}', 'Api\ProventosController@update')->name('api.proventos.update');
        Route::delete('/delete/{id}', 'Api\ProventosController@delete')->name('api.proventos.delete');
    });
});
