<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\OtpReset;
use App\Models\User;
use App\Mail\TestEmail;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {

        
        $request->validate([
            'email' => 'required|email',
        ]);
        
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Se o e-mail não existir, retorne um erro
            return response()->json([
                'message' => 'E-mail não encontrado.',
            ], 404);
        }


        $latestOtp = OtpReset::where('email', $request->email)
            ->latest()
            ->first();
    
            if ($latestOtp && $latestOtp->created_at->gt(now()->subDays(2))) {
                return response()->json(['message' => 'Aguarde 2 dias antes de solicitar um novo OTP'], 429);
            }
          // Gerar o código OTP de 8 dígitos
        $otp = rand(10000000, 99999999);

        // Salvar o OTP no banco de dados com o e-mail e data de expiração
        $otpReset = OtpReset::create([
            'email' => $request->email,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(20), // Tempo de expiração de 10 minutos
        ]);



        // Dados dinâmicos do e-mail
        $data = [
            'otp_code' => $otp,
        ];

        try {
            // Envia o e-mail
            Mail::to($request->email)->send(new TestEmail($data));
            
            return
             response()->json([
                'message' => 'E-mail enviado com sucesso!'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao enviar e-mail: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
