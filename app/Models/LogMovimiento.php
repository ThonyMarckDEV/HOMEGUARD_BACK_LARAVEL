<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogMovimiento extends Model
{
    use HasFactory;

    // Definir los campos que pueden ser asignados masivamente
    protected $fillable = ['Hora', 'Fecha', 'imagen'];

    // Deshabilitar las marcas de tiempo automáticas si no las deseas
    public $timestamps = false;
}
