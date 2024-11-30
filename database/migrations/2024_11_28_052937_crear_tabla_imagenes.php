<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaImagenes extends Migration
{
    public function up()
    {
        Schema::create('imagenes', function (Blueprint $table) {
            $table->id('idImagen');  // ID autoincremental
            $table->string('ruta_imagen');  // Ruta de la imagen
            $table->timestamp('fecha')->nullable();  // Columna 'fecha' de tipo timestamp
        });
    }

    public function down()
    {
        Schema::dropIfExists('imagenes');  // Eliminar la tabla si es necesario
    }
}
