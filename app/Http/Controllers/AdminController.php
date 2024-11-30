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

     /**
     * Recibe los datos binarios de la imagen y la guarda en el servidor
     * además de registrar su ruta en la base de datos.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function uploadImage(Request $request)
    {
        // Leer los datos binarios desde php://input
        $imageData = file_get_contents('php://input');

        if ($imageData) {
            // Crear un nombre único para la imagen
            $imageName = uniqid() . '.jpg';

            // Definir la ruta donde se guardará la imagen
            $uploadDir = public_path('uploads/');
            $imagePath = $uploadDir . $imageName;

            // Asegurar que el directorio de subida exista
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true); // Crear la carpeta con permisos seguros
            }

            // Guardar la imagen en el servidor
            if (file_put_contents($imagePath, $imageData)) {
                // Registrar la ruta y la fecha en la base de datos
                $imageUrl = '/uploads/' . $imageName;

                $imagen = new Imagen();
                $imagen->ruta_imagen = $imageUrl;  // Ruta de la imagen
                $imagen->fecha = now();  // Fecha actual
                $imagen->save();

                return response()->json([
                    'message' => 'Imagen guardada correctamente.',
                    'image_path' => $imageUrl
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Error al guardar la imagen.'
                ], 500);
            }
        } else {
            return response()->json([
                'message' => 'No se recibió ninguna imagen.'
            ], 400);
        }
    }

     // Guardar movimiento y enviar correo a todos los usuarios
     public function reportarMovimiento(Request $request)
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
    public function perfilUsuario()
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
    public function uploadProfileImageUsuario(Request $request, $idUsuario)
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

    public function updateUsuario(Request $request, $idUsuario)
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

    public function registrarUsuarioAdmin(Request $request)
    {
        $messages = [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'nombres.required' => 'El nombre es obligatorio.',
            'apellidos.required' => 'Los apellidos son obligatorios.',
            'apellidos.regex' => 'Debe ingresar al menos dos apellidos separados por un espacio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe tener un formato válido.',
            'correo.unique' => 'El correo ya está registrado.',
            'edad.integer' => 'La edad debe ser un número entero.',
            'edad.between' => 'La edad debe ser mayor a 18.',
            'dni.digits' => 'El DNI debe tener exactamente 8 dígitos.',
            'dni.required' => 'El DNI es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe incluir al menos una mayúscula y un símbolo.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
        
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:usuarios|max:255',
            'nombres' => 'required|max:255',
            'apellidos' => 'required|regex:/^[a-zA-Z]+(?: [a-zA-Z]+){1,}$/',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/', // Al menos una mayúscula
                'regex:/[\W_]/', // Al menos un símbolo (caracter no alfanumérico)
            ],
            'dni' => 'required|digits:8', // Exactamente 8 dígitos
            'correo' => 'required|email|max:255',
        ], $messages);

        // Si la validación falla, devolver los errores en formato JSON
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
            ], 422); // Código de estado 422 para errores de validación
        }

        try {
            // Asignar valores predeterminados para campos opcionales
            $rol = $request->rol ?? 'familiar'; // Valor predeterminado 'familiar'
            $status = $request->status ?? 'loggedOff'; // Valor predeterminado 'loggedOff'
            $perfil = $request->perfil ?? null; // Si no se proporciona, será null
        
            // Crear usuario con contraseña hasheada y valores predeterminados
            $user = Usuario::create([
                'username' => $request->username,
                'nombres' => $request->nombres,
                'apellidos' => $request->apellidos,
                'dni' => $request->dni,
                'correo' => $request->correo,
                'edad' => $request->edad ?? null,
                'nacimiento' => $request->nacimiento ?? null,
                'sexo' => $request->sexo ?? null,
                'direccion' => $request->direccion ?? null,
                'telefono' => $request->telefono ?? null,
                'departamento' => $request->departamento ?? null,
                'password' => Hash::make($request->password),
                'status' => $status,
                'rol' => $rol,
                'perfil' => $perfil,
            ]);

            return response()->json([
                'message' => 'Usuario creado con éxito',
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Hubo un problema al crear el usuario.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    public function getSchedules()
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

    
    public function scheduleLights(Request $request)
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
        public function deleteSchedule($id)
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

        public function getImagenes()
        {
            // Obtener todas las imágenes ordenadas por fecha descendente
            $imagenes = Imagen::orderBy('fecha', 'desc')->get(['idImagen', 'ruta_imagen', 'fecha']);
            
            // Asegurarse de que las rutas sean completas, concatenando la URL base
            $imagenes = $imagenes->map(function ($imagen) {
                $imagen->ruta_imagen ;
                return $imagen;
            });

            return response()->json($imagenes);
        }

}
