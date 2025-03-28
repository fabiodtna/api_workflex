<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;
use App\Models\Post;
use App\Models\OtpReset;
use Carbon\Carbon;
use App\Mail\workflexmail;

use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
   // public function index(){
   //     $users = User::all();
   //     return response()->json($users);
   // }

   public function show($id)
   {
       try {
           $user = User::findOrFail($id);
           return response()->json($user);
       } catch (ModelNotFoundException $e) {
           return response()->json(['message' => 'User não encontrado!']);
       }
   }

   public function searchuser(Request $request){

    $perPage = 20;

    $termoPesquisa = $request->input('termo_pesquisa');

    $user = User::where(function ($query) use ($termoPesquisa) {
        $query->where('nome', 'like', '%' . $termoPesquisa . '%')
        ->orWhere('sobrenome', 'like', '%' . $termoPesquisa . '%')
        ->orWhere('descricao', 'like', '%' . $termoPesquisa . '%')
        ->orWhere('cidade', 'like', '%' . $termoPesquisa . '%');
    })->paginate($perPage);

    return response()->json($user);
   }

   public function store(Request $request)
   {
       do {
           $uuid = mt_rand(100000, 9999999999);
       } while (User::where('id', $uuid)->exists());
   
       // Validação dos dados
       $validator = Validator::make($request->all(), [
           'ft_user' => 'string|max:255',
           'ft_capa'=> 'string|max:255',
           'nome' => 'required|string|max:255',
           'sobrenome' => 'required|string|max:50',
           'telefone' => 'required|string|max:15',
           'cpf' => ['required', 'string', 'size:11', function ($attribute, $value, $fail) {
               if (!self::isValidCPF($value)) {
                   $fail('CPF inválido!');
               }
           }],
           'email' => [
               'required',
               'string',
               'email',
               'max:255',
               Rule::unique('users')->where(function ($query) use ($request) {
                   return $query->where('email', $request->input('email'));
               })->ignore(null, 'id')
           ],
           'password' => 'required|string|min:6',
           'cidade' => 'string|max:100',
           'uf'=> 'string|max:4',
           'frela' => 'boolean',
           'descricao'=> 'string|max:230',
           'tempcad' => 'string|max:30',
       ], [
           'email.unique' => 'Este email já existe.',
           'cpf.unique' => 'Este CPF já está cadastrado.',
       ]);
   
       if ($validator->fails()) {
           return response()->json(['error' => $validator->errors()->first()], 205);
       }
   
       // Hash do CPF para armazenar de forma segura
       $cpf = $request->input('cpf');
       $hashcpf = hash('sha256', $cpf);
   
       // Verifica se o CPF já está cadastrado
       if (User::where('cpf', $hashcpf)->exists()) {
           return response()->json(['message' => 'CPF já cadastrado!'], 205);
       }
   
       // Criação do usuário
       $user = new User();
       $user->id = $uuid;
       $user->ft_user = $request->input('ft_user');
       $user->ft_capa = $request->input('ft_capa');
       $user->nome = $request->input('nome');
       $user->sobrenome = $request->input('sobrenome');
       $user->telefone = $request->input('telefone');
       $user->cpf = $hashcpf;
       $user->email = $request->input('email');
       $user->password = bcrypt($request->input('password'));
       $user->cidade = $request->input('cidade');
       $user->uf = $request->input('uf');
       $user->frela = $request->input('frela');
       $user->areainte = $request->input('areainte');
       $user->descricao = $request->input('descricao');
       $user->services = $request->input('service');
       $user->avaliacao = '';
       $user->tempcad = '';
       $user->save();
   
       return response()->json(['message' => 'Cadastrado com Sucesso!'], 200);
   }
   
   // Função para validar CPF
   public static function isValidCPF($cpf)
   {
       // Remove caracteres não numéricos
       $cpf = preg_replace('/[^0-9]/', '', $cpf);
   
       if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
           return false;
       }
   
       for ($t = 9; $t < 11; $t++) {
           $d = 0;
           for ($c = 0; $c < $t; $c++) {
               $d += $cpf[$c] * (($t + 1) - $c);
           }
           $d = ((10 * $d) % 11) % 10;
           if ($cpf[$c] != $d) {
               return false;
           }
       }
   
       return true;
   }
   
    public function update(Request $request){
     
          // Obtenha o usuário autenticado
         $user = Auth::user();

        // Verifique se o usuário existe no banco de dados
         $existingUser = User::find($user->id);

        if (!$existingUser) {
            return redirect()->back()->with('error', 'Usuário não encontrado.');
        }
    
        // Atualize os campos do usuário
            $existingUser->ft_user = $request->input('ft_user') ?? $user->ft_user ;
            $existingUser->ft_capa = $request->input('ft_capa') ?? $user->ft_capa ;
            $existingUser->nome = $request->input('nome') ?? $user->nome;
            $existingUser->sobrenome = $request->input('sobrenome') ?? $user->sobrenome;
            $existingUser->telefone = $request->input('telefone') ?? $user->telefone;
            if($request->has('password')) {
                $existingUser->password = bcrypt($request->input('password'));
            }
            $existingUser->cidade = $request->input('cidade') ?? $user->cidade;
            $existingUser->uf = $request->input('uf') ?? $user->uf;
            $existingUser->frela = $request->input('frela') ?? $user->frela;
            $existingUser->areainte = $request->input('areainte') ?? $user->areainte;
            $existingUser->descricao = $request->input('descricao') ?? $user->descricao;
        
        // Salve as alterações
        $existingUser->save();

        // Redirecione de volta com uma mensagem de sucesso
        return response()->json($existingUser, 201);

    }


    public function resetSenha(Request $request)
    {
        $otpCode = $request->json('otpcode'); 
        $password = $request->json('senha');

        if (!$otpCode || !$password) {
            return response()->json([
                'error' => 'Faltando parâmetros necessários'
            ], 400);
        }

        // Busca o OTP no banco
        $otp = OtpReset::where('otp_code', $otpCode)
                    ->first();

      
        if (!$otp) {
            return response()->json([
                'error' => 'Código OTP inválido'
            ], 410);
        }

      
        
        // Verifica se o OTP já expirou
        if (Carbon::now()->greaterThan(Carbon::parse($otp->expires_at))) {
            return response()->json([
                'error' => 'Código OTP expirado'
            ], 410);
        }

        $user = User::where('email', $otp->email)->first();
        
        
        if (!$user) {
            return response()->json([
                'error' => 'Usuário não encontrado'
            ], 404);
        }


        $user->password = bcrypt($request->json('senha'));
        $user->save();


        return response()->json([
            'success' => 'Senha alterada com sucesso!'
        ], 200);
    }



    public function savetokenNotify(Request $request) {
    // Obtenha o usuário autenticado
    $user = Auth::user();

    // Verifique se o usuário está autenticado
    if (!$user) {
        return response()->json(['error' => 'Usuário não autenticado'], 401);
    }

    // Valide os dados da requisição para garantir que 'notifitoken' não esteja vazio
    $request->validate([
        'notifitoken' => 'required|string',
    ]);

    // Atualize o campo 'notifitoken' do usuário
    $user->notifitoken = $request->input('notifitoken');

    // Salve as alterações no banco de dados
    $user->save();

    // Retorne uma resposta JSON com os detalhes do usuário atualizado
    return response()->json(['message' => 'Notifitoken atualizado com sucesso'], 200);

    }
    public function getDeletedUsers()
    {
        $users = User::onlyTrashed()->get();
        return response()->json($users);
    }

    public function deluser(){

        $user = Auth::user();

        if ($user) {
            $user->delete(); 
            return response()->json(['message' => 'Usuário deletado com sucesso'], 200);
        }
            Post::where('user_id', $user->id)
            ->update(['status_post' => false]);
    
        return response()->json(['message' => 'Usuário não autenticado'], 401);
    }


}
    