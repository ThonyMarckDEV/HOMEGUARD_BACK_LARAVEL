<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Led;
use App\Models\Vigilancia;
use Illuminate\Support\Str;
use App\Models\Imagen;
use App\Models\LogMovimiento;
use App\Models\Usuario;
use App\Mail\MovimientoReportado;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


class FamiliarController extends Controller
{
    // Función para recibir y guardar el enlace
    public function obtenerLinkdelESP32Familiar(Request $request)
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

    public function obtenerLinkStreamESP32LaravelFamiliar()
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
     public function storeLogFamiliar(Request $request)
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
     public function getLedStatesFamiliar()
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
    public function updateLedStateFamiliar(Request $request, $id)
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

    public function getVigilanceStatusFamiliar()
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
    public function updateVigilanceStatusFamiliar(Request $request)
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

     /**
     * Recibe los datos binarios de la imagen y la guarda en el servidor
     * además de registrar su ruta en la base de datos.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadImageFamiliar(Request $request)
    {
        // Verificar si se recibe una imagen en el request
        if ($request->hasFile('image')) {
            // Obtener los datos de la imagen
            $image = $request->file('image');

            // Crear un nombre único para la imagen
            $imageName = Str::uuid() . '.jpg';

            // Guardar la imagen en el directorio 'uploads'
            $imagePath = $image->storeAs('uploads', $imageName, 'public');

            // Registrar la ruta de la imagen en la base de datos
            $imagen = new Imagen();
            $imagen->ruta_imagen = $imagePath;
            $imagen->save();

            return response()->json([
                'message' => 'Imagen guardada correctamente.',
                'image_path' => $imagePath
            ]);
        }

        return response()->json(['message' => 'No se recibió ninguna imagen.'], 400);
    }

     // Guardar movimiento y enviar correo a todos los usuarios
     public function reportarMovimientoFamiliar(Request $request)
     {
         // Obtener la hora y la fecha actual
         $hora_actual = now()->format('H:i:s');
         $fecha_actual = now()->format('Y-m-d');
 
         // Insertar el reporte en la tabla logMovimiento
         $logMovimiento = LogMovimiento::create([
             'Hora' => $hora_actual,
             'Fecha' => $fecha_actual,
             'imagen' => null,  // Agregar la lógica para manejar las imágenes si es necesario
         ]);
 
         // Obtener todos los correos de los usuarios
         $usuarios = Usuario::all();
 
         // Enviar correo a todos los usuarios
         try {
             foreach ($usuarios as $usuario) {
                 Mail::to($usuario->correo)->send(new MovimientoReportado($hora_actual, $fecha_actual));
             }
 
             return response()->json(['message' => 'Reporte registrado y correos enviados a todos los usuarios con éxito.']);
         } catch (\Exception $e) {
             return response()->json(['message' => 'Error al enviar los correos: ' . $e->getMessage()], 500);
         }
     }


     // En EstudianteController.php
    public function perfilUsuarioFamiliar()
    {
        $usuario = Auth::user();
        $profileUrl = $usuario->perfil ? url("{$usuario->perfil}") : null;

        return response()->json([
            'success' => true,
            'data' => [
                'idUsuario' => $usuario->idUsuario,
                'username' => $usuario->username,
                'nombres' => $usuario->nombres,
                'apellidos' => $usuario->apellidos,
                'dni' => $usuario->dni,
                'correo' => $usuario->correo,
                'edad' => $usuario->edad,
                'nacimiento' => $usuario->nacimiento,
                'sexo' => $usuario->sexo,
                'direccion' => $usuario->direccion,
                'telefono' => $usuario->telefono,
                'departamento' => $usuario->departamento,
                'perfil' => $profileUrl,  // URL completa de la imagen de perfil
            ]
        ]);
    }
    public function uploadProfileImageUsuarioFamiliar(Request $request, $idUsuario)
    {
        $admin = Usuario::find($idUsuario);
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }

        // Verifica si hay un archivo en la solicitud
        if ($request->hasFile('perfil')) {
            // Ruta donde se va a guardar la imagen dentro de 'public/storage/profiles/$idUsuario'
            $path = public_path("profiles/$idUsuario"); // Utiliza public_path para asegurarte que esté en la carpeta pública

            // Si el directorio no existe, crea el directorio
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            // Elimina la imagen de perfil existente si existe
            if ($admin->perfil && file_exists(public_path($admin->perfil))) {
                unlink(public_path($admin->perfil)); // Elimina el archivo actual
            }

            // Obtén el archivo y guarda la imagen en la carpeta 'public/storage/profiles/$idUsuario'
            $filename = $request->file('perfil')->getClientOriginalName();
            $request->file('perfil')->move($path, $filename); // Mueve el archivo al directorio público

            // Guarda la ruta relativa del archivo en la base de datos
            $admin->perfil = "profiles/$idUsuario/$filename";
            $admin->save();

            return response()->json(['success' => true, 'filename' => $filename]);
        }

        return response()->json(['success' => false, 'message' => 'No se cargó la imagen'], 400);
    }

    public function updateUsuarioFamiliar(Request $request, $idUsuario)
    {
        try {
            // Buscar el usuario por su ID
            $usuario = Usuario::findOrFail($idUsuario);

            // Actualizar los campos que se envían en el request
            if ($request->has('username')) {
                $usuario->username = $request->input('username');
            }

            if ($request->has('email')) {
                $usuario->email = $request->input('email');
            }

            if ($request->has('edad')) {
                $usuario->edad = $request->input('edad');
            }

            if ($request->has('nacimiento')) {
                $usuario->nacimiento = $request->input('nacimiento');
            }

            if ($request->has('sexo')) {
                $usuario->sexo = $request->input('sexo');
            }

            if ($request->has('direccion')) {
                $usuario->direccion = $request->input('direccion');
            }

            if ($request->has('telefono')) {
                $usuario->telefono = $request->input('telefono');
            }

            if ($request->has('departamento')) {
                $usuario->departamento = $request->input('departamento');
            }

            // Guardar los cambios en la base de datos
            $usuario->save();

            return response()->json([
                'message' => 'Usuario actualizado con éxito',
                'data' => $usuario
                
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getSchedulesFamiliar()
    {
        // Obtener todas las programaciones de luces donde hora_encendido y hora_apagado no son null
        $schedules = led::whereNotNull('hora_encendido')
                        ->whereNotNull('hora_apagado')
                        ->orderBy('id', 'asc') // Para ordenar las programaciones por el ID
                        ->get();
        
        // Si no se encuentran programaciones
        if ($schedules->isEmpty()) {
            return response()->json(['message' => 'No hay programaciones disponibles.'], 404);
        }
    
        // Mapeamos los resultados para agregar el nombre de la luz según el led_id
        $schedules = $schedules->map(function($schedule) {
            $schedule->luz_nombre = ($schedule->led_id == 1) ? 'Luz Patio' : 'Luz Casa';
            return $schedule;
        });
    
        return response()->json($schedules);
    }

    
    public function scheduleLightsFamiliar(Request $request)
    {
        try {
            // Validación previa para asegurarse de que los valores estén en el formato correcto
            $validated = $request->validate([
                'hora_encendido' => 'required|date_format:H:i:s',
                'hora_apagado' => 'required|date_format:H:i:s',
                'id' => 'required|in:1,2',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Enviar la respuesta de error si la validación falla
            return response()->json(['error' => $e->errors()], 400);
        }
    
        // Log de datos validados
        Log::info('Datos validados:', $validated);
    
        // Buscar el LED específico por su ID (1 para Luz Patio, 2 para Luz Casa)
        $led = Led::find($validated['id']);
        
        if (!$led) {
            Log::error('No se encontró el LED con ID: ' . $validated['id']);
            return response()->json(['error' => 'No se encontró el LED especificado.'], 404);
        }
    
        // Verificar si ya existe una programación activa para el LED
        Log::info('LED encontrado:', $led->toArray());
    
        // Eliminar programación existente, si existe
        if ($led->hora_encendido && $led->hora_apagado) {
            Log::info('Eliminando programación activa para el LED con ID: ' . $validated['id']);
            $led->hora_encendido = null;
            $led->hora_apagado = null;
            $led->save();
            
            Log::info('Programación eliminada para el LED:', $led->toArray());
        }
    
        // Actualizar la programación con las nuevas horas
        Log::info('Actualizando programación para el LED con ID: ' . $validated['id']);
        $led->hora_encendido = $validated['hora_encendido'];
        $led->hora_apagado = $validated['hora_apagado'];
        $led->state = 0;  // Estado inicial apagado
        $led->save();
    
        // Verificar si se guardó correctamente
        Log::info('Programación actualizada para el LED:', $led->toArray());
    
        // Regresar la respuesta en formato JSON
        return response()->json(['message' => 'Programación de luces actualizada exitosamente.'], 200)
            ->header('Content-Type', 'application/json');
    }       


      // Eliminar solo la programación de las luces (no el LED completo)
        public function deleteScheduleFamiliar($id)
        {
            // Buscar la programación del LED
            $schedule = Led::find($id);  
        
            if (!$schedule) {
                return response()->json(['error' => 'Programación no encontrada.'], 404);
            }
        
            // Poner las horas de encendido y apagado a null
            $schedule->hora_encendido = null;
            $schedule->hora_apagado = null;

            // Guardar los cambios en la base de datos
            $schedule->save();
        
            return response()->json(['message' => 'Programación eliminada exitosamente.'], 200);
        }

}
