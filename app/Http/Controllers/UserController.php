<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;

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

        $validator = Validator::make($request->all(), [
            'ft_user' => 'string|max:255',
            'ft_capa'=> 'string|max:255',
            'nome' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:50',
            'telefone' => 'required|string|max:15',
            'cpf'=>'string|max:13',
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
            'email.unique' => 'Este email ou cpf já existe.'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 205);
        }

        // Se a validação passar, você pode criar o usuário
        $user = new User();
        $user->id = $uuid;
        $user->ft_user = $request->input('ft_user');
        $user->ft_capa = $request->input('ft_capa');
        $user->nome = $request->input('nome');
        $user->sobrenome = $request->input('sobrenome');
        $user->telefone = $request->input('telefone');
        $user->cpf = $request->input('cpf');
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

        return response()->json(['Cadastrado com Sucesso!', 200]);

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


    //colocar isso pra funcionar
    public function resetSenha(Request $request)
        {
            // Valide o campo de e-mail
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            // Encontre o usuário com base no e-mail
            $user = User::where('email', $request->email)->first();

            // Gere uma nova senha aleatória
            $novaSenha = Str::random(8);
        
            // Atualize a senha do usuário com a nova senha gerada
            $user->password = bcrypt($novaSenha);
            $user->save();

            // Envie um e-mail com a nova senha para o usuário
            // Você pode usar o serviço de envio de e-mail do Laravel aqui

            // Redirecione de volta com uma mensagem de sucesso
            return redirect()->route('login')
                ->with('success', 'Sua senha foi redefinida com sucesso. Verifique seu e-mail para a nova senha.');
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


    public function emailresetpass()
{
    $num = str_pad(mt_rand(1,99999999),8,'0',STR_PAD_LEFT);


    $detalhes = [
        'titulo' => 'Bem-vindo ao Workflex!',
        'mensagem' => 'Reset senha sua senha'
    ];

    Mail::to('sansdtna@gmail.com')->send(new WorkflexMail($detalhes));

    return 'E-mail enviado com sucesso!';
}


}
    