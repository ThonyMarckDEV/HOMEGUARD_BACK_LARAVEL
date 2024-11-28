<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Led;
use App\Models\Vigilancia;

class AdminController extends Controller
{
    // Función para recibir y guardar el enlace
    public function obtenerLinkdelESP32(Request $request)
    {
        // Verificar que el enlace esté presente
        $request->validate([
            'link' => 'required|string|url',
        ]);

        $link = $request->input('link');

        // Guardar el enlace en un archivo (puedes usar Storage para gestionarlo)
        $file = 'stream_link.txt';
        Storage::disk('local')->put($file, $link);

        // Responder con éxito
        return response()->json([
            'message' => "Enlace recibido: $link"
        ]);
    }

    public function obtenerLinkStreamESP32Laravel()
    {
        // Ruta del archivo donde se guardó el enlace
        $file = 'stream_link.txt';
        
        // Verificamos si el archivo existe
        if (Storage::disk('local')->exists($file)) {
            // Leemos el contenido del archivo
            $link = Storage::disk('local')->get($file);

            // Respondemos con el enlace
            return response()->json([
                'link' => $link
            ]);
        }

        // Si el archivo no existe, respondemos con un error
        return response()->json([
            'message' => 'No se encontró el enlace.'
        ], 404);
    }


     // Función para recibir y guardar los logs
     public function storeLog(Request $request)
     {
         // Validar que el 'log' esté presente en la solicitud
         $request->validate([
             'log' => 'required|string',
         ]);
 
         // Obtener el log desde el cuerpo de la solicitud
         $log = $request->input('log');
         
         // Ruta del archivo donde se guardarán los logs
         $logFile = 'logs.txt';
 
         // Escribir el log en el archivo
         $currentDateTime = now()->toDateTimeString();
         $logEntry = $currentDateTime . ' - ' . $log . "\n";
         
         // Usamos Storage para guardar el log en el archivo
         Storage::disk('local')->append($logFile, $logEntry);
 
         // Responder con éxito
         return response()->json([
             'message' => 'Log recibido y guardado.'
         ]);
     }

     // Obtener los estados de los LEDs
     public function getLedStates()
     {
         // Obtener el estado de los LEDs con sus ids
         $leds = Led::all();
         
         // Transformar los datos en un formato adecuado
         $states = $leds->mapWithKeys(function ($led) {
             return [$led->id => (int)$led->state];  // Convertir el estado a número entero
         });
 
         return response()->json($states);
     }

      // Cambiar el estado de un LED
    public function updateLedState(Request $request, $id)
    {
        // Validar el estado (debe ser 0 o 1)
        $request->validate([
            'state' => 'required|in:0,1'
        ]);

        // Buscar el LED por su id
        $led = Led::findOrFail($id);

        // Actualizar el estado del LED
        $led->state = $request->input('state');
        $led->save();

        return response()->json(['message' => 'Estado del LED actualizado correctamente']);
    }

    public function getVigilanceStatus()
    {
        $vigilance = Vigilancia::first();  // Asumimos que solo hay un registro de vigilancia
    
        // Si hay un registro de vigilancia, devolver el estado como booleano
        if ($vigilance) {
            return response()->json(['is_active' => (bool)$vigilance->is_active]);
        }
    
        // Si no existe un registro de vigilancia, crear uno y devolver el estado como booleano
        $vigilance = Vigilancia::create(['is_active' => 0]); // 0 representa "false"
        return response()->json(['is_active' => (bool)$vigilance->is_active]);
    }

    /**
     * Actualizar el estado del modo vigilancia.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateVigilanceStatus(Request $request)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $vigilance = Vigilancia::first();

        if (!$vigilance) {
            // Si no existe, crear un nuevo registro
            $vigilance = Vigilancia::create(['is_active' => $request->is_active]);
        } else {
            // Si existe, actualizar el estado
            $vigilance->update(['is_active' => $request->is_active]);
        }

        return response()->json(['message' => 'Estado de vigilancia actualizado correctamente']);
    }


}
