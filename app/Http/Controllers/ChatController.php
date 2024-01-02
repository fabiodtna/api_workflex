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
       
        if(Auth::check()){

            $user_id2 = $request->input('id_user');
            //verifica se o user existe
            $iduser2 = User::where('id','=',$user_id2)->get();

            if($iduser2 != '[]'){
                $chat = new Chat();
                $userId = Auth::user()->id;
                $chat->user_id1 = $userId;
                $chat->user_id2 = $user_id2;
                $chat->save();
                return response()->json($chat);
            }else{
                return ['error' => 'Não Autorizado'];
            }

        }else{
            return ['error' => 'Não Autorizado'];
        }
      

    }

}
