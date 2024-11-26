<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Carrito;
use App\Models\Pedido;
use App\Models\Pago;
use App\Models\DetalleDireccionPedido;
use Illuminate\Http\Request;
use App\Mail\NotificacionPagoCompletado;
use App\Mail\NotificacionPedidoEliminado;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use FPDF;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;


class AdminController extends Controller
{
   
}
