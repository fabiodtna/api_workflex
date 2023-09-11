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
    
    public function auth(AuthRequest $request){

        //passar dados email password e device_name
       $user =  User::where('email', $request->email)->first();
       if(!$user | !Hash::check($request->password, $user->password)){
            return response()->json([
                'Error'=> 'Senha ou email esta incorreto!'
            ]);
       }else{
            $user->tokens()->delete();
            $token = $user->createToken($request->device_name)->plainTextToken;

            return response()->json([
                'token'=> $token
        ]);
       }
    }
    public function userauth(){
        $check = auth::check();
        return response()->json($check);
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
