<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Led extends Model
{
    use HasFactory;

    protected $table = 'leds';  // Nombre de la tabla en la base de datos
    protected $primaryKey = 'id';  // La clave primaria, si es diferente de 'id'
    protected $fillable = ['state'];  // Los campos que se pueden asignar masivamente

    public $timestamps = false;

}
