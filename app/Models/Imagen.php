<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Imagen extends Model
{
    use HasFactory;
    
    protected $table = 'imagenes';  // Asegúrate de que coincida con el nombre de la tabla

    // Desactivar el manejo automático de created_at y updated_at
    public $timestamps = false;

    // Definir los campos que son asignables masivamente
    protected $fillable = ['ruta_imagen', 'fecha']; // Puedes agregar más campos si los necesitas

    // Definir el tipo de datos para la columna 'fecha' (opcional)
    protected $casts = [
        'fecha' => 'datetime',  // Asegura que se maneje como una fecha
    ];
}
