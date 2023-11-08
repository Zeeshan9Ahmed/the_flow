<?php

use App\Services\User\CreateUserService;
use Illuminate\Support\Facades\Route;

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

Route::get('/unauthorize', function () {
   return response()->json(["status"=>0,"message"=>"Sorry User is Unauthorize"], 401);
})->name('unauthorize');

