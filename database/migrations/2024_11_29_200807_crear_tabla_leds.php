<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaLeds extends Migration
{
    /**
     * Ejecuta la migración.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leds', function (Blueprint $table) {
            $table->id();  // Crea una columna 'id' como clave primaria
            $table->boolean('state');  // Campo para el estado del LED (activo/inactivo)
            $table->time('hora_encendido')->nullable();  // Hora de encendido del LED
            $table->time('hora_apagado')->nullable();  // Hora de apagado del LED (puede ser NULL)
        });
    }

    /**
     * Revierte la migración.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leds');
    }
}
