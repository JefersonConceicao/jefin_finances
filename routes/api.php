<?php

Route::post('/login', 'Auth\LoginController@authenticateUserAPI')->name('api.login');

Route::group(['middleware' => 'verifyApi', 'prefix' => 'auth' ], function($router){
    Route::get('/users', 'Api\UserController@index')->name('api.user.index');
});
