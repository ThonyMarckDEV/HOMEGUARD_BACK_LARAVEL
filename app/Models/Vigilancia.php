<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vigilancia extends Model
{
    use HasFactory;

    // Definir la tabla asociada
    protected $table = 'vigilancia';

    public $timestamps = false;
    // Permitir la asignación masiva de campos
    protected $fillable = ['is_active'];

    // Definir las columnas que no deberían ser modificadas por asignación masiva
    protected $guarded = [];
}
