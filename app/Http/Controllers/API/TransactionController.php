<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Mail;
use PayPal\Api\Payment;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

/**/
use App\Mail\SoporteKiintos;
/*modelos*/
use App\Models\Transaccione;
use App\Models\Usuario;
use App\Models\Notificacion;
use App\Models\Saldo;

use App\Http\Controllers\API\RecompensasController;

class TransactionController extends Controller
{
    public $successStatus = 200;
    public $unauthorizedStatus=401;
    public $notfoudStatus=404;
/*-----------------------------------------------------------------------------------------------------*/
    //getcode
    public function genereratecode(){

        $validator = \Validator::make(request()->header(),["token.*"    => "required|min:0","cantidad.*"=>"required|min:0"],["token.required"=>"Ingrese su Token","cantidad.required"=>"Ingrese la cantidad a retirar"]);
         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
         $usuario= Usuario::where('token','=', request()->header('token'));

        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->unauthorizedStatus],$this->unauthorizedStatus,[],JSON_UNESCAPED_SLASHES);
        }
        $tipo= request()->header('tipo');

        if($tipo===null){
            $tipo='pagar';
        }
        else{
            if($tipo!='cobrar'){
                return response()->json(['message'=>'falta tipo de transacción','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
        if($tipo==='pagar'){
            $saldo= Saldo::find($usuario->get()->toArray()[0]['id']);
            if(((double)$this->desencriptar($saldo->saldo))<(double)$this->remplazar(request()->header('cantidad'))){
                return response()->json(['message'=>'Su saldo no puede cubrir este pago','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
        $informacion=[
            'id'=>$usuario->get()->toArray()[0]['id'],
            'cantidad'=> $this->remplazar(request()->header('cantidad')),
            'elaborado'=>time(),
            'tipo'=>$tipo
        ];
        $cadena= $this->encriptar($usuario->get()->toArray()[0]['crypt'], json_encode($informacion));
        $codigos=new \App\Models\Codes();
        $codigos->codes=$cadena;
        $codigos->status=false;
        $codigos->fecha=date("Y-m-d H:i:s");
        $codigos->save();
        $codigos= \App\Models\Codes::where('codes','=',$cadena)->get()->toArray()[0];

        return response()->json(['cadena'=>encrypt(json_encode(['id'=>$codigos['id'],'tipo'=>'n']))],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
    }
    //getcodes
    public function genereratecodef(){

        $validator = \Validator::make(request()->header(),["token.*"=> "required|min:0","cantidad.*"=>"required|min:0","desc.*"=>"required|min:0","tiempo.*"=>"required|min:0","tiempo.*"=>"required|min:0"],["token.required"=>"Ingrese su Token","cantidad.required"=>"Ingrese la cantidad a retirar","desc.required"=>"Ingrese Una descripción","tiempo.required"=>"Ingrese el intervalo de tiempo entre cada escaneo","tiempo.required"=>"Ingrese el id del negocio"]);


         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }

        $informacion=array(
            "id"=> request()->header('id'),
            "cantidad"=> request()->header("cantidad"),
            "intervalo"=> request()->header("tiempo"),
            "desc"=> request()->header("desc")
        );
        $cadena=encrypt(json_encode($informacion),JSON_UNESCAPED_SLASHES);
        $codigos=new \App\Models\codef();
        $codigos->codigo=$cadena;
        $codigos->status=TRUE;
        $codigos->save();

        $codigos= \App\Models\codef::where('codigo','=',$cadena)->get()->toArray()[0];
        return response()->json(['cadena'=> encrypt(json_encode(["id"=>$codigos['id'],'tipo'=>'f_e']))],$this->successStatus,[],JSON_UNESCAPED_SLASHES);

    }
    //setcode
    public function readcode(){
        $validator = \Validator::make(request()->header(),["token.*"=> "required|min:0",'cadena.*'=>'required|min:0'],["token.required"=>"Ingrese su Token",'cadena.*'=>'Ingrese la cadena']);

         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
        $usuario= Usuario::where('token','=', request()->header('token'));

        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
        }

            $cadena= json_decode(decrypt(request()->header('cadena')), true);

            if($cadena['tipo']==='n'){
                        $codigos= \App\Models\Codes::where('id','=', $cadena['id']);
                if($codigos->count()===0){
                    return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }
                $codigos=$codigos->get()->toArray()[0];
                $cadenaqr= json_decode($this->desencriptar($codigos['codes']),true);

                if($usuario->get()->toArray()[0]['id']=== $cadenaqr['id']){
                    return response()->json(['message'=>'No se puede realizar la transaccion a la misma cuenta','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }

                if($codigos['status']===1){
                   return response()->json(['message'=>'este codigo ya fue utilizado anteriormente','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }

                if((time()-$cadenaqr['elaborado'])>300){
                    \App\Models\Codes::where('id','=', $cadena['id'])->update(['status'=>TRUE]);
                    return response()->json(['message'=>'su qr ha expirado','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }
                        \App\Models\Codes::where('id','=', $cadena['id'])->update(['status'=>TRUE]);
                if($cadenaqr['tipo']==='pagar'){

                    $emisor= Usuario::where('id','=',$cadenaqr['id'])->get()->toArray()[0];

                    $this->pagos($emisor, $usuario->get()->toArray()[0], $cadenaqr['cantidad'], 'P', 'C', 'R');

                    return response()->json(['message'=>'Transacción Realizada','status'=>$this->successStatus],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
                }
                else {

                    $saldo= Saldo::find($usuario->get()->toArray()[0]['id']);

                        if(((double) $this->remplazar($cadenaqr['cantidad']))>((double) $this->desencriptar($saldo->saldo))){
                               return response()->json(['message'=>'la transaccion no puede ser completada por falta de saldo en tu cuenta','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                        }
                    $receptor= Usuario::where('id','=',$cadenaqr['id'])->get()->toArray()[0];
                    $this->pagos($usuario->get()->toArray()[0], $receptor, $cadenaqr['cantidad'], 'C', 'P', 'R');

                    return response()->json(['message'=>'Transacción Realizada','status'=>$this->successStatus],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
                }
            }
            else if($cadena['tipo']==='f_e'){
                $codigos= \App\Models\codef::where('id','=',$cadena['id']);
                if($codigos->count()<=0){
                    return response()->json(['message'=>'Este código ya no es valido','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }
                $codigos=$codigos->get()->toArray()[0];

                if($codigos['status']===false){
                    return response()->json(['message'=>'Este código ya no es valido','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }
                $param= json_decode(decrypt($codigos['codigo']),true);

                if($param['id']===$usuario->get()->toArray()[0]['id']){
                    return response()->json(['message'=>'No puedes escanear un qr generado por ti','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }
                $res= \App\Models\Detalleusuario::where('institucion','like','%.'.$param['id'].'.%')->where('idcuenta','=',$usuario->get()->toArray()[0]['id']);

                if($res->count()===0){
                    return response()->json(['message'=>'Lo sentimos Usted no tiene autorización para escanear éste código Qr','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }

                $saldo= Saldo::find($usuario->get()->toArray()[0]['id']);

                        if(((double) $this->remplazar($param['cantidad']))>((double) $this->desencriptar($saldo->saldo))){
                               return response()->json(['message'=>'la transaccion no puede ser completada por falta de saldo en tu cuenta','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                        }

                $res= Transaccione::where('descripcion','=',$param['desc'])->orderby('id','DESC')->take(1);
                $uscan=false;
                if($res->count()>0){
                    $fecha= new \DateTime(date("Y-m-d H:i:s"));
                    $fecha2=new \DateTime($res->get()->toArray()[0]['fecha']);
                    $diff=$fecha->diff($fecha2);
                    if(($diff->days)>=1){
                        $uscan=true;
                    }
                    else if(($diff->i)>=$param['intervalo']){
                        $uscan=true;
                    }
                }
                else{
                    $uscan=true;
                }
                if(!$uscan){
                    return response()->json(['message'=>'Por normativa, debes esperar al menos '.($param['intervalo']===1)?'minuto ':'minutos '.' para poder escanear otro código Qr','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
                }
                $receptor= Usuario::where('id','=',$param['id'])->get()->toArray()[0];
                $this->pagos2($usuario->get()->toArray()[0],$receptor, $param['cantidad'],'P', 'C', 'R',$param['desc']);
            return response()->json(['message'=>'Transacción Realizada','status'=>$this->successStatus],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
            }
            else if($cadena['tipo']==='f_i'){

            }

    }
    //cobrar
    public function charge(){
        $validator = \Validator::make(request()->header(),["token.*"    => "required|min:0","clabe.*"=>"required|min:0|size:18|numeric","cantidad.*"=>"required|min:0","id.*"=>"required|min:0|numeric"],["token.required"=>"Ingrese su Token","id.required"=>"Ingrese su id del receptor","cantidad.required"=>"Ingrese la cantidad"]);
         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
         $usuario= Usuario::where('token','=', request()->header('token'));
        //dd($usuario->get()->toArray());
        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->unauthorizedStatus],$this->unauthorizedStatus,[],JSON_UNESCAPED_SLASHES);
        }

        if($usuario->get()->toArray()[0]['id']=== (int)request()->header('id')){
            return response()->json(['message'=>'No se puede realizar la transaccion a la misma cuenta','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
        }
             $receptor= Usuario::where('id','=',(int)request()->header('id'))->get()->toArray()[0];
             $mensaje="";
         if(!array_key_exists('desc',request()->header())){
                 $mensaje='';
             }
             else{$mensaje= request()->header('desc');}

             $this->pagos2($receptor,$usuario->get()->toArray()[0], request()->header('cantidad'),'C','P', 'P',$mensaje);
             //$this->pagos(Usuario::where('id','=', request()->header('id'))->get()->toArray()[0],$usuario->get()->toArray()[0], request()->header('cantidad'),'C','P', 'P');
        //, (!request()->header()->has('desc'))?"": request()->header('desc'));
            $mens1="";

            if($mensaje<>""){
                $mens1='Descripción : '.$mensaje;
            }
            else{
                $mens1="Descripción : retiro por pago";
            }

        $deu= \App\Models\Detalleusuario::where('idcuenta','=',$usuario->get()->toArray()[0]['id'])->get()->toArray()[0];

        $notificacion=new Notificacion();
        $notificacion->titulo='Salida';
        $notificacion->cantidad= $this->remplazar(request()->header('cantidad'));
        $notificacion->fecha=date("Y-m-d H:i:s");
        $notificacion->tipo='C';
        $notificacion->status='P';
        $notificacion->idnot= request()->header('id');
        $notificacion->texto=$deu['nombre'].' '.$deu['apellidos']." te ha solicitado un pago por la cantidad de $ ".number_format($this->remplazar(request()->header('cantidad')),2);
        $notificacion->descripcion=$mens1;
        $notificacion->save();
        //receptor
        return response()->json(['message'=>'Peticion realizada','status'=>$this->successStatus],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
    }
    //pagar
    public function pay(){

         $validator = \Validator::make(request()->header(),["token.*"    => "required|min:0","clabe.*"=>"required|min:0|size:18|numeric","cantidad.*"=>"required|min:0","id.*"=>"required|min:0|numeric"],["token.required"=>"Ingrese su Token","id.required"=>"Ingrese su id del receptor","cantidad.required"=>"Ingrese la cantidad"]);
         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
         $usuario= Usuario::where('token','=', request()->header('token'));

        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
        }

        if($usuario->get()->toArray()[0]['id']=== (int)request()->header('id')){
            return response()->json(['message'=>'No se puede realizar la transaccion a la misma cuenta','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
        }
        $saldo= Saldo::find($usuario->get()->toArray()[0]['id']);

         if(((double) $this->remplazar(request()->header('cantidad')))>((double) $this->desencriptar($saldo->saldo))){

                return response()->json(['message'=>'la transaccion no puede ser completada por falta de saldo en tu cuenta','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
         }
         $receptor= Usuario::where('id','=',(int)request()->header('id'))->get()->toArray()[0];

         //$this->pagos($usuario->get()->toArray()[0],Usuario::where('id','=', request()->header('id'))->get()->toArray()[0], request()->header('cantidad'),'P','C', 'R');

        //$this->pagos($usuario->get()->toArray()[0], $receptor, request()->header('cantidad'), 'P', 'C', 'R');

         $mensaje="";
         if(!array_key_exists('desc',request()->header())){
                 $mensaje='';
             }
             else{$mensaje= request()->header('desc');}


         $this->pagos2($usuario->get()->toArray()[0], $receptor, request()->header('cantidad'), 'P', 'C', 'R', $mensaje);
         $deu= \App\Models\Detalleusuario::where('idcuenta','=',$usuario->get()->toArray()[0]['id'])->get()->toArray()[0];


            if($mensaje<>""){

                $mens2='Descripción : '.$mensaje;
            }
            else{

                $mens2="Descripción : deposito por pago";
            }

        $notificacion=new Notificacion();
        $notificacion->titulo='Entrada';
        $notificacion->cantidad= $this->remplazar(request()->header('cantidad'));
        $notificacion->fecha=date("Y-m-d H:i:s");
        $notificacion->tipo='P';
        $notificacion->status='P';
        $notificacion->idnot= request()->header('id');
        $notificacion->texto=$deu['nombre'].' '.$deu['apellidos']." te ha depositado a tu cuenta la cantidad de $ ".number_format($this->remplazar(request()->header('cantidad')),2);
        $notificacion->descripcion=$mens2;
        $notificacion->save();

        return response()->json(['message'=>'Transacción Realizada','status'=>$this->successStatus],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
    }
    //depositar
    public function paymet(){
        $validator = \Validator::make(request()->header(),["token.*"=> "required|min:0","id.*"=>"required|min:0"],["token.required"=>"Ingrese su Token","id.required"=>"Ingrese su Id de paypal"]);
         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
        $usuario= Usuario::where('token','=', request()->header('token'));

        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->unauthorizedStatus],$this->unauthorizedStatus,[],JSON_UNESCAPED_SLASHES);
        }
        $usuario=$usuario->get()->toArray()[0];

        $saldo= Saldo::find($usuario['id']);

        $paypalcontex=new ApiContext(
                new OAuthTokenCredential(\config('paypal')['client_id'],\config('paypal')['secret']));
        $paypalcontex->setConfig(config('paypal')['settings']);

        $payments=new Payment();
        try {
            $payment = Payment::get(request()->header('id'), $paypalcontex);
            if($payment->getState()=='approved'){
                $respuesta=$payment->toArray();
                $monto = $respuesta['transactions'][0]['amount']['total'];
                   $saldo->saldo= $this->encriptar($usuario['crypt'], ((double)$this->desencriptar($saldo->saldo)+((double)($this->remplazar($monto)))));
                   $saldo->save();

                   $this->log(json_encode(array('Movimiento'=>'Deposito de Fondos',
                       'Detalle'=>array('Id'=>$saldo->idcliente,'Saldo Actual'=> $saldo->saldo),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
                   $transacciones=new Transaccione();
                   $transacciones->id_cliente=$usuario['id'];
                   $transacciones->entrada=1;
                   $transacciones->descripcion="Recarga";
                   $transacciones->cantidad= $this->encriptar($usuario['crypt'], ((double)$this->remplazar($monto)));
                   $transacciones->status="R";
                   $transacciones->id_c2=0;
                   $transacciones->fecha=date("Y-m-d H:i:s");;
                   $transacciones->nombre="";
                   $transacciones->tipot="D";
                   $transacciones->tkey="";
                   $transacciones->save();

                   //id=3
                   $usuario= Usuario::find($saldo->idcliente);
                   $recompensa=new RecompensasController();
                   $recompensa->recompensa($usuario, 3, $this->remplazar($monto));
                   //id=9
                   $recompensa->recompensa($usuario, 9, $this->remplazar($monto));

                       return response()->json([ "message" => "Transaccion realizada de manera satisfactoria" ,"status"=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);

            }
            else if($payment->getState()=='failed'){
                return response()->json([ "message" => 'Transaccion no se realizado de manera satisfactoria, intente mas tarde' ,"status"=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
         }

         catch (Exception $ex) {
             return response()->json(['message'=>$ex->getMessage(),'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
         }
    }
    //retirar
    public function payout(){

        $validator = \Validator::make(request()->header(),["token.*"    => "required|min:0","clabe.*"=>"required|min:0|numeric","cantidad.*"=>"required|min:0"],["token.required"=>"Ingrese su Token","clabe.required"=>"Ingrese su Clabe Interbancaria","cantidad.required"=>"Ingrese la cantidad a retirar"]);
         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
        if($this->remplazar(request()->header('cantidad'))<100){
            return response()->json(['message'=>'la cantidad debe ser mayor a 100 ','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
        }
        $usuario= Usuario::where('token','=', request()->header('token'));

        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->unauthorizedStatus],$this->unauthorizedStatus,[],JSON_UNESCAPED_SLASHES);
        }
        $usuario=$usuario->get()->toArray()[0];
        $saldo= Saldo::find($usuario['id']);

         if(((double) $this->remplazar(request()->header('cantidad')))>((double) $this->desencriptar($saldo->saldo))){
                return response()->json(['message'=>'la transaccion no puede ser completada ya que la cantidad es mayor a su saldo','status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
         }
         $saldo->saldo= $this->encriptar($usuario['crypt'], ((double)$this->desencriptar($saldo->saldo)-(double)($this->remplazar(request()->header('cantidad')))));
         $saldo->save();
         $this->log(json_encode(array('Movimiento'=>'Retiro de Fondos',
                       'Detalle'=>array('Id'=>$saldo->idcliente,'Saldo Actual'=> $saldo->saldo),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
         $transacciones=new Transaccione();
         $transacciones->id_cliente=$usuario['id'];
         $transacciones->entrada=0;
         $transacciones->descripcion="Retiro de tu cuenta";
         $transacciones->cantidad= $this->encriptar($usuario['crypt'], $this->remplazar(request()->header('cantidad')));
         $transacciones->status="R";
         $transacciones->id_c2=0;
         $transacciones->fecha=date("Y-m-d H:i:s");;
         $transacciones->nombre="";
         $transacciones->tipot="R";
         $transacciones->tkey="";
         $transacciones->save();

        $detalle= \App\Models\Detalleusuario::where('idcuenta','=',$usuario['id'])->get()->toArray()[0];

         $datos= new \stdClass();
         $datos->from=$usuario['correo'];
         $datos->view='mails.retiro';
         $datos->text='mails.retiro_plain';
         $datos->nombre=$detalle['nombre'].' '.$detalle['apellidos'];
         $datos->fromname=$detalle['nombre'].' '.$detalle['apellidos'];
         $datos->tcuenta=\config('confkiintos')['cuenta'][$usuario['esempresa']];
         $datos->correo=$usuario['correo'];
         $datos->telefono=$detalle['telefono'];
         $datos->cantidad= request()->header('cantidad');
         $datos->clabe= request()->header('clabe');

         $datos->subject='retiro a cuenta bancaria';

         Mail::to('retiros@kiintos.com')->send(new SoporteKiintos($datos));
            return response()->json(["message" => "Tu solicitud de retiro está en proceso para que tu dinero será transferido a tu cuenta bancaria, ten en cuenta que el proceso puede tardar algunos días en completarse","status"=>$this->successStatus],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
    }
    //paypay
    public function credentials(){
         $validator = \Validator::make(request()->header(),["token.*"    => "required"],["token.required"=>"Ingrese su Token"]);
         if($validator->fails()){
            foreach ($validator->errors()->messages() as $mensaje){
                return response()->json(['message'=>$mensaje[0],'status'=>$this->notfoudStatus],$this->notfoudStatus,[],JSON_UNESCAPED_SLASHES);
            }
        }
        $usuario= Usuario::where('token','=', request()->header('token'));

        if($usuario->count()===0){
            return response()->json(['message'=>'Sus credenciales no se pudierón verificar','status'=>$this->unauthorizedStatus],$this->unauthorizedStatus,[],JSON_UNESCAPED_SLASHES);
        }
        return response()->json(["id" =>\config('paypal')['client_id'], "key" => \config('paypal')['secret']],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
    }

/*-----------------------------------------------------------------------------------------------------*/
private function paso(){}

private function encriptar($key,$pass){
        $key=hash_hmac('sha256', '12345', $key);
        $val=rand(0, 32);
        $key= substr($key, $val,32);
        $enc=new Encrypter($key,'AES-256-CBC');
        $crypt=$enc->encryptString($pass);
        $crypt= json_encode(['key'=>$key,'crypt'=>$crypt],JSON_UNESCAPED_SLASHES);
        $crypt= encrypt($crypt);
        return $crypt;
    }

    private function desencriptar($crypt){
        $crypt= decrypt($crypt);
        $crypt= json_decode($crypt,true);
        $encrypt=new Encrypter($crypt['key'], 'AES-256-CBC');
        $passwd=$encrypt->decryptString($crypt['crypt']);
        return $passwd;
    }
    private function remplazar($value){
        $cantidad=str_replace('$','',$value);
        $cantidad=str_replace(',','',$cantidad);
        $cantidad=str_replace(' ','',$cantidad);
        return $cantidad;
    }

    private function pagos($emisor,$receptor,$cantidad,$tipo1,$tipo2,$status){




        $detu1= \App\Models\Detalleusuario::where('idcuenta','=',$emisor['id'])->get()->toArray()[0];
        $detu2= \App\Models\Detalleusuario::where('idcuenta','=',$receptor['id'])->get()->toArray()[0];

            $saldo1= Saldo::find($emisor['id']);
            $saldo2= Saldo::find($receptor['id']);
            if($status==='R'){
                $saldo1->saldo= $this->encriptar($emisor['crypt'], ((double)$this->desencriptar($saldo1->saldo)-(double)($this->remplazar($cantidad))));
                $saldo1->save();


                   $this->log(json_encode(array('Movimiento'=>'Pago',
                       'Detalle'=>array('Id'=>$saldo1->idcliente,'Saldo Actual'=> $saldo1->saldo,'descripción'=>'retiro por pago'),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));

                $saldo2->saldo= $this->encriptar($receptor['crypt'], ((double)$this->desencriptar($saldo2->saldo)+(double)($this->remplazar($cantidad))));
                $saldo2->save();

                   $this->log(json_encode(array('Movimiento'=>'Cobro',
                       'Detalle'=>array('Id'=>$saldo2->idcliente,'Saldo Actual'=> $saldo2->saldo,'descripción'=>'deposito por pago'),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            }
        $transacciones=new Transaccione();
         $transacciones->id_cliente=$emisor['id'];
         $transacciones->entrada=0;
         $transacciones->descripcion="retiro por pago";
         $transacciones->cantidad= $this->encriptar($emisor['crypt'], $this->remplazar($cantidad));
         $transacciones->status=$status;
         $transacciones->id_c2=$receptor['id'];
         $transacciones->fecha=date("Y-m-d H:i:s");
         $transacciones->nombre=$detu2['nombre'].' '.$detu2['apellidos'];
         $transacciones->tipot=$tipo1;
         $transacciones->tkey="";
         $transacciones->save();

         $transacciones=new Transaccione();
         $transacciones->id_cliente=$receptor['id'];
         $transacciones->entrada=1;
         $transacciones->descripcion="deposito por pago";
         $transacciones->cantidad= $this->encriptar($receptor['crypt'], $this->remplazar($cantidad));
         $transacciones->status=$status;
         $transacciones->id_c2=$emisor['id'];
         $transacciones->fecha=date("Y-m-d H:i:s");
         $transacciones->nombre=$detu1['nombre'].' '.$detu1['apellidos'];
         $transacciones->tipot=$tipo2;
         $transacciones->tkey="";
         $transacciones->save();
         if($status==='R'){
         //id=4 pagos
          $usuario= Usuario::find($emisor['id']);
          $recompensa=new RecompensasController();
          $recompensa->recompensa($usuario, 4, (double) $this->remplazar($cantidad));
          //id=4 pagos
          $usuario= Usuario::find($receptor['id']);
          $recompensa=new RecompensasController();
          $recompensa->recompensa($usuario, 4, (double) $this->remplazar($cantidad));
         }
    }
    private function pagos2($emisor,$receptor,$cantidad,$tipo1,$tipo2,$status,$mensaje){


        $detu1= \App\Models\Detalleusuario::where('idcuenta','=',$emisor['id'])->get()->toArray()[0];

        $detu2= \App\Models\Detalleusuario::where('idcuenta','=',$receptor['id'])->get()->toArray()[0];


            $saldo1= Saldo::find($emisor['id']);

            $saldo2= Saldo::find($receptor['id']);

            if($status==='R'){
                $saldo1->saldo= $this->encriptar($emisor['crypt'], ((double)$this->desencriptar($saldo1->saldo)-(double)($this->remplazar($cantidad))));
                $saldo1->save();


                   $this->log(json_encode(array('Movimiento'=>'Pago',
                       'Detalle'=>array('Id'=>$saldo1->idcliente,'Saldo Actual'=> $saldo1->saldo,'descripción'=>'retiro por pago'),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));

                $saldo2->saldo= $this->encriptar($receptor['crypt'], ((double)$this->desencriptar($saldo2->saldo)+(double)($this->remplazar($cantidad))));
                $saldo2->save();

                   $this->log(json_encode(array('Movimiento'=>'Cobro',
                       'Detalle'=>array('Id'=>$saldo2->idcliente,'Saldo Actual'=> $saldo2->saldo,'descripción'=>'deposito por pago'),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            }

            $mens1="";
            $mens2="";
            if($mensaje<>""){
                $mens1='Descripción : '.$mensaje;
                $mens2='Descripción : '.$mensaje;
            }
            else{
                $mens1="Descripción : retiro por pago";
                $mens2="Descripción : deposito por pago";
            }

            $transacciones2=new Transaccione();
         $transacciones2->id_cliente=$receptor['id'];
         $transacciones2->entrada=1;
         $transacciones2->descripcion=$mens2;
         $transacciones2->cantidad= $this->encriptar($receptor['crypt'], $this->remplazar($cantidad));
         $transacciones2->status=$status;
         $transacciones2->id_c2=$emisor['id'];
         $transacciones2->fecha=date("Y-m-d H:i:s");
         $transacciones2->nombre=$detu1['nombre'].' '.$detu1['apellidos'];
         $transacciones2->tipot=$tipo2;
         $transacciones2->tkey="";
         $transacciones2->save();

         $transacciones=new Transaccione();
         $transacciones->id_cliente=$emisor['id'];
         $transacciones->entrada=0;
         $transacciones->descripcion=$mens1;
         $transacciones->cantidad= $this->encriptar($emisor['crypt'], $this->remplazar($cantidad));
         $transacciones->status=$status;
         $transacciones->id_c2=$receptor['id'];
         $transacciones->fecha=date("Y-m-d H:i:s");
         $transacciones->nombre=$detu2['nombre'].' '.$detu2['apellidos'];
         $transacciones->tipot=$tipo1;
         $transacciones->tkey="";
         $transacciones->save();

          if($status==='R'){
         //id=4 pagos
          $usuario= Usuario::find($emisor['id']);
          $recompensa=new RecompensasController();
          $recompensa->recompensa($usuario, 4, (double) $this->remplazar($cantidad));
          //id=4 pagos
          $usuario= Usuario::find($receptor['id']);
          $recompensa=new RecompensasController();
          $recompensa->recompensa($usuario, 4, (double) $this->remplazar($cantidad));
         }



    }
    /*public function saldosx(){
        $info= \App\Models\Detalleusuario::select('detalleusuario.idcuenta','detalleusuario.nombre','saldos.saldo')
                ->join('saldos','saldos.idcliente','=','detalleusuario.idcuenta');
        $info=$info->get()->toArray();
        for($i=0;$i<count($info);$i++){
            $info[$i]['saldo']= $this->desencriptar($info[$i]['saldo']);
        }

        return response()->json(["saldos" =>$info],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
   }
   public function setsaldos(){
       $info= json_decode(request()->header('saldos'),true);
       $info=$info['saldos'];
       for($i=0;$i<count($info);$i++){
           $user= Usuario::where('id','=',$info[$i]['idcuenta'])->get()->toArray()[0];
            $info[$i]['saldo']= $this->encriptar($user['crypt'], $info[$i]['saldo']);
            $saldo1= Saldo::find($info[$i]['idcuenta']);
            $saldo1->saldo=$info[$i]['saldo'];
            $saldo1->save();
        }
        return response()->json(['ok'=>'ok']);
   }*/

    private function log($mensaje){
        $log=new \App\Models\Log();
        $log->detalle=$mensaje;
        $log->save();
    }
}
