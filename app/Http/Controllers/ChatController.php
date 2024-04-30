<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use App\Models\User;

class ChatController extends Controller
{
    public function index(){

        $userId = Auth::id();

        $chats = Chat::where('user_id1', $userId)
            ->orWhere('user_id2', $userId) // Carrega os relacionamentos dos usuários
            ->get();

    $formattedChats = $chats->map(function ($chat) use ($userId) {
        // Determine o ID do outro usuário no chat
        $otherUserId = $chat->user_id1 === $userId ? $chat->user_id2 : $chat->user_id1;

        // Obtenha os detalhes do outro usuário
        $otherUser = User::find($otherUserId);

        // Formate a resposta
        return [
            'chat_id' => $chat->id,
            'other_user' => [
                'id' => $otherUser->id,
                'nome' => $otherUser->nome,
                'sobrenome' => $otherUser->sobrenome,
                'photo' => $otherUser->ft_user,
            ],
            // Outros dados do chat, se necessário
        ];
    });

    return response()->json($formattedChats);



        //$chats = Chat::where('user_id1', Auth::id())
        //->orWhere('user_id2', Auth::id())
        //->get();

        //return response()->json($chats);
        
    }

    public function store(Request $request)
    {
        // Verifica se o usuário está autenticado
        if(Auth::check()){
            $userId = Auth::user()->id;
            
            // Obtém o ID do segundo usuário do request
            $user_id2 = $request->input('id_user');
            
            // Verifica se o usuário existe
            $user2 = User::find($user_id2);
    
            // Verifica se o usuário existe e se não é o mesmo que o usuário logado
            if($user2 && $user_id2 != $userId){
    
                // Verifica se já existe um chat entre os dois usuários
                $existingChat = Chat::where(function($query) use ($userId, $user_id2) {
                    $query->where('user_id1', $userId)
                          ->where('user_id2', $user_id2);
                })->orWhere(function($query) use ($userId, $user_id2) {
                    $query->where('user_id1', $user_id2)
                          ->where('user_id2', $userId);
                })->first();
    
                // Se não existe um chat, cria um novo
                if(!$existingChat){
                    $chat = new Chat();
                    $chat->user_id1 = $userId;
                    $chat->user_id2 = $user_id2;
                    $chat->save();
                    return response()->json(['chat_id' => $chat->id]);
                } else {
                    return response()->json(['chat_id' => $existingChat->id]);
                }
            } else {
                return ['error' => 'User nao existe'];
            }
        } else {
            return ['error' => 'Não Autorizado'];
        }
    }
    

}
