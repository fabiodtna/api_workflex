<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Http\Requests\AuthRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\Chat;
use App\Models\User;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $idchat = $request->input('id');
        $userId = Auth::user()->id; 

        // Verificar se existe chat_id
        $msgv = Chat::where('id', $idchat)->exists();
        if(!$idchat){
            return response()->json(['error' => 'Não Existe'], 404);
        }

        $chat = Chat::where('id', $idchat)
            ->where(function ($query) use ($userId) {
                $query->where('user_id1', $userId)
                    ->orWhere('user_id2', $userId);
            })
            ->first();

        if ($chat) {
            // O usuário faz parte da conversa, agora você pode buscar as mensagens
            $mensagens = Message::where('chat_id', $idchat)->get();

            // Identificar o ID do usuário que não está logado
            $otherUserId = ($chat->user_id1 == $userId) ? $chat->user_id2 : $chat->user_id1;

            // Pegar o token de push notification do outro usuário
            $otherUser = User::find($otherUserId);
            if ($otherUser && $otherUser->push_token) {
                $token = $otherUser->push_token;
                $title = 'Nova mensagem no chat';
                $body = 'Você tem novas mensagens no chat ' . $idchat;
                $this->sendPushNotification($token, $title, $body);
            }

            return response()->json($mensagens);
        } else {
            // O usuário não faz parte da conversa, retorne uma resposta adequada
            return response()->json(['error' => 'Você não faz parte desta conversa.'], 403);
        }
    }

    public function sendPushNotification($token, $title, $body)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://exp.host/--/api/v2/push/send', [
            'to' => $token,
            'title' => $title,
            'body' => $body,
        ]);

        if ($response->successful()) {
            return response()->json(['success' => 'Notificação enviada com sucesso']);
        }

        return response()->json(['error' => 'Falha ao enviar a notificação'], $response->status());
    }


  
    public function store(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|integer',
            'content' => 'required|string|max:255',
        ]);

        $id_chat = $request->input('chat_id');

        // Verificar se o chat existe
        $chatExists = Chat::where('id', $id_chat)->exists();

        if (!$chatExists) {
            return response()->json(['error' => 'Chat não encontrado'], Response::HTTP_NOT_FOUND);
        }

        if (Auth::check()) {
            $userId = Auth::user()->id;

            // Verificar se o usuário faz parte do chat
            $chat = Chat::where('id', $id_chat)
                ->where(function ($query) use ($userId) {
                    $query->where('user_id1', $userId)
                          ->orWhere('user_id2', $userId);
                })
                ->first();

            if ($chat) {
                $msg = new Message();
                $msg->chat_id = $id_chat;
                $msg->id_user = $userId;
                $msg->content = $request->input('content');
                $msg->save();

                // Enviar notificação para o outro usuário
                $otherUserId = ($chat->user_id1 == $userId) ? $chat->user_id2 : $chat->user_id1;
                $otherUser = User::find($otherUserId);
                $otherUsernotify = $otherUser->notifitoken;
                $username = Auth::user()->nome;
                $msgtouser = $request->input('content');
                $msgnotify = strlen($msgtouser) > 20 ? substr($msgtouser, 0, 15) . ' ...' : $msgtouser;

                sleep(2);

                if (!empty($otherUsernotify)) {
                    $client = new Client();
                    $response = $client->post('https://exp.host/--/api/v2/push/send', [
                        'headers' => [
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'to' => $otherUsernotify,
                            'title' => $username,
                            'body' => $msgnotify,
                        ],
                    ]);
                }

                return response()->json($msg, Response::HTTP_CREATED);
            } else {
                return response()->json(['error' => 'Usuário não autorizado a participar deste chat'], Response::HTTP_FORBIDDEN);
            }
        } else {
            return response()->json(['error' => 'Não Autorizado'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
