<?php

namespace App\Http\Controllers\API\v2;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Encryption\Encrypter;
use App\Http\Controllers\Controller;
use Chat;
use Musonza\Chat\Models\Conversation;
use App\Events\MessageSent;
use App\Message;

use App\Models\Usuario;
use App\Models\Detalleusuario;
use App\Models\Transaccione;
use App\Models\Log;
use App\Models\Saldo;
/**
 * Description of MessageController
 *
 * @author valdez
 */
class MessageController extends Controller {

    public function newconversation() {
        $validacion = array(
            array('encabezado' => 'token',
                'longitud' => 0,
                'mensaje' => 'Ingrese su token'),
            array('encabezado' => 'idusuario',
                'longitud' => 0,
                'mensaje' => 'Ingrese su id del usuario')
        );
        $validar = $this->validar(request()->header(), $validacion);

        if ($validar['status'] != 200) {

            return response()->json(['message' => $validar['message'], 'status' => $validar['status']], 404, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario1 = Usuario::where('token', '=', request()->header('token'));
        if ($usuario1->count() <= 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => 404], 200, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario1 = $usuario1->get()->toArray()[0]['id'];
        $usuario2 = Usuario::find(request()->header('idusuario'));
        if ($usuario2 === null) {
            return response()->json(['message' => 'Este Usuario no existe', 'status' => 404], 200, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario2 = $usuario2->id;

        $conversacion = Chat::conversations()->between(Usuario::find($usuario1), Usuario::find($usuario2));
        if ($conversacion === null) {
            $participantes = [$usuario1, $usuario2];
            $conversacion = Chat::createConversation($participantes);
            $datos = Detalleusuario::where('idcuenta', '=', $usuario2)->get()->toArray()[0];
            return response()->json(['idconversacion' => $conversacion->id, 'nombre' => $datos['nombre'] . ' ' . $datos['apellidos']], 200, [], JSON_UNESCAPED_SLASHES);
        } else {
            $datos = Detalleusuario::where('idcuenta', '=', $usuario2)->get()->toArray()[0];
            return response()->json(['idconversacion' => $conversacion->id, 'nombre' => $datos['nombre'] . ' ' . $datos['apellidos']], 200, [], JSON_UNESCAPED_SLASHES);
        }
    }

    public function sendmessage() {
        $this->validatoken();
        $validacion = array(
            array('encabezado' => 'tipomensaje',
                'longitud' => 0,
                'mensaje' => 'Ingrese el tipo del mensaje'),
            array('encabezado' => 'idconv',
                'longitud' => 0,
                'mensaje' => 'Ingrese el id de la conversacion')
        );
        $validar = $this->validar(request()->header(), $validacion);
        if ($validar['status'] != 200) {

            return response()->json(['message' => $validar['message'], 'status' => $validar['status']], 200, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() <= 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => 404], 200, [], JSON_UNESCAPED_SLASHES);
        }       
        $usuario = Usuario::find($usuario->get()->toArray()[0]['id']);
        if (request()->header('tipomensaje') === 'texto') {
            
            $conversacion = Chat::conversations()->getById(request()->header('idconv'));
            $mensaje = Chat::message(request()->header('mensaje'))
                    ->type('text')
                    ->from($usuario)//desde
                    ->to($conversacion)
                    ->send();
            return response()->json(['message' => 'Mensaje Enviado', 'status' => 200], 200, [], JSON_UNESCAPED_SLASHES);
        }
        /*else if(request()->header('tipomensaje') === 'transaccion'){
            $conversacion = Chat::conversations()->getById(request()->header('idconv'));
            $participantes=$conversacion->users;
            $mensaje="";
            $emisor=null;
            $receptor=null;
            
            
            if($participantes[0]->id==$usuario->id){
                   $emisor= Usuario::where('id','=',$usuario->id)->get()->toArray()[0];
                   $receptor= Usuario::where('id','=',$participantes[1]->id)->get()->toArray()[0];
               }
               else{
                   $receptor= Usuario::where('id','=',$usuario->id)->get()->toArray()[0];
                   $emisor= Usuario::where('id','=',$participantes[1]->id)->get()->toArray()[0];
               }
               
           if(request()->header('tipotran')==='cobro'){
               $mensaje="Cobro $".request()->header('cantidad');
               
               $this->pagos2($receptor,$emisor, request()->header('cantidad'),'C','P', 'P',"");
               
           }
           else{
               $this->pagos2($emisor, $receptor, request()->header('cantidad'), 'P', 'C', 'R', "");
               $mensaje="Pago $".request()->header('cantidad');               
           }    
            $mensaje = Chat::message($mensaje)
                    ->type('trans')
                    ->from($usuario)//desde
                    ->to($conversacion)
                    ->send();
            return response()->json(['message' => 'Mensaje Enviado', 'status' => 200], 200, [], JSON_UNESCAPED_SLASHES);
        }*/
        else if(request()->header('tipomensaje') === 'imagen'){
            $conversacion = Chat::conversations()->getById(request()->header('idconv'));
            
            $img=basename(Storage::disk('local')->put('chat/' . $conversacion->id . '/img', request()->file('image')));
            
            $mensaje = Chat::message(request()->root() . '/api/message/getimage/'.$img)
                    ->type('image')
                    ->from($usuario)//desde
                    ->to($conversacion)
                    ->send();
            return response()->json(['message' => 'Mensaje Enviado', 'status' => 200], 200, [], JSON_UNESCAPED_SLASHES);
        }
    }

    public function getmessage() {
        $this->validatoken();
                
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() <= 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => 404], 200, [], JSON_UNESCAPED_SLASHES);
        }       
        //$usuario = Usuario::find($usuario->get()->toArray()[0]['id']);
        $usuario = Usuario::find(2);
        $mensajes=array();        
        $conversaciones = Chat::conversations()->getall($usuario);
        foreach ($conversaciones as $con) {
           $conversacion = Chat::conversations()->getById($con);
            if (Chat::conversation($conversacion)->for($usuario)->getMessages()->count() > 0) {
                $mens = Chat::conversation($conversacion)->for($usuario)->getMessages();
                for($i=0;$i<$mens->total();$i++)
                {
                    
                    $menx=$mens->get($i);
                    $deu= Detalleusuario::where('idcuenta', '=',$menx->user_id)->get()->toArray()[0];
                    //dd($menx->created_at->toDateTimeString());
                    //print_r($menx->created_at);
                    //array_push
                    array_push($mensajes,array('idconversacion'=>$menx->conversation_id,'nombre'=>$deu['nombre'].' '.$deu['apellidos'],'idusuario'=>$menx->user_id,'idmensaje'=>$menx->id,'tipo'=>$menx->type,'mensaje'=>$menx->body,'fecha'=>$menx->created_at->format('Y-m-d H:i:s A')));
                    //
                    //dd($menx);
                }
            }
            
        }
        
        if($mensajes===false){
          return response()->json(['message' => "no hay mensajes por el momento", "status" => 404], 200, [], JSON_UNESCAPED_SLASHES);  
        }
        else
        {
          return response()->json(['mensajes' =>$mensajes], 200, [], JSON_UNESCAPED_SLASHES);    
        }
        /*if ($mensajes = Chat::conversation($conversacion)->for($usuario)->getMessages()->count() > 0) {
            
            $message = Chat::messages()->getById($mensajes->id);
            Chat::message($message)->for($usuario)->markSend();
            return response(['id'=>$mensajes->id,"tipo"=>$mensajes->type,'mensaje'=>$mensajes->body]);            
        } else {
            
        }*/
    }
   

