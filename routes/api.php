<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\FamiliarController;
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

        Route::post('/upload-image', [AdminController::class, 'uploadImage']);

        Route::post('/reportarMovimiento', [AdminController::class, 'reportarMovimiento']);

//================================================================================================


//================================================================================================
    //RUTAS PROTEGIDAS A
    // RUTAS PARA ADMINISTRADOR VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
    Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 

        Route::get('/obtenerLinkStreamESP32Laravel', [AdminController::class, 'obtenerLinkStreamESP32Laravel']);
       
        Route::post('vigilance/toggle', [AdminController::class, 'updateVigilanceStatus']); // Actualizar estado de vigilancia

        Route::get('perfilUsuario', [AdminController::class, 'perfilUsuario']);

        Route::post('uploadProfileImageUsuario/{idUsuario}', [AdminController::class, 'uploadProfileImageUsuario']);

        Route::put('/updateUsuario/{idUsuario}', [AdminController::class, 'updateUsuario']);

        Route::post('registrarUsuario', [AdminController::class, 'registrarUsuarioAdmin']);

        Route::get('/led/schedules', [AdminController::class, 'getSchedules']); // Obtener todas las programaciones
        
        Route::post('/led/schedule', [AdminController::class, 'scheduleLights']); // Programar luces

        Route::delete('/led/schedule/{id}', [AdminController::class, 'deleteSchedule']); // Eliminar programación
    });



     // RUTAS PARA ADMINISTRADOR VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
    Route::middleware(['auth.jwt', 'checkRoleMW:familiar'])->group(function () { 

        Route::get('/obtenerLinkStreamESP32LaravelFamiliar', [FamiliarController::class, 'obtenerLinkStreamESP32LaravelFamiliar']);
       
        Route::post('vigilance/toggleFamiliar', [FamiliarController::class, 'updateVigilanceStatusFamiliar']); // Actualizar estado de vigilancia

        Route::get('perfilUsuarioFamiliar', [FamiliarController::class, 'perfilUsuarioFamiliar']);
        
        Route::post('uploadProfileImageUsuarioFamiliar/{idUsuario}', [FamiliarController::class, 'uploadProfileImageUsuarioFamiliar']);

        Route::put('/updateUsuarioFamiliar/{idUsuario}', [FamiliarController::class, 'updateUsuarioFamiliar']);


        Route::get('/led/schedulesFamiliar', [FamiliarController::class, 'getSchedulesFamiliar']); // Obtener todas las programaciones
        
        Route::post('/led/scheduleFamiliar', [FamiliarController::class, 'scheduleLightsFamiliar']); // Programar luces

        Route::delete('/led/scheduleFamiliar/{id}', [FamiliarController::class, 'deleteScheduleFamiliar']); // Eliminar programación
    });


//================================================================================================

