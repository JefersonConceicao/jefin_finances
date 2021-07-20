<?php
//AUTENTICAÇÃO DO USUÁRIO
Route::get('/', 'Auth\LoginController@renderFormLogin')->name('auth.renderViewLogin');
Route::post('/login', 'Auth\LoginController@authenticateUser')->name('auth.login');

Route::get('/forgotPassword', function(){
    return view('auth.forgot_password');
});

//REGISTRO DE  USUÁRIO
Route::get('/signup', 'Auth\RegisterController@renderFormSignUp')->name('auth.register');
Route::post('/requestSignUP', 'Auth\RegisterController@registerUser')->name('auth.registerUser');

//SAIR DO SISTEMA
Route::get('/logout', 'Auth\LoginController@logout')->name('auth.logout');

//ROTAS COM AUTENTICAÇÃO
Route::group(['middleware' => 'auth'], function(){
    Route::get('/home', 'HomeController@index')->name('home.index');

    Route::group(['prefix' => 'users'], function(){
        Route::get('/', 'UsersController@index')->name('users.index');
        Route::get('/create', 'UsersController@create')->name('users.create');
        Route::post('/store', 'UsersController@store')->name('users.store');
        Route::get('/edit/{id}', 'UsersController@edit')->name('users.edit');
        Route::put('/update/{id}', 'UsersController@update')->name('users.update');
    });
});



