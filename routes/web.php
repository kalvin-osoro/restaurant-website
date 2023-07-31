<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;

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

Route::get('/',[HomeController::class, "index"]);

Route::get('/users',[AdminController::class, "user"]);

Route::get('/foodMenu',[AdminController::class, "foodMenu"]);

Route::post('/uploadFood',[AdminController::class, "upload"]);

Route::get('/deleteUser/{id}',[AdminController::class, "deleteUser"]);
Route::get('/deleteMenu/{id}',[AdminController::class, "deleteMenu"]);

Route::get('/updateMenu/{id}',[AdminController::class, "updateMenu"]);
Route::post('/update/{id}',[AdminController::class, "update"]);

Route::get('/redirects',[HomeController::class, "redirects"]);

Route::post('/reservation',[AdminController::class, "reservation"]);

Route::get('/viewreservation',[AdminController::class, "viewreservation"]);

Route::get('/viewchef',[AdminController::class, "viewchef"]);

Route::post('/uploadchef',[AdminController::class, "uploadchef"]);

Route::get('/updatechef/{id}',[AdminController::class, "updateChef"]);
Route::post('/updatefoodchef/{id}',[AdminController::class, "updateFoodChef"]);
Route::get('/deletechef/{id}',[AdminController::class, "deleteChef"]);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