    /*public function getconversacion() {
        
        $conversacion = Chat::conversations()->getById(14);
        dd($conversacion->users[0]->id);
    }*/
    
    public function image($image){
       $validacion = array(            
            array('encabezado' => 'idconv',
                'longitud' => 0,
                'mensaje' => 'Ingrese el id de la conversacion')
        );
        $validar = $this->validar(request()->header(), $validacion);
        if ($validar['status'] != 200) {

            return response()->json(['message' => $validar['message'], 'status' => $validar['status']], 200, [], JSON_UNESCAPED_SLASHES);
        }         
        if(Storage::disk('local')->exists('chat/'.request()->header('idconv').'/img/'.$image)){
                    return Storage::download('chat/'.request()->header('idconv').'/img/'.$image);
                }
                else{
                    return Storage::download('profile/default/default.webp'); 
                }
    }
    /* ----------------------------------------------------------------------------------------- */

    private function validar($header, $validacion) {

        for ($x = 0; $x < count($validacion); $x++) {
            if (array_key_exists('adicional', $validacion[$x])) {

                if ($validacion[$x]['adicional']) {
                    $lon1 = strlen($header[$validacion[$x]['encabezado']][0]);
                    $lon2 = $validacion[$x]['longitud'];
                    if ($lon1 <= $lon2) {
                        $mjs = array("message" => $validacion[$x]['mensaje'],
                            "status" => 404);
                        return $mjs;
                    }
                }
            } else {
                if (!array_key_exists($validacion[$x]['encabezado'], $header)) {
                    $mjs = array("message" => $validacion[$x]['mensaje'],
                        "status" => 404);
                    return $mjs;
                } else {

                    $lon1 = strlen($header[$validacion[$x]['encabezado']][0]);
                    $lon2 = $validacion[$x]['longitud'];
                    //echo $lon2;

                    if ($lon1 <= $lon2) {
                        $mjs = array("message" => $validacion[$x]['mensaje'],
                            "status" => 404);
                        return $mjs;
                    }
                }
            }
        }
        return array("status" => 200);
    }

    private function validatoken() {
        $validar = array(
            array('encabezado' => 'token',
                'longitud' => 0,
                'mensaje' => 'ingrese su token'));
        $validacion = $this->validar(request()->header(), $validar);
        if ($validacion['status'] != 200) {
            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], 404, [], JSON_UNESCAPED_SLASHES);
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
    }
    private function log($mensaje){
        $log=new \App\Models\Log();
        $log->detalle=$mensaje;
        $log->save();
    }
    private function remplazar($value){
        $cantidad=str_replace('$','',$value);
        $cantidad=str_replace(',','',$cantidad);
        $cantidad=str_replace(' ','',$cantidad);
        return $cantidad;
    }
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
}
