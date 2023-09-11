<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
         'ft_user',
         'nome',
         'sobrenome', 
         'descricao', 
         'foto1', 
         'foto2', 
         'foto3',
         'uf',
         'cidade'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
