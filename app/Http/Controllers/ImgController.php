<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImgController extends Controller
{

    public function uploadimg(Request $request)
{
    

    // Valide o arquivo enviado
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Exemplo de regras de validação
    ]);

    $from = $request->input('from');
    
    if($from == 'workflex_apk'){
          $imageName = time() . '.jpg';
    // Armazene a imagem na pasta custom_images dentro de public
    $imagePath = $request->file('image')->storeAs('/images', $imageName, 'public');

    // Obtenha a URL pública para a imagem armazenada
    $imageUrl = Storage::url($imagePath);

    // Você pode salvar $imageUrl no seu banco de dados ou retorná-lo na resposta
    // como uma URL de acesso à imagem.
    
    return response()->json(['message' => 'Image uploaded successfully', 'imageUrl' => $imageUrl, 'nomeimg' => $imageName]);
    }


    return response()->json(['message' => 'Algo deu errado!']);
  
}

}
