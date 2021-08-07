<?php

Route::post('/login', 'Auth\LoginController@authenticateUserAPI')->name('api.login');

Route::group(['middleware' => 'verifyApi', 'prefix' => 'auth' ], function($router){
    Route::group(['prefix' => 'users'], function(){
        Route::get('/', 'Api\UserController@index')->name('api.user.index');
        Route::put('/update/{id}', 'Api\UserController@update')->name('api.user.update');
    });
});
