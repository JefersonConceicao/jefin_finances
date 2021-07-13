<?php
Route::get('/', function () {
    return view('auth.login');
});

Route::get('/signup', function(){
    return view('auth.register');
});

Route::get('/forgotPassword', function(){
    return view('auth.forgot_password');
});


