<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('ft_user');
            $table->string('ft_capa');
            $table->string('nome');
            $table->string('sobrenome');
            $table->string('telefone');
            $table->string('cpf'); /// colocar unique dps
            $table->string('email')->unique();
            $table->string('password');
            $table->string('cidade');
            $table->string('uf');
            $table->boolean('frela');
            $table->string('areainte');
            $table->text('descricao');
            $table->string('services');
            $table->string('avaliacao');
            $table->string('tempcad');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
