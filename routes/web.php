<?php
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/forgotPassword', function(){
    return view('auth.forgot_password');
});

//REGISTER USER
Route::get('/signup', 'Auth\RegisterController@renderFormSignUp')->name('auth.register');
Route::post('/requestSignUP', 'Auth\RegisterController@registerUser')->name('auth.registerUser');




