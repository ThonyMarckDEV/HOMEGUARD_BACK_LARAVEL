<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearTablaAuditoria extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id(); // ID de la auditoría
            $table->unsignedBigInteger('idUsuario'); // ID del usuario relacionado
            $table->string('descripcion'); // Descripción de la acción
            $table->timestamp('fecha_hora'); // Fecha y hora de la acción
            $table->foreign('idUsuario')->references('idUsuario')->on('usuarios')->onDelete('cascade'); // Relación con la tabla users
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auditoria');
    }
}
