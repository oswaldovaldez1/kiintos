<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/




/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::middleware('auth:api')->get('/user', function (Request $request) {
    abort(404);
});
//usuario
Route::group(['namespace'=>'API'], function(){        
    Route::group(['prefix'=>'v1'], function (){
       Route::group(['prefix' => 'usuarios'], function() {
        //SERVER_LOGIN->terminado
        Route::post('autenticar','UserController@login')->middleware('throttle:999999,1');
        //SERVER_LOGIND
        Route::post('autenticard','UserController@logind')->middleware('throttle:999999,1');
        //SERVER_REFRESH->terminado
        Route::post('datos','UserController@info')->middleware('throttle:999999,1');
        //SERVER_LOGOUT->terminado
        Route::post('cerrar','UserController@close')->middleware('throttle:999999,1');
        //SERVER_ADDUSER->terminado
        Route::post('registro','UserController@newuser')->middleware('throttle:999999,1');
        //SERVER_FORGOTPASS->terminado
        Route::post('recuperar','UserController@restore')->middleware('throttle:999999,1');
        //SERVER_PROFILE->terminado
        Route::post('getperfil','UserController@viewprofile')->middleware('throttle:999999,1');
        //SERVER_SAVEPROFILE->terminado
        Route::post('setperfil','UserController@updateprofile')->middleware('throttle:999999,1');        
        //SERVER_SENDCONTACT->terminado
        Route::post('buscar','UserController@searchuser')->middleware('throttle:999999,1');
        //SERVER_RECORD->terminado
        Route::post('historial','UserController@record')->middleware('throttle:999999,1');
        //SERVER_NOTIFICACIONES->terminado
        Route::post('notificaciones','UserController@notice')->middleware('throttle:999999,1');
        //SERVER_SHOWSTORE->terminado
        Route::post('buscartiendas','UserController@searchstore')->middleware('throttle:999999,1');
        //SERVER_CONFIRM->terminado
        Route::post('respuesta','UserController@answer')->middleware('throttle:999999,1');        
        
 /*________________________________________________________________________________________________________________*/
        //SERVER_LOGIN->terminado
        Route::get('autenticar', function (){
            abort(404);
        });           
        //SERVER_REFRESH->terminado
        Route::get('datos', function (){
            abort(404);
        });
        //SERVER_LOGOUT->terminado
        Route::get('cerrar', function (){
            abort(404);
        });
        //SERVER_ADDUSER->terminado
        Route::get('registro',function (){
            abort(404);
        });
        //SERVER_FORGOTPASS->terminado
        Route::get('recuperar',function (){
            abort(404);
        });
        //SERVER_PROFILE->terminado
        Route::get('getperfil',function (){
            abort(404);
        });
        //SERVER_SAVEPROFILE->terminado
        Route::get('setperfil',function (){
            abort(404);
        });
        //SERVER_SENDCONTACT->terminado
        Route::get('buscar',function (){
            abort(404);
        });
        //SERVER_RECORD->terminado
        Route::get('historial',function (){
            abort(404);
        });
        //SERVER_NOTIFICACIONES->terminado
        Route::get('notificaciones',function (){
            abort(404);
        });
        //SERVER_SHOWSTORE->terminado
        Route::get('buscartiendas',function (){
            abort(404);
        });
        //SERVER_CONFIRM->terminado
        Route::get('respuesta',function (){
            abort(404);
        });
    });
    
    //imagenes
    Route::group(['prefix'=>'image'], function (){
        //SERVER_UPLOADFILE->terminado
        Route::post('setimage','UserController@imagesaveprofile')->middleware('throttle:999999,1');
        //SERVER_DOWNFILE->terminado
        Route::get('getimage/{image}','UserController@imagegetprofile')->middleware('throttle:999999,1');
    });
    
    //transacciones
    Route::group(['prefix'=>'transaccion'], function (){
        //SERVER_GETCOD->terminado
        Route::post('getcode','TransactionController@genereratecode')->middleware('throttle:999999,1');
        //SERVER_GETCODF
        Route::post('getcodes','TransactionController@genereratecodef')->middleware('throttle:999999,1');
        //SERVER_SETCOD
        Route::post('setcode','TransactionController@readcode')->middleware('throttle:999999,1');
        //SERVER_TRANCOB
        Route::post('cobrar','TransactionController@charge')->middleware('throttle:999999,1');
        //SERVER_TRANPAG
        Route::post('pagar','TransactionController@pay')->middleware('throttle:999999,1');
        //SERVER_TRANS->terminado
        Route::post('depositar','TransactionController@paymet')->middleware('throttle:999999,1');
        //SERVER_DRAW->terminar
        Route::post('retirar','TransactionController@payout')->middleware('throttle:999999,1');
        //SERVER_OPNY->terminado
        Route::post('paypal','TransactionController@credentials')->middleware('throttle:999999,1');
        //saldos        
    });    
        Route::group(['prefix'=>'admin'], function (){
            Route::post('validar','UserController@validaradmin');
            Route::get('usuarios','UserController@getalluser');
        });
    });
    //v2
    Route::group(['prefix'=>'v2'], function (){
       Route::group(['prefix' => 'usuarios'], function() {
        //SERVER_LOGIN->terminado
        Route::post('autenticar','UserController@login')->middleware('throttle:999999,1');
        //SERVER_LOGIND
        Route::post('autenticard','UserController@logind')->middleware('throttle:999999,1');
        //SERVER_REFRESH->terminado
        Route::post('datos','UserController@info')->middleware('throttle:999999,1');
        //SERVER_LOGOUT->terminado
        Route::post('cerrar','UserController@close')->middleware('throttle:999999,1');
        //SERVER_ADDUSER->terminado
        Route::post('registro','UserController@newuser')->middleware('throttle:999999,1');
        //SERVER_FORGOTPASS->terminado
        Route::post('recuperar','UserController@restore')->middleware('throttle:999999,1');
        //SERVER_PROFILE->terminado
        Route::post('getperfil','UserController@viewprofile')->middleware('throttle:999999,1');
        //SERVER_SAVEPROFILE->terminado
        Route::post('setperfil','UserController@updateprofile')->middleware('throttle:999999,1');        
        //SERVER_SENDCONTACT->terminado
        Route::post('buscar','UserController@searchuser')->middleware('throttle:999999,1');
        //SERVER_RECORD->terminado
        Route::post('historial','UserController@record')->middleware('throttle:999999,1');
        //SERVER_NOTIFICACIONES->terminado
        Route::post('notificaciones','UserController@notice')->middleware('throttle:999999,1');
        //SERVER_SHOWSTORE->terminado
        Route::post('buscartiendas','UserController@searchstore')->middleware('throttle:999999,1');
        //SERVER_CONFIRM->terminado
        Route::post('respuesta','UserController@answer')->middleware('throttle:999999,1');        
        
 /*________________________________________________________________________________________________________________*/
        //SERVER_LOGIN->terminado
        Route::get('autenticar', function (){
            abort(404);
        });           
        //SERVER_REFRESH->terminado
        Route::get('datos', function (){
            abort(404);
        });
        //SERVER_LOGOUT->terminado
        Route::get('cerrar', function (){
            abort(404);
        });
        //SERVER_ADDUSER->terminado
        Route::get('registro',function (){
            abort(404);
        });
        //SERVER_FORGOTPASS->terminado
        Route::get('recuperar',function (){
            abort(404);
        });
        //SERVER_PROFILE->terminado
        Route::get('getperfil',function (){
            abort(404);
        });
        //SERVER_SAVEPROFILE->terminado
        Route::get('setperfil',function (){
            abort(404);
        });
        //SERVER_SENDCONTACT->terminado
        Route::get('buscar',function (){
            abort(404);
        });
        //SERVER_RECORD->terminado
        Route::get('historial',function (){
            abort(404);
        });
        //SERVER_NOTIFICACIONES->terminado
        Route::get('notificaciones',function (){
            abort(404);
        });
        //SERVER_SHOWSTORE->terminado
        Route::get('buscartiendas',function (){
            abort(404);
        });
        //SERVER_CONFIRM->terminado
        Route::get('respuesta',function (){
            abort(404);
        });
    });
    
    //imagenes
    Route::group(['prefix'=>'image'], function (){
        //SERVER_UPLOADFILE->terminado
        Route::post('setimage','UserController@imagesaveprofile')->middleware('throttle:999999,1');
        //SERVER_DOWNFILE->terminado
        Route::get('getimage/{image}','UserController@imagegetprofile')->middleware('throttle:999999,1');
    });
    
    //transacciones
    Route::group(['prefix'=>'transaccion'], function (){
        //SERVER_GETCOD->terminado
        Route::post('getcode','TransactionController@genereratecode')->middleware('throttle:999999,1');
        //SERVER_GETCODF
        Route::post('getcodes','TransactionController@genereratecodef')->middleware('throttle:999999,1');
        //SERVER_SETCOD
        Route::post('setcode','TransactionController@readcode')->middleware('throttle:999999,1');
        //SERVER_TRANCOB
        Route::post('cobrar','TransactionController@charge')->middleware('throttle:999999,1');
        //SERVER_TRANPAG
        Route::post('pagar','TransactionController@pay')->middleware('throttle:999999,1');
        //SERVER_TRANS->terminado
        Route::post('depositar','TransactionController@paymet')->middleware('throttle:999999,1');
        //SERVER_DRAW->terminar
        Route::post('retirar','TransactionController@payout')->middleware('throttle:999999,1');
        //SERVER_OPNY->terminado
        Route::post('paypal','TransactionController@credentials')->middleware('throttle:999999,1');
        //saldos        
    });    
        Route::group(['prefix'=>'admin'], function (){
            Route::post('validar','UserController@validaradmin');
            Route::get('usuarios','UserController@getalluser');
        });
    });
    Route::group(['prefix' => 'usuarios'], function() {
        //SERVER_LOGIN->terminado
        Route::post('autenticar','UserController@login')->middleware('throttle:999999,1');
        //SERVER_LOGIND
        Route::post('autenticard','UserController@logind')->middleware('throttle:999999,1');
        //SERVER_REFRESH->terminado
        Route::post('datos','UserController@info')->middleware('throttle:999999,1');
        //SERVER_LOGOUT->terminado
        Route::post('cerrar','UserController@close')->middleware('throttle:999999,1');
        //SERVER_ADDUSER->terminado
        Route::post('registro','UserController@newuser')->middleware('throttle:999999,1');
        //SERVER_FORGOTPASS->terminado
        Route::post('recuperar','UserController@restore')->middleware('throttle:999999,1');
        //SERVER_PROFILE->terminado
        Route::post('getperfil','UserController@viewprofile')->middleware('throttle:999999,1');
        //SERVER_SAVEPROFILE->terminado
        Route::post('setperfil','UserController@updateprofile')->middleware('throttle:999999,1');        
        //SERVER_SENDCONTACT->terminado
        Route::post('buscar','UserController@searchuser')->middleware('throttle:999999,1');
        //SERVER_RECORD->terminado
        Route::post('historial','UserController@record')->middleware('throttle:999999,1');
        //SERVER_NOTIFICACIONES->terminado
        Route::post('notificaciones','UserController@notice')->middleware('throttle:999999,1');
        //SERVER_SHOWSTORE->terminado
        Route::post('buscartiendas','UserController@searchstore')->middleware('throttle:999999,1');
        //SERVER_CONFIRM->terminado
        Route::post('respuesta','UserController@answer')->middleware('throttle:999999,1');        
        Route::get('paso','UserController@paso');
 /*________________________________________________________________________________________________________________*/
        //SERVER_LOGIN->terminado
        Route::get('autenticar', function (){
            abort(404);
        });           
        //SERVER_REFRESH->terminado
        Route::get('datos', function (){
            abort(404);
        });
        //SERVER_LOGOUT->terminado
        Route::get('cerrar', function (){
            abort(404);
        });
        //SERVER_ADDUSER->terminado
        Route::get('registro',function (){
            abort(404);
        });
        //SERVER_FORGOTPASS->terminado
        Route::get('recuperar',function (){
            abort(404);
        });
        //SERVER_PROFILE->terminado
        Route::get('getperfil',function (){
            abort(404);
        });
        //SERVER_SAVEPROFILE->terminado
        Route::get('setperfil',function (){
            abort(404);
        });
        //SERVER_SENDCONTACT->terminado
        Route::get('buscar',function (){
            abort(404);
        });
        //SERVER_RECORD->terminado
        Route::get('historial',function (){
            abort(404);
        });
        //SERVER_NOTIFICACIONES->terminado
        Route::get('notificaciones',function (){
            abort(404);
        });
        //SERVER_SHOWSTORE->terminado
        Route::get('buscartiendas',function (){
            abort(404);
        });
        //SERVER_CONFIRM->terminado
        Route::get('respuesta',function (){
            abort(404);
        });
    });
    
    //imagenes
    Route::group(['prefix'=>'image'], function (){
        //SERVER_UPLOADFILE->terminado
        Route::post('setimage','UserController@imagesaveprofile')->middleware('throttle:999999,1');
        //SERVER_DOWNFILE->terminado
        Route::get('getimage/{image}','UserController@imagegetprofile')->middleware('throttle:999999,1');
    });
    
    //transacciones
    Route::group(['prefix'=>'transaccion'], function (){
        //SERVER_GETCOD->terminado
        Route::post('getcode','TransactionController@genereratecode')->middleware('throttle:999999,1');
        //SERVER_GETCODF
        Route::post('getcodes','TransactionController@genereratecodef')->middleware('throttle:999999,1');
        //SERVER_SETCOD
        Route::post('setcode','TransactionController@readcode')->middleware('throttle:999999,1');
        //SERVER_TRANCOB
        Route::post('cobrar','TransactionController@charge')->middleware('throttle:999999,1');
        //SERVER_TRANPAG
        Route::post('pagar','TransactionController@pay')->middleware('throttle:999999,1');
        //SERVER_TRANS->terminado
        Route::post('depositar','TransactionController@paymet')->middleware('throttle:999999,1');
        //SERVER_DRAW->terminar
        Route::post('retirar','TransactionController@payout')->middleware('throttle:999999,1');
        //SERVER_OPNY->terminado
        Route::post('paypal','TransactionController@credentials')->middleware('throttle:999999,1');
        //saldos        
    });    
        Route::group(['prefix'=>'admin'], function (){
            Route::post('validar','UserController@validaradmin');
            Route::get('usuarios','UserController@getalluser');
        });
        
});










