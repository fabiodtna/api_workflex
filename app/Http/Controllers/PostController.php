<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Post;

class PostController extends Controller
{
    // Get posts
    public function index(){

        //http://127.0.0.1:8000/post?page=4

        $perPage = 10;

        $posts = Post::where('status_post', 'true')->orderBy('created_at', 'desc')
                     ->paginate($perPage);

        return response()->json($posts);
    }

    // Show one post
    public function show($id)
    {
        $post = Post::find($id);
        
        if($post){
            return response()->json($post);
        } else {
            return response()->json(['error' => 'O post não foi encontrado'], 404);
        }
      
        
    }

    // Search item
    public function search(Request $request){

        $perPage = 20;

        $termoPesquisa = $request->input('termo_pesquisa');

        $post = Post::where(function ($query) use ($termoPesquisa) {
            $query->where('nome', 'like', '%' . $termoPesquisa . '%')
            ->orWhere('sobrenome', 'like', '%' . $termoPesquisa . '%')
            ->orWhere('descricao', 'like', '%' . $termoPesquisa . '%')
            ->orWhere('cidade', 'like', '%' . $termoPesquisa . '%');
        })->paginate($perPage);

        return response()->json($post);

    }

    // Create post
     public function store(Request $request)
    {
        $user = Auth::user();

        $post = new Post();
        if( $request->input('cidade') == ''){
            $post->user_id = $user->id;
            $post->ft_user = $user->ft_user;
            $post->nome = $user-> nome;
            $post->sobrenome = $user ->sobrenome;
            $post->descricao = $request->input('descricao');
            $post->foto1 = $request->input('foto1');
            $post->foto2 = $request->input('foto2');
            $post->foto3 = $request->input('foto3');
            $post->status_post = $request->input('status_post');
            $post->uf = Auth::user()->uf; 
            $post->cidade = Auth::user()->cidade;
            $post->save();
        }
        else{
            $post->user_id = $user->id;
            $post->ft_user = $user->ft_user;
            $post->nome = $user-> nome;
            $post->sobrenome = $user ->sobrenome;
            $post->descricao = $request->input('descricao');
            $post->foto1 = $request->input('foto1');
            $post->foto2 = $request->input('foto2');
            $post->foto3 = $request->input('foto3');
            $post->status_post = $request->input('status_post');
            $post->uf = $request->input('uf');
            $post->cidade = $request->input('cidade');
            $post->save();
        }
      

        return response()->json(['message' => 'Sucesso!']);
    }

    // Update post 
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $userId = Auth::user()->id; 

        if($post->user_id == $userId){
            $post->descricao = $request->input('descricao');
            $post->foto1 = $request->input('foto1');
            $post->foto2 = $request->input('foto2');
            $post->foto3 = $request->input('foto3');
            $post->status_post = $request->input('status_post');
            $post->uf = $request->input('uf');
            $post->cidade = $request->input('cidade');
            $post->save();
            return response()->json($post);
        }

        return response()->json(['error' => 'Não autorizado!'], 404);
    }

    // Get all post user logado

    public function alluser(){

      
        $userId = Auth::user()->id; 

        $post = Post::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json($post);
    }

    // Get all post user com id 

    public function allpostuser($id){

        $post = Post::where('user_id', $id)->orderBy('created_at', 'desc')->paginate(10);
        
        return response()->json($post);
    }

    // Delete post
    public function destroy($id)
    {
        $post = Post::find($id);

        $userId = Auth::user()->id; 

        if ($post) {
            if($post->user_id == $userId){
                $post->delete();
                return response()->json(['success' => 'Post Exluido'], 202);
            }
            return response()->json(['error' => 'Nao Autorizado'], 404);
        } else {
            // O post não existe
            return response()->json(['error' => 'O post não foi encontrado'], 404);
        }
      
    }

}
