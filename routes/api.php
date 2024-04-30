<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImgController;

Route::post('/login/auth', [AuthController::class, 'auth']);
Route::post('/users', [UserController::class, 'store']);   
Route::post('/upload-image', [ImgController::class, 'uploadimg']);

Route:: middleware(['auth:sanctum'])->group(function(){

    Route::get('/user/{id}', [UserController::class,'show']);
    Route::post('/user/update', [UserController::class, 'update']); 
    Route::get('/login/verify', [AuthController::class, 'userauth']);
    Route::get('/logado',[AuthController::class, 'userauthdata']);

    Route::get('/post', [PostController::class,'index']);
    Route::post('/post', [PostController::class,'store']);
    Route::post('/post/search', [PostController::class,'search']);
    Route::get('/post/{id}', [PostController::class, 'show']);
    Route::post('/post/update/{id}', [PostController::class,'update']);
    Route::post('/post/del/{id}',  [PostController::class,'destroy']);
    Route::get('postsuser/', [PostController::class,'alluser']);
    Route::get('postusers/{id}', [PostController::class,'allpostuser']);

    Route::get('/chat',[ChatController::class, 'index']);
    Route::post('/chat/store',[ChatController::class, 'store']);

    Route::post('/msg', [MessageController::class, 'index']);
    Route::post('/msg/store', [MessageController::class, 'store']);

    Route::get('/logout', [AuthController::class, 'logout']);
});
  
Route::get('/', function(){
    return redirect('https://www.siteexterno.com');
});