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

        Route::post('/send-verification-codeUser', [AuthController::class, 'sendVerificationCodeUser']);

        Route::post('/verify-codeUser', [AuthController::class, 'verifyCodeUser']);
        
        Route::post('/change-passwordUser', [AuthController::class, 'changePasswordUser']);

//================================================================================================


//================================================================================================
    //RUTAS PROTEGIDAS A
    // RUTAS PARA ADMINISTRADOR VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
    Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 
       
    });



//================================================================================================

