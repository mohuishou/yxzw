<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

//登录
$app->post("login",[
    'as' => 'user.login',
    'uses' => 'User\UserController@login'
]);

//需要认证的路由
$app->group(['namespace'=>'User','middleware' => 'auth'],function () use($app){
    $app->get("/user",[
        'as' => 'user.info',
        'uses' => 'UserController@user'
    ]);

    $app->post("/logout",[
        'as' => 'user.logout',
        'uses' => 'UserController@logout'
    ]);

    $app->post("/email",[
        'as' => 'user.email',
        'uses' => 'UserController@updateEmail'
    ]);

    $app->post("auto",[
        'as' => 'user.auto',
        'uses' => 'UserController@autoSwitch'
    ]);
});