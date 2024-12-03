<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AuditoriaController extends Controller
{
    /**
     * Registrar el inicio de sesión de un usuario.
     *
     * @return void
     */
    public static function auditoriaIniciarSesion($idUsuario)
    {
        Auditoria::create([
            'idUsuario' => $idUsuario,  // Usar directamente el idUsuario
            'descripcion' => 'Inicio de sesión',  // Descripción del evento
            'fecha_hora' => Carbon::now(),  // Fecha y hora actual
        ]);
    }

    /**
     * Registrar el cierre de sesión de un usuario.
     *
     * @return void
     */
    public static function auditoriaCerrarSesion($idUsuario)
    {
        Auditoria::create([
            'idUsuario' => $idUsuario,  // Usar directamente el idUsuario
            'descripcion' => 'Cierre de sesión',
            'fecha_hora' => Carbon::now(),
        ]);
    }

     /**
     * Registrar el encendido o apagado del foco y la auditoría correspondiente.
     *
     * @param int $idUsuario
     * @param int $idFoco
     * @param int $state
     * @return void
     */
    public static function auditoriaEstadoFoco($idUsuario, $idFoco, $state)
    {
        // Descripción del evento
        $descripcion = $state == 0 ? 'Apagó el foco' : 'Encendió el foco';

        // Descripción del tipo de foco
        $tipoFoco = $idFoco == 1 ? 'Foco Patio' : ($idFoco == 2 ? 'Foco Casa' : 'Foco Desconocido');

        // Registrar la auditoría
        Auditoria::create([
            'idUsuario' => $idUsuario,  // El ID del usuario que realizó la acción
            'descripcion' => "$descripcion ($tipoFoco)",  // Descripción del evento
            'fecha_hora' => Carbon::now(),  // Fecha y hora actual
        ]);
    }

}
