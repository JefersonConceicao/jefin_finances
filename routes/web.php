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

//ROTAS COM AUTENTICAÇÃO
Route::group(['middleware' => 'auth'], function(){
    Route::get('/home', 'HomeController@index')->name('home.index');

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
        Route::post('/copyDespesas', 'DespesasController@copyDespesas')->name('despesas.copyDespesas');
    });

    Route::group(['prefix' => 'lancamentos'], function(){
        Route::get('/', 'LancamentosController@index')->name('lancamentos.index');
        Route::get('/create', 'LancamentosController@create')->name('lancamentos.create');
        Route::post('/store', 'LancamentosController@store')->name('lancamentos.store');
        Route::get('/edit/{id}', 'LancamentosController@edit')->name('lancamentos.edit');
        Route::put('/update/{id}', 'LancamentosController@update')->name('lancamentos.update');
        Route::delete('/delete/{id}', 'LancamentosController@delete')->name('lancamentos.delete');
        Route::get('/getGastosGraphs', 'LancamentosController@dataGastosGraficos')->name('lancamentos.dataGrafico');
    });

    Route::group(['prefix' => 'dividas'], function(){
        Route::get('/', 'DividasController@index')->name('dividas.index');
        Route::get('/create', 'DividasController@create')->name('dividas.create');
        Route::post('/store', 'DividasController@store')->name('dividas.store');
        Route::get('/edit/{id}', 'DividasController@edit')->name('dividas.edit');
        Route::put('/update/{id}', 'DividasController@update')->name('dividas.update');
        Route::get('/show/{id}', 'DividasController@show')->name('dividas.show');
        Route::put('/payDebt/{id}', 'DividasController@declarePayment')->name('dividas.declarePayment');
        Route::delete('/delete/{id}', 'DividasController@delete')->name('dividas.destroy');
    });

    Route::group(['prefix' => 'orcamentario'], function(){
        Route::get('/', 'OrcamentarioController@index')->name('orcamentario.index');
    });
});

