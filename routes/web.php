<?php
//AUTENTICAÇÃO DO USUÁRIO
Route::get('/', 'Auth\LoginController@renderFormLogin')->name('auth.renderViewLogin');
Route::post('/login', 'Auth\LoginController@authenticateUser')->name('auth.login');
Route::post('/loginWithGoogle', 'Auth\LoginController@userWithGoogle')->name('auth.authWithGoogle');

Route::get('/forgotPassword', 'Auth\ForgotPasswordController@renderViewForgotPassword')->name('auth.forgotPassword');

Route::post('/sendMailForgotPassword', 'Auth\ForgotPasswordController@sendMailForgotPassword')->name('auth.sendMailForgotPassword');

Route::get('/forgotChangePassword/{token}', 'Auth\ResetPasswordController@renderViewResetPassword')->name('auth.resetPassword');
Route::put('/resetPassword', 'Auth\ResetPasswordController@resetPassword')->name('auth.resetPassword');

//REGISTRO DE USUÁRIO
Route::get('/signup', 'Auth\RegisterController@renderFormSignUp')->name('auth.register');
Route::post('/requestSignUP', 'Auth\RegisterController@registerUser')->name('auth.registerUser');

//SAIR DO SISTEMA
Route::get('/logout', 'Auth\LoginController@logout')->name('auth.logout');

//GUIA DO USUÁRIO: PÚBLICO, ABRE PELA TELA DE LOGIN
Route::view('/guia', 'guia.index')->name('guia.index');

//ROTAS COM AUTENTICAÇÃO
Route::group(['middleware' => 'auth'], function(){
    //A PROJEÇÃO É A TELA INICIAL; /home FICA PARA LINKS ANTIGOS
    Route::redirect('/home', '/projecao')->name('home.index');

    Route::group(['prefix' => 'users'], function(){
        //RENDER VIEWS
        Route::get('/', 'UsersController@index')->name('users.index');
        Route::get('/create', 'UsersController@create')->name('users.create');
        Route::get('/profile', 'UsersController@profile')->name('users.profile');
        Route::get('/edit/{id}', 'UsersController@edit')->name('users.edit');
        Route::post('/store', 'UsersController@store')->name('users.store');
        Route::put('/update/{id}', 'UsersController@update')->name('users.update');
        Route::delete('/delete/{id}', 'UsersController@delete')->name('users.delete');
        Route::put('/profileUpdate', 'UsersController@profileUpdate')->name('users.profileUpdate');
        Route::put('/changePassword', 'UsersController@changePassword')->name('users.changePassword');
    });

    Route::group(['prefix' => 'proventos'], function(){
        Route::get('/', 'ProventosController@index')->name('proventos.index');
        Route::get('/create', 'ProventosController@create')->name('proventos.create');
        Route::post('/store', 'ProventosController@store')->name('proventos.store');
        Route::get('/edit/{id}', 'ProventosController@edit' )->name('proventos.edit');
        Route::put('/update/{id}', 'ProventosController@update')->name('proventos.update');
        Route::delete('/delete/{id}', 'ProventosController@delete')->name('proventos.delete');
        Route::post('/copyProventos', 'ProventosController@copyProventos')->name('proventos.copy');
    });

    Route::group(['prefix' => 'tiposDespesas'], function(){
        Route::get('/', 'TiposDespesasController@index')->name('tiposDespesas.index');
        Route::get('/create', 'TiposDespesasController@create')->name('tiposDespesas.create');
        Route::post('/store', 'TiposDespesasController@store')->name('tiposDespesas.store');
        Route::get('/edit/{id}', 'TiposDespesasController@edit')->name('tiposDespesas.edit');
        Route::put('/update/{id}', 'TiposDespesasController@update')->name('tiposDespesas.update');
        Route::delete('/delete/{id}', 'TiposDespesasController@delete')->name('tiposDespesas.delete');
        Route::get('/optionsDespesasJSON', 'TiposDespesasController@optionsDespesasJSON') ->name('tiposDespesas.optionsDespesas');
    });

    Route::group(['prefix' => 'despesas'], function(){
        Route::get('/', 'DespesasController@index')->name('despesas.index');
        Route::get('/create', 'DespesasController@create')->name('despesas.create');
        Route::post('/store', 'DespesasController@store')->name('despesas.store');
        Route::get('/edit/{id}', 'DespesasController@edit')->name('despesas.edit');
        Route::put('/update/{id}', 'DespesasController@update')->name('despesas.update');
        Route::delete('/delete/{id}', 'DespesasController@delete')->name('despesas.delete');
        Route::put('/payDespesa/{id}', 'DespesasController@delcararPagamentoDespesa')->name('despesa.pay');
    });

    Route::group(['prefix' => 'proventosFixos'], function(){
        Route::get('/', 'ProventosFixosController@index')->name('proventosFixos.index');
        Route::get('/create', 'ProventosFixosController@create')->name('proventosFixos.create');
        Route::post('/store', 'ProventosFixosController@store')->name('proventosFixos.store');
        Route::get('/edit/{id}', 'ProventosFixosController@edit')->name('proventosFixos.edit');
        Route::put('/update/{id}', 'ProventosFixosController@update')->name('proventosFixos.update');
        Route::delete('/delete/{id}', 'ProventosFixosController@delete')->name('proventosFixos.delete');
        Route::put('/reajustar/{id}', 'ProventosFixosController@reajustar')->name('proventosFixos.reajustar');
        Route::put('/encerrar/{id}', 'ProventosFixosController@encerrar')->name('proventosFixos.encerrar');
    });

    Route::group(['prefix' => 'despesasFixas'], function(){
        Route::get('/', 'DespesasFixasController@index')->name('despesasFixas.index');
        Route::get('/create', 'DespesasFixasController@create')->name('despesasFixas.create');
        Route::post('/store', 'DespesasFixasController@store')->name('despesasFixas.store');
        Route::get('/edit/{id}', 'DespesasFixasController@edit')->name('despesasFixas.edit');
        Route::put('/update/{id}', 'DespesasFixasController@update')->name('despesasFixas.update');
        Route::delete('/delete/{id}', 'DespesasFixasController@delete')->name('despesasFixas.delete');
        Route::put('/reajustar/{id}', 'DespesasFixasController@reajustar')->name('despesasFixas.reajustar');
        Route::put('/encerrar/{id}', 'DespesasFixasController@encerrar')->name('despesasFixas.encerrar');
    });

    Route::get('/projecao', 'ProjecaoController@index')->name('projecao.index');

    Route::group(['prefix' => 'lancamentos'], function(){
        Route::get('/', 'LancamentosController@index')->name('lancamentos.index');
        Route::get('/create', 'LancamentosController@create')->name('lancamentos.create');
        Route::post('/store', 'LancamentosController@store')->name('lancamentos.store');
        Route::get('/edit/{id}', 'LancamentosController@edit')->name('lancamentos.edit');
        Route::put('/update/{id}', 'LancamentosController@update')->name('lancamentos.update');
        Route::delete('/delete/{id}', 'LancamentosController@delete')->name('lancamentos.delete');
        Route::get('/getGastosGraphs', 'LancamentosController@dataGastosGraficos')->name('lancamentos.dataGrafico');
    });

});

