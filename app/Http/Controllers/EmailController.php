<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\Emailwk;


class EmailController extends Controller
{
    public function enviarEmail(Request $request)
    {
        // Valida se o e-mail foi enviado corretamente
        $request->validate([
            'email' => 'required|email'
        ]);

        // Pega o e-mail do usuário
        $destinatario = $request->input('email');

        // Envia o e-mail
        Mail::to($destinatario)->send(new Emailwk(['email' => $destinatario]));

        return response()->json(['mensagem' => 'E-mail enviado com sucesso para ' . $destinatario], 200);
    }
}
