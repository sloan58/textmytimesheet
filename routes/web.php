<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/tmts', 'TextController@entry');

Route::group(['middleware' => ['admin']], function () {
    Route::get('/dashboard', 'DashboardController@index');
    CRUD::resource('user', 'Admin\UserCrudController');
    CRUD::resource('timeentry', 'Admin\TimeEntryCrudController');
    Route::post('report', 'DashboardController@report')->name('report');
});