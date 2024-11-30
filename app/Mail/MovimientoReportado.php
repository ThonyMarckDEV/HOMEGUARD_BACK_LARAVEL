<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MovimientoReportado extends Mailable
{
    use Dispatchable, Queueable;

    public $hora;
    public $fecha;

    // Constructor para pasar hora y fecha al correo
    public function __construct($hora, $fecha)
    {
        $this->hora = $hora;
        $this->fecha = $fecha;
    }

    // Método para construir el correo
    public function build()
    {
        return $this->subject('Movimiento Detectado')  // Agregar un asunto
                    ->view('emails.movimiento')    // Usar la vista del correo
                    ->with([
                        'hora' => $this->hora,    // Pasar hora al correo
                        'fecha' => $this->fecha,  // Pasar fecha al correo
                    ]);
    }
}

