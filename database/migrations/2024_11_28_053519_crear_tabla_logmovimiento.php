<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaLogmovimiento extends Migration
{
    public function up()
    {
        Schema::create('logmovimiento', function (Blueprint $table) {
            $table->id('idReporte');
            $table->time('Hora');
            $table->date('Fecha');
            $table->string('imagen')->nullable();  // Campo para la imagen (puede ser null)
        });
    }

    public function down()
    {
        Schema::dropIfExists('logmovimiento');
    }
}
