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

Route::get('/welcome', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/', 'ItemController@index');
Route::get('/stream', 'ItemController@stream');
Route::get('/item/{item}', 'ItemController@show');

Route::post('/cartitem', 'CartItemController@store');
Route::get('/cartitem', 'CartItemController@index');
Route::delete('/cartitem/{cartItem}', 'CartItemController@destroy');
Route::put('/cartitem/{cartItem}', 'CartItemController@update');

Route::get('/buy', 'BuyController@index');
Route::post('/buy', 'BuyController@store');

Route::group(['prefix' => 'admin'], function() {
    Route::get('login',     'Admin\LoginController@showLoginForm')->name('admin.login');
    Route::post('login',    'Admin\LoginController@login');
    Route::get('/',      'Admin\AdminController@index')->name('admin.home');
});

Route::group(['prefix' => 'admin', 'middleware' => 'auth:admin'], function() {
    Route::post('logout',   'Admin\LoginController@logout')->name('admin.logout');
    Route::get('/',      'Admin\AdminController@index')->name('admin.home');
    Route::get('/sale',      'Admin\AdminController@sale')->name('admin.sale');
    Route::post('/import_csv', 'Admin\AdminController@importCsv')->name('admin.import.csv');
    Route::get('/export_csv', 'Admin\AdminController@exportCsv')->name('admin.export.csv'); 
});