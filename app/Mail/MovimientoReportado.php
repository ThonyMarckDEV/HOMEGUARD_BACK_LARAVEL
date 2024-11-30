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

    public function __construct($hora, $fecha)
    {
        $this->hora = $hora;
        $this->fecha = $fecha;
    }

    public function build()
    {
        return $this->view('emails.movimiento')
                    ->with([
                        'hora' => $this->hora,
                        'fecha' => $this->fecha,
                    ]);
    }
}
