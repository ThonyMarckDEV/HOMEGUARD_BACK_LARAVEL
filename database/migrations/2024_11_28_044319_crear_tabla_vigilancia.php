<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaVigilancia extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crear la tabla 'vigilance' para almacenar el estado del modo vigilancia
        Schema::create('vigilancia', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(false);  // Estado de la vigilancia (activo o no)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vigilancia');
    }
}
