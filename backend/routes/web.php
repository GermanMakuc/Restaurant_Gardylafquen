<?php
use App\Product;
use App\Category;
use App\State;
use App\Order;
use App\User;
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

Route::get('/', function () 
{
	$products = Product::latest()->get();
	$categories = Category::latest()->get();

	$CountStates = State::where('status', 'ESPERA')->count();
	$states = State::where('status', 'ESPERA')->latest()->get();

	$names = User::pluck('name','id');

    return view('welcome', compact('products','categories', 
    	'CountStates', 'states', 'names'));
})->name('index');


Route::get('/Categories/', 'CategoryController@index')->name('index.category');
Route::post('/Categories/Create/', 'CategoryController@store')->name('store.category');
Route::post('/Categories/Delete/{id}', 'CategoryController@destroy')->name('destroy.category');
Route::post('/Categories/Update/{id}', 'CategoryController@update')->name('update.category');

Route::get('/Products/', 'ProductController@index')->name('index.product');
Route::post('/Products/Create/', 'ProductController@store')->name('store.product');
Route::post('/Products/Delete/{id}', 'ProductController@destroy')->name('destroy.product');
Route::post('/Products/Update/{id}', 'ProductController@update')->name('update.product');
Route::post('/Products/Update/image/{id}', 'ProductController@changeImage')->name('update.product.image');

Route::get('/User/', 'MeserosController@index')->name('index.user');
Route::post('/User/Create/', 'MeserosController@store')->name('store.user');
Route::post('/User/Delete/{id}', 'MeserosController@destroy')->name('destroy.user');
Route::post('/User/Update/{id}', 'MeserosController@update')->name('update.user');


Route::post('/Order/Create/', 'OrderController@store')->name('store.order');
Route::get('/Order/Show/{id}', 'OrderController@show')->name('show.order');
Route::get('/Order/Print/{id}', 'OrderController@print')->name('print.order');
Route::get('/Order/Print/Total/{id}', 'OrderController@printer')->name('print.total.order');
Route::get('/Order/Total/{id}', 'OrderController@total')->name('total.order');
Route::post('/Order/Update/{id}', 'OrderController@update')->name('update.order');
Route::post('/Order/Edit/{id}', 'OrderController@edit')->name('edit.order');
Route::post('/Order/Delete/{id}', 'OrderController@destroy')->name('destroy.order');

Route::post('/Result/Search/', 'OrderController@searchDates')->name('search.order');
Route::post('/Result/Search/Alternative/', 'OrderController@searchDate')->name('search.alter.order');

Route::get('/Result/', 'OrderController@result')->name('result.order');