<?php

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

//RUTAS

//================================================================================================
        //RUTAS  AUTH

        //RUTA PARA QUE LOS USUAIOS SE LOGEEN POR EL CONTROLLADOR AUTHCONTROLLER
        Route::post('login', [AuthController::class, 'login']);

        Route::post('logout', [AuthController::class, 'logout']);

        Route::post('refresh-token', [AuthController::class, 'refreshToken']);

        Route::post('update-activity', [AuthController::class, 'updateLastActivity']);

        Route::post('/check-status', [AuthController::class, 'checkStatus']);

        Route::post('/send-message', [AuthController::class, 'sendContactEmail']);

        Route::post('/mandarLinkStreamESP32', [AdminController::class, 'obtenerLinkdelESP32']);

        Route::post('/store-log', [AdminController::class, 'storeLog']);

        Route::get('/getLedStates', [AdminController::class, 'getLedStates']);

        Route::post('/updateLedState/{id}', [AdminController::class, 'updateLedState']); // Actualizar el estado de un LED

        Route::get('vigilance/status', [AdminController::class, 'getVigilanceStatus']);  // Obtener estado de vigilancia


//================================================================================================


//================================================================================================
    //RUTAS PROTEGIDAS A
    // RUTAS PARA ADMINISTRADOR VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
    Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 

        Route::get('/obtenerLinkStreamESP32Laravel', [AdminController::class, 'obtenerLinkStreamESP32Laravel']);
       
        Route::post('vigilance/toggle', [AdminController::class, 'updateVigilanceStatus']); // Actualizar estado de vigilancia
    });



//================================================================================================

