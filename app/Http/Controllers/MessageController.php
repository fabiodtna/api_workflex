<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use App\Models\User;

class MessageController extends Controller
{


    public function index(Request $request){

        $idchat = $request->input('id');

        $userId = Auth::user()->id; 

        //verificar se existe chat_id
        $msgv = Chat::where('id', $idchat)->exists();
        if(!$idchat){
            return ['error' => 'Não Existe'];
        }


        $participaDaConversa = Chat::where('id', $idchat)
            ->where(function ($query) use ($userId) {
                $query->where('user_id1', $userId)
                    ->orWhere('user_id2', $userId);
            })
            ->exists();

            if ($participaDaConversa) {
                // O usuário faz parte da conversa, agora você pode buscar as mensagens
                $mensagens = Message::where('chat_id', $idchat)->get();
                return response()->json($mensagens);
            } else {
                // O usuário não faz parte da conversa, retorne uma resposta adequada
                return response()->json(['error' => 'Você não faz parte desta conversa.'], 403);
            }

    }
    
    public function store(Request $request)
    {

        $id_chat = $request->input('chat_id');

        $msgv = Chat::where('id', $id_chat)->exists();

        if(!$id_chat){
            return ['error' => 'Não Existe'];
        }
        if (Auth::check()){
            $userId = Auth::user()->id;  
            $chat = Chat::where('id', $id_chat)
            ->where('user_id1', $userId)
            ->orWhere('user_id2', $userId)
            ->get();
            if($chat != '[]'){
                $msg = new message();
                $msg-> chat_id = $id_chat;
                $msg-> id_user = $userId;
                $msg-> content = $request->input('content');
                $msg-> save();
                return response()->json($msg);
            }{
                return 'error';
            }
           
            
        } else {
            return ['error' => 'Não Autorizado'];
        }


        //$msg = new Message();
        // validadar se o id do user logado faz parte do id_chat; 

        
        //$msg->chat_id = $request->input('chat_id');
        //$msg->content = $request->input('mensagem');
        //$msg->save();
        //return response()->json($msg);

    }
}
