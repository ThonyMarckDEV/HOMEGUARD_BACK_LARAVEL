<?php

namespace App\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    protected $twilio;

    public function __construct()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->twilio = new Client($sid, $token);
    }

    public function sendWhatsAppMessage($to, $message)
    {
        $from = env('TWILIO_WHATSAPP_FROM');  // Número de WhatsApp de Twilio (sandbox o tu número de Twilio)
        
        try {
            // Enviar el mensaje
            $message = $this->twilio->messages
                ->create(
                    'whatsapp:' . $to, // Número de destino
                    [
                        'from' => $from,  // Número de WhatsApp de Twilio
                        'body' => $message, // Cuerpo del mensaje
                    ]
                );
            
            // Registra el SID para verificar que el mensaje fue enviado
            Log::info("Mensaje enviado con SID: " . $message->sid);
            
            return $message->sid;  // Retorna el SID del mensaje
        } catch (\Exception $e) {
            Log::error("Error al enviar el mensaje de WhatsApp: " . $e->getMessage());
            return null;  // Retorna null si hubo un error
        }
    }
}
