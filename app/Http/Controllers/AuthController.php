<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
    // login user 
    public function auth(AuthRequest $request){

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'Error' => 'Senha ou email está incorreto!'
            ], 401);
        } else {
            $user->tokens()->delete();
            $token = $user->createToken($request->device_name)->plainTextToken;
            return response()->json([
                'token' => $token,
                'id_user' => $user->id
            ]);
        }
    }
    // verificar se o user esta com o token para fazer um login automatico 
    public function userauth(){
        $check = auth::check();
        return response()->json($check);
    }
    
    //pegar dados do user Logado
    public function userauthdata(){
        
        $check = auth::check();
        $user = Auth::user();

        if($check == true){
            return response()->json($user);
        }
    }
    

    public function logout(){

        $user = Auth::user();

        $nomeDoUsuario = Auth::user()->nome;
        // Revogue o token de autenticação do usuário
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return response()->json(['message' => 'Logout efetuado com sucesso', $nomeDoUsuario]);
    }


}
