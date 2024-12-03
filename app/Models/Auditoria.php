<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    use HasFactory;

    protected $fillable = ['idUsuario', 'descripcion', 'fecha_hora'];
    
    protected $table = 'auditoria';  // Asegúrate de que coincida con el nombre de la tabla

    // Desactivar el manejo automático de created_at y updated_at
    public $timestamps = false;

    /**
     * Relación con el modelo User.
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'idUsuario');
    }
}
