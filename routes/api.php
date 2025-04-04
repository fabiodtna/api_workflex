<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImgController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FeedbackController;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Mail;

Route::post('/login/auth', [AuthController::class, 'auth']);
Route::post('/register', [UserController::class, 'store']);
Route::post('/upload-image', [ImgController::class, 'uploadimg']);
Route::post('/send-email', [EmailController::class, 'sendEmail']);
Route::post('/reset-senha', [UserController::class, 'resetsenha']);

Route::post('/feedback', [FeedbackController::class, 'store']);
Route::get('/feedbackWorkflexservice', [FeedbackController::class, 'index']);

Route::get('/feedback', [FeedbackController::class, 'index']);
Route::post('/feedback', [FeedbackController::class, 'store']);
   
Route::middleware(['auth:sanctum'])->group(function(){
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/user/search', [UserController::class, 'searchuser']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::get('/login/verify', [AuthController::class, 'userauth']);
    Route::get('/logado', [AuthController::class, 'userauthdata']);
    Route::post('/notifytoken',  [UserController::class, 'savetokenNotify']);
    Route::get('/delete_user',  [UserController::class, 'deluser']);
 

    Route::get('/post', [PostController::class, 'index']);
    Route::post('/post', [PostController::class, 'store']);
    Route::post('/post/search', [PostController::class, 'search']);
    Route::get('/post/{id}', [PostController::class, 'show']);
    Route::post('/post/update/{id}', [PostController::class, 'update']);
    Route::get('/post/del/{id}',  [PostController::class, 'destroy']);
    Route::get('postsuser/', [PostController::class, 'alluser']);
    Route::get('postusers/{id}', [PostController::class, 'allpostuser']);

    Route::get('/chat', [ChatController::class, 'index']);
    Route::post('/chat/store', [ChatController::class, 'store']);

    Route::post('/msg', [MessageController::class, 'index']);
    Route::post('/msg/store', [MessageController::class, 'store']);

    Route::get('/logout', [AuthController::class, 'logout']);
});


Route::fallback(function(){
    return response()->json(['message' => 'Rota não encontrada ou método não suportado!'], Response::HTTP_NOT_FOUND);
});

Route::get('/', function(){
    return redirect('http://workflex.wuaze.com/');
});
?>