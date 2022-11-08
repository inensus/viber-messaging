<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'viber-messaging'], function () {
    Route::group(['prefix' => 'viber-credential'], function () {
        Route::get('/', 'ViberCredentialController@show');
        Route::put('/', 'ViberCredentialController@update');
    });
    Route::group(['prefix' => 'webhook'], function () {
        Route::post('/', 'WebhookController@index');
    });

    /*
     * TODO: this is for mpm cloud
    Route::group(['prefix' => 'webhook'], function () {
        Route::get('/{company_name}', 'WebhookController@listen');
    });
    */

});