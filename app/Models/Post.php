<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\DisabledPostScope;

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
         'status_post',
         'uf',
         'cidade'
    ];
    public function user(){
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new DisabledPostScope);
    }

  
}
