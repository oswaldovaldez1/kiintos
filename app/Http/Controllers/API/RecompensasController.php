<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Encryption\Encrypter;

use App\Models\Recompensas;
use App\Models\Saldo;
use App\Models\Usuario;
use App\Models\Transaccione;

/**
 * Description of RecompensasController
 *
 * @author warlock
 */
class RecompensasController extends Controller {
    public function recomsem($usuario){
        
        
        $hoy= \Carbon::now();//parse(date("Y-m-d H:i:s"));
        $registro=\Carbon::parse($usuario->registro);
        //dd($registro);
        
        if($hoy->month===2 and $hoy->day===29){
            return;
        }
        else if($hoy->month===2 and $hoy->day===28 and $registro->day>=28){
            
            $registro=\Carbon::parse($registro->year.'-02-28');
        }
        else if($hoy->month===4 or $hoy->month===6 or $hoy->month===9 or $hoy->month===11 and $registro->day===31){
            
            $registro=\Carbon::parse($registro->year.'-'.$registro->month.'-30');
        }
        //print_r($hoy);
        $semanas=$hoy->diffInWeeks($registro);
        //print_r($semanas);
        $total=$semanas-$usuario->semanas;
        //print_r($total);
        //return;
        for($x=0;$x<$total;$x++){
            $this->recompensa($usuario, 5, 0);
        }
        $usuario->semanas=$semanas;
        $usuario->update();
    }
    public function recompensa($usuario,$id,$cantidad){        
        $fijos=null;
        $transacciones=new Transaccione();
        $recon=new Recompensas();
        $recon= Recompensas::find($id);
        $cantidad= (double)$this->remplazar($cantidad);        
        $saldo1= Saldo::find($usuario->id);        
        $valor=0;                
        $saldo=(double)$this->desencriptar($saldo1->saldo);        
        if($recon->tevento==='F'){
            $fijos= strpos($usuario->evento, '.'.$id.'.');
            if($fijos===false){
                $fijos=true;
            }
            else{
                return;;                
            }
        }
        if($recon->tipo==='E'){
            $valor=$recon->valor;
            $saldo=$saldo+$valor;
        }
        else if($recon->tipo==='P'){
            $valor=(double)(number_format((($recon->valor/100)*$cantidad),2));
            $saldo=$saldo+$valor;
        }
        
        $saldo1->saldo=$this->encriptar($usuario->crypt, $this->remplazar($saldo));
        $saldo1->update();
        
        $transacciones->id_cliente = $usuario->id;
        $transacciones->entrada = 1;
        $transacciones->descripcion = $recon->mensaje;
        $transacciones->cantidad = $this->encriptar($usuario->crypt, $this->remplazar($valor));
        $transacciones->status = "R";
        $transacciones->id_c2 = 0;
        $transacciones->fecha = date("Y-m-d H:i:s");
        $transacciones->nombre = "";
        $transacciones->tipot = "I";
        $transacciones->tkey = "";
        $transacciones->save();
        if($fijos){
            $usuario->evento=$usuario->evento.$id.'.';
            $usuario->update();
        }
        
        $not = new \App\Models\Notificacion();

                    $not->fecha = date("Y-m-d H:i:s");
                    $not->status = 'P';
                    $not->titulo = 'Salida';
                    $not->texto = $recon->mensaje.' $ '.number_format($valor,2);
                    $not->cantidad = $valor;
                    $not->idnot = $usuario->id;
                    $not->tipo = 'I';
                    $not->descripcion='Ingreso';
                    $not->save();
                    
        $this->log(json_encode(array('Movimiento'=>'Minar',                    
                       'Detalle'=>array('Id'=>$usuario->id,'Saldo Actual'=> $this->desencriptar($saldo1->saldo),'descripción'=>$recon->descripcion),
                       'Fecha'=>date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
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
    private function remplazar($value){
        $cantidad=str_replace('$','',$value);
        $cantidad=str_replace(',','',$cantidad);
        $cantidad=str_replace(' ','',$cantidad);
        return $cantidad;
    }
    private function log($mensaje){
        $log=new \App\Models\Log();
        $log->detalle=$mensaje;
        $log->save();
    }
}
