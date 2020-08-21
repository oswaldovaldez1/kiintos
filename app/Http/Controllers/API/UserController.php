<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
/**/
use App\Mail\SoporteKiintos;
/* Modelos */
use App\Models\Usuario;
use App\Models\Detalleusuario;
use App\Models\Saldo;
use App\User;
use App\Models\Catredes;

use App\Http\Controllers\API\RecompensasController;

class UserController extends Controller {

    public $successStatus = 200;
    public $unauthorizedStatus = 401;
    public $notfoudStatus = 404;

    /* ----------------------------------------------------------------------------------------------------- */

    public function login() {

        $validacion = array(
            array('encabezado' => 'correo',
                'longitud' => 0,
                'mensaje' => 'Ingrese su Correo'),
            array('encabezado' => 'password',
                'longitud' => 0,
                'mensaje' => 'Ingrese su Contraseña')
        );
        $validar = $this->validar(request()->header(), $validacion);

        if ($validar['status'] != $this->successStatus) {

            return response()->json(['message' => $validar['message'], 'status' => $validar['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Usuario::where('correo', '=', request()->header('correo'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Esta cuenta de Correo no esta registrada', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        if ($usuario->get()->toArray()[0]['activo'] != 1 and $usuario->get()->toArray()[0]['verificacion'] != '') {
            return response()->json(['message' => 'Su cuenta no esta activada', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        if ($this->desencriptar($usuario->get()->toArray()[0]['passwd']) != request()->header('password')) {
            return response()->json(['message' => 'Error en autentificacion, verifique su usuario o password', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        $input = ['name' => request()->header('correo'), 'password' => encrypt(request()->header('password')), 'email' => request()->header('correo')];
        //  User::where('email','=',request()->header('correo'))->delete();
        $user = User::where('email', '=', request()->header('correo')); //find(request()->header('correo'));
        if ($user->count() === 0) {
            $user = User::create($input);
        } else {
            $user = User::find($user->get()->toArray()[0]['id']);
        }

        $token = $user->createToken('kiintosApp')->accessToken;

        if (Usuario::where('correo', '=', request()->header('correo'))->update(array('token' => $token, "clave" => "")) != 0) {
            $this->log(json_encode(array('Movimiento' => 'Inicio de sesión',
                "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            return response()->json($this->getdatos($usuario->get()->toArray()[0]['id']), $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        } else {
            return response()->json(['message' => 'Error en autentificacion, verifique su usuario o password', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
    }

    public function logind() {

        $validacion = array(
            array('encabezado' => 'correo',
                'longitud' => 0,
                'mensaje' => 'Ingrese su Correo'),
            array('encabezado' => 'password',
                'longitud' => 0,
                'mensaje' => 'Ingrese su Contraseña')
        );
        $validar = $this->validar(request()->header(), $validacion);

        if ($validar['status'] != $this->successStatus) {

            return response()->json(['message' => $validar['message'], 'status' => $validar['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Usuario::where('correo', '=', request()->header('correo'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Esta cuenta de Correo no esta registrada', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        if ($usuario->get()->toArray()[0]['activo'] != 1 and $usuario->get()->toArray()[0]['verificacion'] != '') {
            return response()->json(['message' => 'Su cuenta no esta activada', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        if ($this->desencriptar($usuario->get()->toArray()[0]['passwd']) != request()->header('password')) {
            return response()->json(['message' => 'Error en autentificacion, verifique su usuario o password', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        $input = ['name' => request()->header('correo'), 'password' => encrypt(request()->header('password')), 'email' => request()->header('correo')];
        //  User::where('email','=',request()->header('correo'))->delete();
        if ($usuario->get()->toArray()[0]['token'] <> "") {
            $this->log(json_encode(array('Movimiento' => 'Inicio de sesión Navegador',
                "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            return response()->json($this->getdatos($usuario->get()->toArray()[0]['id']), $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        }

        $user = User::where('email', '=', request()->header('correo')); //find(request()->header('correo'));
        if ($user->count() === 0) {
            $user = User::create($input);
        } else {
            $user = User::find($user->get()->toArray()[0]['id']);
        }

        $token = $user->createToken('kiintosApp')->accessToken;
        if (Usuario::where('correo', '=', request()->header('correo'))->update(array('token' => $token, "clave" => "")) != 0) {
            $this->log(json_encode(array('Movimiento' => 'Inicio de sesión',
                "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            return response()->json($this->getdatos($usuario->get()->toArray()[0]['id']), $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        } else {
            return response()->json(['message' => 'Error en autentificacion, verifique su usuario o password', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
    }

    public function viewprofile() {
        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        return response()->json($this->getperfil($usuario->get()->toArray()[0]['id']), $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    public function updateprofile() {
        //return response()->json(['message' => request()->header('nombre'), 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        $validar = array(
            array('encabezado' => 'token',
                'longitud' => 0,
                'mensaje' => "Sus credenciales no se pudierón verificar"),
            array('encabezado' => 'nombre',
                'longitud' => 0,
                'mensaje' => "Ingrese su Nombre"),
            array('encabezado' => 'tcuenta',
                'longitud' => 0,
                'mensaje' => "Falta tipo de cuenta"),
        );

        $validacion = $this->validar(request()->header(), $validar);
        if ($validacion['status'] != $this->successStatus) {
            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        if (request()->header('tcuenta') === 'personal') {
            $validar = array(
                array('encabezado' => 'apellidos',
                    'longitud' => 0,
                    'mensaje' => "Ingrese sus Apellidos"),
                array('encabezado' => 'nacimiento',
                    'longitud' => 0,
                    'mensaje' => "Ingrese se Fecha de Nacimiento!!!"),
            );
            $validacion = $this->validar(request()->header(), $validar);
            if ($validacion['status'] != $this->successStatus) {
                return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
            }
            if (Detalleusuario::where('idcuenta', '=', $usuario->get()->toArray()[0]['id'])->update(['nombre' => request()->header('nombre'), 'apellidos' => request()->header('apellidos'), 'f_nacimiento' => date("Y-m-d", strtotime(request()->header('nacimiento'))), 'frase' => request()->header('frase')]) != 0) {

                $this->log(json_encode(array('Movimiento' => 'Actulaización Cuenta',
                    "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                    'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
                //id=2
                $usuariox= Usuario::find($usuario->get()->toArray()[0]['id']);
                $recompensa=new RecompensasController();
                $recompensa->recompensa($usuariox, 2, 0);
                return response()->json(["message" => "Actualizacion Completada", "status" => 200], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
            } else {
                return response()->json(['message' => 'Informacón no actualizada,intente mas tarde', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
            }
        } else if (request()->header('tcuenta') === 'negocio') {
            $redesoc = json_decode(request()->header('redes'), true);
            Detalleusuario::where('idcuenta', '=', $usuario->get()->toArray()[0]['id'])->update(['nombre' => request()->header('nombre')]);
            \App\Models\Empresa::where('id_empresa', '=', $usuario->get()->toArray()[0]['id'])->update(['direccion' => request()->header('direccion'), 'servicios' => request()->header('servicios'), 'tellocal' => request()->header('tellocal'), 'url' => request()->header('url'), 'ubicacion' => '', 'horario_atencion' => request()->header('horarios')]);

            $redes = Catredes::all();
            foreach ($redes as $reds) {
                \App\Models\Redes::where('id_empresa', '=', $usuario->get()->toArray()[0]['id'])->where('id_redes', '=', $reds->id)->update(['url' => $redesoc[$reds->nombre]]);
            }
            $this->log(json_encode(array('Movimiento' => 'Actulaización Cuenta',
                "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            //id=2
                $usuariox= Usuario::find($usuario->get()->toArray()[0]['id']);
                $recompensa=new RecompensasController();
                $recompensa->recompensa($usuariox, 2, 0);
            return response()->json(["message" => "Actualizacion Completada", "status" => 200], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        }
        return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
    }

    public function info() {
        $this->validatoken();
        if (request()->header('token') === "") {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->unauthorizedStatus], $this->unauthorizedStatus, [], JSON_UNESCAPED_SLASHES);
        }
        return response()->json($this->getdatos($usuario->get()->toArray()[0]['id']), $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    public function close() {
        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $id = $usuario->get()->toArray()[0]['id'];
        if (Usuario::where('id', '=', $usuario->get()->toArray()[0]['id'])->update(['token' => '']) != 0) {

            $this->log(json_encode(array('Movimiento' => 'Cierre de Sessión',
                'Detalle' => array('Id' => $id),
                'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
            return response()->json(["userid" => "-1", "userlogged" => "desactivado"], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        }
        return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
    }

    public function record() {
        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        //id=5
        $usuariox= Usuario::find($usuario->get()->toArray()[0]['id']);
        $recompensa=new RecompensasController();
        $recompensa->recomsem($usuariox);

        $transacciones = \App\Models\Transaccione::where('id_cliente', '=', $usuario->get()->toArray()[0]['id'])
                        ->where('status', '<>', 'C')
                        ->orderBy('id', 'DESC')->get()->toArray();

        return response()->json(['historial' => $this->gethistorial($transacciones, $usuario)], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    public function searchstore() {

        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        return response()->json(['tiendas' => $this->gettiendas()], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    public function searchuser() {
        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $validar = array(
            array('encabezado' => 'valor',
                'longitud' => 0,
                'mensaje' => 'Verifique el valor de su busqueda'));
        $validacion = $this->validar(request()->header(), $validar);

        if ($validacion['status'] != $this->successStatus) {

            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Detalleusuario::select('idcuenta as id', 'perfil', 'nombre', 'apellidos', 'telefono as numero')
                        ->where(DB::raw('CONCAT(nombre," ",apellidos)'), 'LIKE', '%' . request()->header('valor') . '%')
                        ->orWhere('telefono', 'LIKE', '%' . substr(request()->header('valor'), strlen(request()->header('valor')) - 10, 10) . '%')->limit(1);
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Este numero no esta registrado', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        $usuario = $usuario->get()->toArray()[0];
        $usuario['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];
        unset($usuario['apellidos']);
        $usuario['perfil'] = request()->root() . '/api/image/getimage/' . $usuario['perfil'];

        return response()->json(['contactos' => [$usuario]], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    //SERVER_ADDUSER
    public function newuser() {

        $validar = array(
            array('encabezado' => 'nombre',
                'longitud' => 0,
                'mensaje' => "Ingrese su Nombre"),
            array('encabezado' => 'apellidos',
                'longitud' => 0,
                'mensaje' => "Ingrese sus Apellidos",
                'adicional' => request()->header('tcuenta') === 'false'),
            array('encabezado' => 'correo',
                'longitud' => 0,
                'mensaje' => "Ingrese su Correo"),
            array('encabezado' => 'password',
                'longitud' => 0,
                'mensaje' => "Ingrese su Contraseña"),
            array('encabezado' => 'telefono',
                'longitud' => 0,
                'mensaje' => "Ingrese su Telefono Celular")
        );
        $validacion = $this->validar(request()->header(), $validar);
        if ($validacion['status'] != $this->successStatus) {
            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        if(strpos(request()->header('telefono'), '+')>0){
            return response()->json(['message' => 'numero no valido', 'status' =>$this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $detalleu = Detalleusuario::where('telefono', '=', request()->header('telefono'));
        if ($detalleu->count() != 0) {
            return response()->json(['message' => 'Este Telefono celular ya fue registrado anteriormente', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $user = Usuario::where('correo','=',request()->header('correo'));

        if ($user->count()>0) {
            return response()->json(['message' => 'Este Correo ya fue registrado anteriormente', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = new Usuario();
        $usuario->correo = request()->header('correo');
        $usuario->crypt = $this->generateRandomString(5);
        $usuario->verificacion = $this->generateRandomString(30);
        $usuario->esempresa = (strtolower(request()->header('tcuenta')) === 'true') ? 1 : 0;
        $usuario->passwd = $this->encriptar($usuario->crypt, request()->header('password'));
        $usuario->registro=date("Y-m-d H:i:s");
        $usuario->evento='.';
        $usuario->semanas=0;
        $usuario->clave = '';
        $usuario->token = '';
        $usuario->activo = 0;

        $usuario->save();

        $user = Usuario::where('correo','=',request()->header('correo'));

        if ($user->count()>0) {
            $user=Usuario::find($user->get()->toArray()[0]['id']);
           /* if ($dirh) {
                while (($dirElement = readdir($dirh)) !== false) {

                }
                closedir($dirh);
            }*/

            $detalleusuario = new Detalleusuario();
            $detalleusuario->idcuenta = $user->id;
            $detalleusuario->nombre = request()->header('nombre');
            $detalleusuario->apellidos = ($user->esempresa === 0) ? request()->header('apellidos') : '';
            $detalleusuario->telefono = request()->header('telefono');
            $detalleusuario->f_nacimiento = date('Y-m-d H:i:s', strtotime(null));
            $detalleusuario->activa = false;
            $detalleusuario->perfil = 'default.webp';
            $detalleusuario->tipoperfil = '';
            $detalleusuario->ext = '';
            $detalleusuario->frase = '';
            $detalleusuario->institucion = '';
            $detalleusuario->save();
            $detalleu = Detalleusuario::find($user->id);
            if ($detalleu === null) {
                Usuario::where('correo', '=', $user->correo)->delete();
                return response()->json(['message' => 'error al registrar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
            }
            $saldos = new Saldo();
            $saldos->idcliente = $user->id;
            $saldos->saldo = $this->encriptar($user->crypt, '0');
            $saldos->save();
            $sald = Saldo::find($user->id);
            if ($sald === null) {
                Detalleusuario::where('idcuenta', '=', $user->id)->delete();
                Usuario::where('correo', '=', $user->correo)->delete();
                return response()->json(['message' => 'error al registrar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
            }
            if ($user->esempresa === 1) {
                $empresa = new \App\Models\Empresa();
                $empresa->id_empresa = $user->id;
                $empresa->direccion = '';
                $empresa->horario_atencion = '';
                $empresa->servicios = '';
                $empresa->tellocal = '';
                $empresa->url = '';
                $empresa->ubicacion = '';
                $empresa->save();
                $emp = \App\Models\Empresa::find($user->id);
                if ($empresa === null) {
                    Detalleusuario::where('idcuenta', '=', $user->id)->delete();
                    Saldo::where('idcliente', '=', $user->id)->delete();
                    Usuario::where('correo', '=', $user->correo)->delete();
                    return response()->json(['message' => 'error al registrar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                }
                for ($idredes = 1; $idredes < 4; $idredes++) {
                    $redes = new \App\Models\Redes();
                    $redes->id_empresa = $user->id;
                    $redes->id_redes = $idredes;
                    $redes->url = '';
                    $redes->save();
                }
            }
            $datos = new \stdClass();
            $datos->from = \config('mail')['username'];
            $datos->view = 'mails.registro';
            $datos->text = 'mails.registro_plain';
            $datos->nombre = $detalleu->nombre . ' ' . $detalleu->apellidos;
            $datos->fromname = 'Kiintos';
            $datos->enlace = request()->root() . '/activar/' . $user->verificacion;
            $datos->imagen = request()->root() . '/api/image/getimage/default.webp';
            $datos->subject = 'Registro';
            Mail::to($user->correo)->send(new SoporteKiintos($datos));

            $this->log(json_encode(array('Movimiento' => 'Registro de Cuenta',
                'Detalle' => array('Id' => $user->id, 'TCuenta' => $user->esempresa),
                'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));

            return response()->json(["message" => "Registro completado, entre a su correo",
                        "status" => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        }
    }

    //SERVER_FORGOTPASS
    public function restore() {
        $validar = array(
            array('encabezado' => 'email',
                'longitud' => 0,
                'mensaje' => "Ingrese su Correo"));
        $validacion = $this->validar(request()->header(), $validar);
        if ($validacion['status'] != $this->successStatus) {
            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario = Usuario::find(request()->header('email'));
        if ($usuario === null) {
            return response()->json(['message' => 'Este correo no esta registrado', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $usuario->clave = $this->generateRandomNumeric(4);
        $usuario->update();
        $detalleu = Detalleusuario::find($usuario->id);
        $datos = new \stdClass();
        $datos->from = \config('mail')['username'];
        $datos->view = 'mails.recuperacion';
        $datos->text = 'mails.recuperacion_plain';
        $datos->nombre = $detalleu->nombre . ' ' . $detalleu->apellidos;
        $datos->pin = $usuario->clave;
        $datos->pass = $this->desencriptar($usuario->passwd);
        $datos->enlace = request()->root() . '/recuperacion/' . $usuario->clave;
        $datos->imagen = request()->root() . '/api/image/getimage/default.webp';
        $datos->subject = 'Recuperacion de Contraseña';
        $datos->fromname = 'Kiintos';
        Mail::to($usuario->correo)->send(new SoporteKiintos($datos));
        return response()->json(["message" => "Se ha enviado su contraseña a su correo",
                    "status" => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    //SERVER_NOTIFICACIONES->
    public function notice() {

        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        }
        //id=5
        $usuariox= Usuario::find($usuario->get()->toArray()[0]['id']);
        $recompensa=new RecompensasController();
        $recompensa->recomsem($usuariox);
        $notificaciones = \App\Models\Notificacion::where('status', '=', 'P')
               ->where('idnot', '=', $usuario->get()->toArray()[0]['id'])
                ->limit(1);

        if ($notificaciones->count() > 0) {
            $notificaciones = $notificaciones->get()->toArray()[0];
            \App\Models\Notificacion::where('id', '=', $notificaciones['id'])->update(['status' => 'V']);
            //$trans
            if ($notificaciones['tipo'] == 'P') {
                return response()->json(['id' => $notificaciones['id'], 'titulo' => $notificaciones['titulo'], 'mensaje' => $notificaciones['texto'] . ' con fecha ' . $notificaciones['fecha'], 'descripcion' => $notificaciones['descripcion'], 'tipo' => 'I', 'url' => ''], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
            }
            if ($notificaciones['tipo'] == 'C') {

                return response()->json(['id' => $notificaciones['id'], 'titulo' => $notificaciones['titulo'], 'mensaje' => $notificaciones['texto'] . ' con fecha ' . $notificaciones['fecha'], 'descripcion' => $notificaciones['descripcion'], 'tipo' => 'C', 'url' => request()->root() . '/api/usuarios/respuesta'], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
            }
            if ($notificaciones['tipo'] == 'I') {
                return response()->json(['id' => $notificaciones['id'], 'titulo' => $notificaciones['titulo'], 'mensaje' => $notificaciones['texto'] . ' con fecha ' . $notificaciones['fecha'], 'descripcion' => $notificaciones['descripcion'], 'tipo' => 'I', 'url' => ''], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
            }
            if ($notificaciones['tipo'] == 'R') {
                return response()->json(['id' => $notificaciones['id'], 'titulo' => $notificaciones['titulo'], 'mensaje' => $notificaciones['texto'] . ' con fecha ' . $notificaciones['fecha'], 'descripcion' => $notificaciones['descripcion'], 'tipo' => 'I', 'url' => ''], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
            }
        }

        return response()->json(['message' => 'No hay nuevas notificaciones', 'status' => $this->notfoudStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
    }

    //SERVER_CONFIRM->
    public function answer() {
        $this->validatoken();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $validar = array(
            array('encabezado' => 'id',
                'longitud' => 0,
                'mensaje' => 'Ingrese su Id'),
            array('encabezado' => 'respuesta',
                'longitud' => 0,
                'mensaje' => 'ingrese su Respuesta'));
        $usuario = $usuario->get()->toArray()[0];
        $validacion = $this->validar(request()->header(), $validar);
        if ($validacion['status'] != $this->successStatus) {
            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        $not = \App\Models\Notificacion::where('id', '=', request()->header('id'));
        if ($not->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        $not = $not->get()->toArray()[0];

        $saldo = Saldo::where('idcliente', '=', $usuario['id']);
        $saldo = $saldo->get()->toArray()[0];


        switch (request()->header('respuesta')) {
            case "s": {

                    if (((double) $not['cantidad']) > ((double) $this->desencriptar($saldo['saldo']))) {
                        return response()->json(['message' => 'la transaccion no puede ser completada por falta de saldo en tu cuenta', 'status' => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
                    }

                    $tran = \App\Models\Transaccione::where('fecha', '=', $not['fecha'])->where('id_cliente', '=', $not['idnot']);
                    $tran = $tran->get()->toArray()[0];

                    if ($tran['status'] != 'P') {
                        return response()->json(['message' => 'la transaccion fue cancelada o aceptada anteriormente', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                    }
                    $detuser = Detalleusuario::where('idcuenta', '=', $usuario['id'])->get()->toArray()[0];

                    \App\Models\Transaccione::where('fecha', '=', $not['fecha'])->where('id_cliente', '=', $not['idnot'])->update(['status' => 'R']);
                    \App\Models\Transaccione::where('fecha', '=', $not['fecha'])->where('id_cliente', '=', $tran['id_c2'])->update(['status' => 'R']);
                    $saldo['saldo'] = $this->encriptar($usuario['crypt'], ($this->desencriptar($saldo['saldo']) - $not['cantidad']));

                    Saldo::where('idcliente', '=', $usuario['id'])->update(['saldo' => $saldo['saldo']]);
                    //-.-
                    //id=4 pagos
                    $usuariox = Usuario::find($usuario['id']);
                    $recompensa = new RecompensasController();
                    $recompensa->recompensa($usuariox, 4, (double) $this->remplazar($not['cantidad']));

                    $usuario = Usuario::where('id', '=', $tran['id_c2'])->get()->toArray()[0];

                    $saldo = Saldo::where('idcliente', '=', $usuario['id'])->get()->toArray()[0];

                    $saldo['saldo'] = $this->encriptar($usuario['crypt'], ($this->desencriptar($saldo['saldo']) + $not['cantidad']));
                    Saldo::where('idcliente', '=', $usuario['id'])->update(['saldo' => $saldo['saldo']]);
                    //-.-
                    //id=4 pagos
                    $usuariox = Usuario::find($usuario['id']);
                    $recompensa = new RecompensasController();
                    $recompensa->recompensa($usuariox, 4, (double) $this->remplazar($not['cantidad']));
                    $cant = $not['cantidad'];

                    $not = new \App\Models\Notificacion();

                    $not->fecha = date("Y-m-d H:i:s");
                    $not->status = 'P';
                    $not->titulo = 'Salida';
                    $not->texto = $detuser['nombre'] . ' ' . $detuser['apellidos'] . '  te acaba de depositar la cantidad de $ ' . number_format($cant, 2);
                    $not->cantidad = $cant;
                    $not->idnot = $usuario['id'];
                    $not->tipo = 'I';
                    $not->descripcion='';
                    $not->save();
                    return response()->json(['message' => 'Se ha aceptado la transaccion', 'status' => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
                    break;
                }
            case "n": {
                    $tran = \App\Models\Transaccione::where('fecha', '=', $not['fecha'])->where('id_cliente', '=', $not['idnot']);
                    $tran = $tran->get()->toArray()[0];
                    if ($tran['status'] != 'P') {
                        return response()->json(['message' => 'la transacción fue cancelada o aceptada anteriormente', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                    }

                    \App\Models\Transaccione::where('fecha', '=', $not['fecha'])->where('id_cliente', '=', $not['idnot'])->update(['status' => 'C']);
                    \App\Models\Transaccione::where('fecha', '=', $not['fecha'])->where('id_cliente', '=', $tran['id_c2'])->update(['status' => 'C']);

                    return response()->json(['message' => 'Transacción Cancelada', 'status' => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);

                    break;
                }
            default : {
                    return response()->json(['message' => 'Respuesta Incorrecta', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                }
        }
    }

    /* ----------------------------------------------------------------------------------------------------- */

    public function imagesaveprofile() {

        $this->validatoken();
        //dd(request());
        //return response()->json();
        $usuario = Usuario::where('token', '=', request()->header('token'));
        if ($usuario->count() === 0) {
            return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        if (!request()->hasfile('perfil')) {
            return response()->json(['message' => 'Agregue su imagen de perfil', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
        if (strrpos(request()->file('perfil')->getmimeType(), 'image') === false) {
            return response()->json(['message' => 'Solo son permitidos imagenes', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }

        Storage::deleteDirectory('profile/' . $usuario->get()->toArray()[0]['correo'] . '/perfil');
        $perfil = basename(Storage::disk('local')->put('profile/' . $usuario->get()->toArray()[0]['correo'] . '/perfil', request()->file('perfil')));
        if (Detalleusuario::where('idcuenta', '=', $usuario->get()->toArray()[0]['id'])->update(array('perfil' => $perfil)) != 0) {
                //id=2
                $usuariox= Usuario::find($usuario->get()->toArray()[0]['id']);
                $recompensa=new RecompensasController();
                $recompensa->recompensa($usuariox, 2, 0);
            return response()->json(['message' => 'archivo subido con exito'], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        } else {
            return response()->json(['message' => 'archivo subido con exito1', 'status' => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
        }
    }

    public function imagegetprofile($image) {

        if ($image == 'default.jpg') {
            return Storage::download('profile/default/default.webp');
        }
        if ($image == 'default.webp') {
            return Storage::download('profile/default/default.webp');
        }
        $usuario = Usuario::select('usuario.correo')
                ->join('detalleusuario', 'detalleusuario.idcuenta', '=', 'usuario.id')
                ->where('detalleusuario.perfil', '=', $image);
        if ($usuario->count()) {
            if (Storage::disk('local')->exists('profile/' . $usuario->get()->toArray()[0]['correo'] . '/perfil/' . $image)) {
                return Storage::download('profile/' . $usuario->get()->toArray()[0]['correo'] . '/perfil/' . $image);
            } else {
                return Storage::download('profile/default/default.webp');
            }
        } else {
            return Storage::download('profile/default/default.webp');
        }
    }

    /* ----------------------------------------------------------------------------------------------------- */

    public function activate($key) {
        if($key==='1234'){
            return view('validar.activacion');
        }
        $user = Usuario::where('verificacion', '=', $key);
        if ($user->count() === 0) {
            abort(404);
        } else {
            $user = $user->get()->toArray()[0];
            Usuario::where('correo', '=', $user['correo'])->update(['verificacion' => '', 'activo' => 1]);
            $users = Usuario::where('verificacion', '=', $key);
            if ($users->count() === 0) {

                $this->log(json_encode(array('movimiento' => 'Verificacion de Cuenta',
                    'Detalle' => array('Id' => $user['id'], 'TCuenta' => $user['esempresa']),
                    'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));

                //id=1
                $usuario= Usuario::find($user['id']);
                $recompensa=new RecompensasController();
                $recompensa->recompensa($usuario, 1, 0);
                return view('validar.activacion');
            } else {
                abort(404);
            }
        }
    }

    public function wrestore($pin = 0) {

        if (request()->method() === 'GET') {
            $user = Usuario::where('clave', '=', $pin);
            if ($user->count() === 0) {
                abort(404);
            }
            return view('validar.recuperacion')->with(['mail' => $user->get()->toArray()[0]['correo'], 'pin' => $pin]);
        }
        if (request()->method() === 'POST') {
            $this->validate(request(), ['pinc' => 'numeric|digits:4|same:pin', 'passwdc' => 'same:passwd'], ['pinc.same' => 'El PIN no coincide', 'pinc.numeric' => 'El PIN debe ser Numerico', 'pinc.digits' => 'El PIN es de 4 dígitos,verifique en su correo', 'passwdc.same' => 'Las Contraseñas no coinciden']);
            $usuario = Usuario::find(request()->input('mail'));
            if (Usuario::where('correo', '=', $usuario->correo)->update(['passwd' => $this->encriptar($usuario->crypt, request()->input('passwd')), 'clave' => '']) != 0) {
                return view('validar.recuperacion_ok');
            } else {
                return view('validar.recuperacion_fail');
            }
        }
    }

    /* ----------------------------------------------------------------------------------------------------- */

    private function validar($header, $validacion) {

        for ($x = 0; $x < count($validacion); $x++) {
            if (array_key_exists('adicional', $validacion[$x])) {

                if ($validacion[$x]['adicional']) {
                    $lon1 = strlen($header[$validacion[$x]['encabezado']][0]);
                    $lon2 = $validacion[$x]['longitud'];
                    if ($lon1 <= $lon2) {
                        $mjs = array("message" => $validacion[$x]['mensaje'],
                            "status" => $this->notfoudStatus);
                        return $mjs;
                    }
                }
            } else {
                if (!array_key_exists($validacion[$x]['encabezado'], $header)) {
                    $mjs = array("message" => $validacion[$x]['mensaje'],
                        "status" => $this->notfoudStatus);
                    return $mjs;
                } else {

                    $lon1 = strlen($header[$validacion[$x]['encabezado']][0]);
                    $lon2 = $validacion[$x]['longitud'];
                    //echo $lon2;

                    if ($lon1 <= $lon2) {
                        $mjs = array("message" => $validacion[$x]['mensaje'],
                            "status" => $this->notfoudStatus);
                        return $mjs;
                    }
                }
            }
        }
        return array("status" => $this->successStatus);
    }

    private function encriptar($key, $pass) {
        $key = hash_hmac('sha256', '12345', $key);
        $val = rand(0, 32);
        $key = substr($key, $val, 32);
        $enc = new Encrypter($key, 'AES-256-CBC');
        $crypt = $enc->encryptString($pass);
        $crypt = json_encode(['key' => $key, 'crypt' => $crypt], JSON_UNESCAPED_SLASHES);
        $crypt = encrypt($crypt);
        return $crypt;
    }

    private function desencriptar($crypt) {
        $crypt = decrypt($crypt);
        $crypt = json_decode($crypt, true);

        $encrypt = new Encrypter($crypt['key'], 'AES-256-CBC');
        $passwd = $encrypt->decryptString($crypt['crypt']);
        return $passwd;
    }

    private function getdatos($id) {
        $usuario = Usuario::select('usuario.token', 'usuario.correo as email', 'detalleusuario.nombre', 'detalleusuario.apellidos', 'usuario.id as userid', 'detalleusuario.nombre as statususer', 'detalleusuario.nombre as userlogged', 'usuario.esempresa as tipo_cuenta', 'detalleusuario.perfil', 'saldos.saldo')
                        ->join('detalleusuario', 'usuario.id', '=', 'detalleusuario.idcuenta')
                        ->join('saldos', 'usuario.id', '=', 'saldos.idcliente')
                        ->where('usuario.id', '=', $id)
                        ->get()->toArray()[0];
        $usuario['nombre'] = $usuario['nombre'] . ' ' . $usuario['apellidos'];
        unset($usuario['apellidos']);
        $usuario['saldo'] = number_format($this->desencriptar($usuario['saldo']), 2);
        $usuario['perfil'] = request()->root() . '/api/image/getimage/' . $usuario['perfil'];
        $usuario['statususer'] = "1";
        $usuario['userlogged'] = 'Activo';
        $usuario['tipo_cuenta'] = \config('confkiintos')['cuenta'][$usuario['tipo_cuenta']];
        return $usuario;
    }

    private function getperfil($id) {
        $tcuenta = Usuario::select('esempresa')->where('id', '=', $id)->get()->toArray()[0];
        if ($tcuenta['esempresa'] === 0) {
            $dperfil = Usuario::select('usuario.correo', 'usuario.esempresa as tipo_cuenta', 'detalleusuario.nombre', 'detalleusuario.apellidos', 'detalleusuario.telefono', 'detalleusuario.perfil', 'detalleusuario.f_nacimiento as fnacimiento', 'detalleusuario.frase')
                            ->join('detalleusuario', 'usuario.id', '=', 'detalleusuario.idcuenta')
                            ->where('usuario.id', '=', $id)
                            ->get()->toArray()[0];
            $dperfil["fnacimiento"] = ($dperfil["fnacimiento"] === '0000-00-00') ? "" : date("d-m-Y", strtotime($dperfil["fnacimiento"]));
            $dperfil['perfil'] = request()->root() . '/api/image/getimage/' . $dperfil['perfil'];
            $dperfil['tipo_cuenta'] = \config('confkiintos')['cuenta'][$dperfil['tipo_cuenta']];
            return $dperfil;
        } else if ($tcuenta['esempresa'] === 1) {
            $dperfil = Usuario::select('usuario.id', 'usuario.correo', 'usuario.esempresa as tipo_cuenta', 'detalleusuario.nombre', 'detalleusuario.telefono', 'empresa.tellocal as telefonolocal', 'empresa.direccion', 'empresa.ubicacion', 'empresa.ubicacion', 'empresa.servicios', 'detalleusuario.perfil', 'detalleusuario.frase as web', 'empresa.horario_atencion as horarios')
                            ->join('detalleusuario', 'usuario.id', '=', 'detalleusuario.idcuenta')
                            ->join('empresa', 'usuario.id', '=', 'empresa.id_empresa')
                            ->where('usuario.id', '=', $id)
                            ->get()->toArray()[0];
            $redes = Catredes::select('catredes.id', 'catredes.nombre', 'redes.url')
                    ->leftjoin('redes', 'catredes.id', '=', 'redes.id_redes')
                    ->where('redes.id_empresa', '=', $id)
                    ->get()
                    ->toArray();
            $dperfil = array_merge($dperfil, ['redes' => $redes]);
            $dperfil['perfil'] = request()->root() . '/api/image/getimage/' . $dperfil['perfil'];
            $dperfil['tipo_cuenta'] = \config('confkiintos')['cuenta'][$dperfil['tipo_cuenta']];
            return $dperfil;
        }
    }

    private function validatoken() {
        $validar = array(
            array('encabezado' => 'token',
                'longitud' => 0,
                'mensaje' => 'ingrese su token'));
        $validacion = $this->validar(request()->header(), $validar);
        if ($validacion['status'] != $this->successStatus) {
            return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
        }
    }

    private function gethistorial($trancciones, $usuario) {
        $respuesta = array();
        foreach ($trancciones as $histo) {
            $perfil='';
            if($histo['id_c2']===0){
                $perfil=request()->root() . '/api/image/getimage/default.webp';
            }
            else{
                $perfil= request()->root() . '/api/image/getimage/'.Detalleusuario::where('idcuenta','=',$histo['id_c2'])->get()->toArray()[0]['perfil'];

            }
            if ($histo['entrada'] == 0) {
                if ($histo['tipot'] === 'P') {

                    array_push($respuesta, array('titulo' => 'Egreso', 'color' => \config('confkiintos')['colors']['NARANJA'], 'mensaje' => 'Egreso a ' . $histo['nombre'] . ' por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                }
                if ($histo['tipot'] === 'R' && $histo['status'] === 'R') {
                    array_push($respuesta, array('titulo' => 'Egreso', 'color' => \config('confkiintos')['colors']['ROJO'], 'mensaje' => 'Retiro por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                } else if ($histo['tipot'] === 'C' && $histo['status'] === 'R') {
                    array_push($respuesta, array('titulo' => 'Egreso', 'color' => \config('confkiintos')['colors']['NARANJA'], 'mensaje' => 'Egreso a ' . $histo['nombre'] . ' por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                } else if ($histo['tipot'] === 'C' && $histo['status'] === 'P') {

                    if (\App\Models\Notificacion::where('idnot', '=', $usuario->get()->toArray()[0]['id'])->where('fecha', '=', $histo['fecha'])->where('cantidad', '=', $this->desencriptar($histo['cantidad']))->count() > 0) {
                        array_push($respuesta, array('titulo' => 'Egreso Pendiente', 'color' => \config('confkiintos')['colors']['AMARILLO2'], 'mensaje' => 'Egreso pendiente a ' . $histo['nombre'] . ' por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha'],
                            'id' => \App\Models\Notificacion::where('idnot', '=', $usuario->get()->toArray()[0]['id'])->where('fecha', '=', $histo['fecha'])->where('cantidad', '=', $this->desencriptar($histo['cantidad']))->get()->toArray()[0]['id']));
                    }
                }
            } else if ($histo['entrada'] == 1) {
                if ($histo['tipot'] === 'P') {
                    if ($histo['status'] == 'P') {
                        array_push($respuesta, array('titulo' => 'Ingreso Pendiente', 'color' => \config('confkiintos')['colors']['AMARILLO'], 'mensaje' => 'Ingreso pendiente de ' . $histo['nombre'] . ' por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                    }
                    if ($histo['status'] == 'R') {
                        array_push($respuesta, array('titulo' => 'Ingreso', 'color' => \config('confkiintos')['colors']['VERDE'], 'mensaje' => 'Ingreso de ' . $histo['nombre'] . ' por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                    }
                }
                if ($histo['tipot'] === 'C') {
                    array_push($respuesta, array('titulo' => 'Ingreso', 'color' => \config('confkiintos')['colors']['VERDE'], 'mensaje' => 'Ingreso de ' . $histo['nombre'] . ' por $ ' . number_format($this->desencriptar($histo['cantidad']), 2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                }
                if ($histo['tipot'] === 'D') {
                    array_push($respuesta, array('titulo' => 'Ingreso', 'color' => \config('confkiintos')['colors']['AZUL'], 'mensaje' => 'Recarga por $ ' . number_format($this->desencriptar($histo['cantidad']),2),'descripcion'=>$histo['descripcion'],'perfil'=>$perfil, 'fecha' => $histo['fecha']));
                }
            }
        }
        return $respuesta;
    }

    private function gettiendas() {
        $Atiendas = Usuario::select('id')->where('esempresa', '=', '1')->get()->toArray();
        $tiendas = array();
        foreach ($Atiendas as $value) {
            array_push($tiendas, $this->getperfil($value['id']));
        }
        return $tiendas;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function generateRandomNumeric($length = 10) {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function log($mensaje) {

        $log = new \App\Models\Log();
        $log->detalle = $mensaje;
        $log->save();
    }

    /* ----------------------------------------------------------------------------------------------------- */

    public function validaradmin() {
        $this->validatoken();
        $usuario = Usuario::where("token", '=', request()->header('token'));
        if ($usuario->count() > 0) {
            $usuario = $usuario->get()->toArray()[0];
            $admin = \App\Models\admin::where('id', '=', '1')->get()->toArray()[0]['contexto'];
            $admin = decrypt($admin);
            if (strpos($admin, $usuario['correo']) === false) {
                return response()->json(['message' => 'Usted no es un Administrador', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
            } else {
                return response()->json(['message' => 'Administrador', 'status' => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
            }
        }
        return response()->json(['message' => 'Sus credenciales no se pudierón verificar', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
    }
    public function getalluser(){
        $Atiendas = Usuario::select('id')->where('esempresa', '<>', '2')->get()->toArray();
        $tiendas = array();
        foreach ($Atiendas as $value) {
            array_push($tiendas, $this->getperfil($value['id']));
        }
        return response()->json(["usuarios"=>$tiendas],$this->successStatus,[],JSON_UNESCAPED_SLASHES);
    }

    public function modificarperfil() {
        $this->validatoken();
        $usuario = Usuario::where("token", '=', request()->header('token'));
        if ($usuario->count() > 0) {
            $usuario = $usuario->get()->toArray()[0];
            $admin = \App\Models\admin::where('id', '=', '1')->get()->toArray()[0]['contexto'];
            $admin = decrypt($admin);
            if (strpos($admin, $usuario['correo']) === false) {
                return response()->json(['message' => 'Usted no es un Administrador', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
            } else {
                $validar = array(
                    array('encabezado' => 'token',
                        'longitud' => 0,
                        'mensaje' => "Sus credenciales no se pudierón verificar"),
                    array('encabezado' => 'nombre',
                        'longitud' => 0,
                        'mensaje' => "Ingrese su Nombre"),
                    array('encabezado' => 'tcuenta',
                        'longitud' => 0,
                        'mensaje' => "Falta tipo de cuenta"),
                    array('encabezado' => 'id',
                        'longitud' => 0,
                        'mensaje' => "Falta id del usuario"),
                );

                $validacion = $this->validar(request()->header(), $validar);
                if ($validacion['status'] != $this->successStatus) {
                    return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                }
                $usuario = Usuario::where('id', '=', request()->header('id'));
                if($usuario<=0){
                    return response()->json(['message' => 'Usuario no registrado', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                }
                if (request()->hasfile('perfil')) {
                    if (strrpos(request()->file('perfil')->getmimeType(), 'image') === false) {
                        return response()->json(['message' => 'Solo son permitidos imagenes', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                    }
                    Storage::deleteDirectory('profile/' . $usuario->get()->toArray()[0]['correo'] . '/perfil');
                    $perfil = basename(Storage::disk('local')->put('profile/' . $usuario->get()->toArray()[0]['correo'] . '/perfil', request()->file('perfil')));
                    if (Detalleusuario::where('idcuenta', '=', $usuario->get()->toArray()[0]['id'])->update(array('perfil' => $perfil)) != 0) {
                        //return response()->json(['message' => 'archivo subido con exito'], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
                    } else {
                        //return response()->json(['message' => 'archivo subido con exito', 'status' => $this->successStatus], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
                    }
                }

                if (request()->header('tcuenta') === 'personal') {
                    $validar = array(
                        array('encabezado' => 'apellidos',
                            'longitud' => 0,
                            'mensaje' => "Ingrese sus Apellidos"),
                        array('encabezado' => 'nacimiento',
                            'longitud' => 0,
                            'mensaje' => "Ingrese se Fecha de Nacimiento!!!"),
                    );
                    $validacion = $this->validar(request()->header(), $validar);
                    if ($validacion['status'] != $this->successStatus) {
                        return response()->json(['message' => $validacion['message'], 'status' => $validacion['status']], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                    }
                    if (Detalleusuario::where('idcuenta', '=', $usuario->get()->toArray()[0]['id'])->update(['nombre' => request()->header('nombre'), 'apellidos' => request()->header('apellidos'), 'f_nacimiento' => date("Y-m-d", strtotime(request()->header('nacimiento'))), 'frase' => request()->header('frase'), "telefono" => request()->header('telefono')]) != 0) {
                        $this->log(json_encode(array('Movimiento' => 'Actulaización Cuenta por un administrador',
                            "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                            'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
                        return response()->json(["message" => "Actualizacion Completada", "status" => 200], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
                    } else {
                        return response()->json(['message' => 'Informacón no actualizada,intente mas tarde', 'status' => $this->notfoudStatus], $this->notfoudStatus, [], JSON_UNESCAPED_SLASHES);
                    }
                } else if (request()->header('tcuenta') === 'negocio') {
                    $redesoc = json_decode(request()->header('redes'), true);
                    Detalleusuario::where('idcuenta', '=', $usuario->get()->toArray()[0]['id'])->update(['nombre' => request()->header('nombre'), "telefono" => request()->header('telefono')]);
                    \App\Models\Empresa::where('id_empresa', '=', $usuario->get()->toArray()[0]['id'])->update(['direccion' => request()->header('direccion'), 'servicios' => request()->header('servicios'), 'tellocal' => request()->header('tellocal'), 'url' => request()->header('url'), 'ubicacion' => '', 'horario_atencion' => request()->header('horarios')]);

                    $redes = Catredes::all();
                    foreach ($redes as $reds) {
                        \App\Models\Redes::where('id_empresa', '=', $usuario->get()->toArray()[0]['id'])->where('id_redes', '=', $reds->id)->update(['url' => $redesoc[$reds->nombre]]);
                    }
                    $this->log(json_encode(array('Movimiento' => 'Actulaización Cuenta por un administrador',
                        "Detalle" => array('Id' => $usuario->get()->toArray()[0]['id'], 'TCuenta' => $usuario->get()->toArray()[0]['esempresa']),
                        'Fecha' => date("Y-m-d H:i:s")), JSON_UNESCAPED_SLASHES));
                    return response()->json(["message" => "Actualizacion Completada", "status" => 200], $this->successStatus, [], JSON_UNESCAPED_SLASHES);
                }
            }
        }
    }
public function paso(){
    $datos = new \stdClass();
            $datos->from = \config('mail')['username'];
            $datos->view = 'mails.registro';
            $datos->text = 'mails.registro_plain';
            $datos->nombre = 'Oswaldo Neri Valdez';
            $datos->fromname = 'Kiintos';
            $datos->enlace = request()->root() . '/activar/1234';
            $datos->imagen = request()->root() . '/api/image/getimage/default.webp';
            $datos->subject = 'Registro';
            Mail::to('oswaldovaldez92@gmail.com')->send(new SoporteKiintos($datos));
    /*
    $fechaEmision = Carbon::parse($req->input('fecha_emision'));
$fechaExpiracion = Carbon::parse($req->input('fecha_expiracion'));

$diasDiferencia = $fechaExpiracion->diffInDays($fechaEmision);
           */
    /*$fechas=\Carbon::now();
    $fechaant= \Carbon::now(); //\Carbon::parse('2015-03-24');
    $dias=$fechas->diffInWeeks($fechaant);
    //dd($fechas->month);
   dd($fechas->day);
    //dd($fechas->toDateString());
//echo $this->desencriptar('eyJpdiI6IlhMK3NqZFZjdk5Gd0Y0VytsOExJZ3c9PSIsInZhbHVlIjoiaFIzcVIwUis2dWhiM3lYTXdxVytORXNHRzBSQ0tIRjRJUnp2K3lmQUlBNGZGTG16VnRxcUFybWp1eWtYWXZ4eWpSV2s2TFNzRndaODVJYUhmVlpDNkc1RG5WaWxvT1BSbUtWczNSUEhlXC9Wb0x5cHF5Rmt6Umh0RzNkeUhZRmVxTUdjQlRtSkNaUXJ3RlVDb2w1bnhoaUxMREgrNnkwSGYwcFRLZFVTaEx5OFdYZjNqMU5GTUdFTlg1YnNUd0ZLcnpLdkJSMjZ2MjJBMlJsWTN6dmwzU2RyNFpwdFRQTXV0SHlBUVhNTTJOU1E2bEhIVXJCTXlaZ2RmUUM5MVwvUmVMNXlSK1g5OW1DZnpVM3RIQ29QYTRyeFk0dUxJNnJpeU9ScVwvSjE4dENlcFZRRGd2OFBYQ1RnOEdTaGFqaGFja2IrajBcL1wvMmMwdm9WRDlmME44VFBHaEE9PSIsIm1hYyI6IjExMTUzMjAwZjljOWNhY2VlMjk2YzRiODRkMmYwODgxNDg0MjFkMzQ1ZmQwMzQ1Mzg1MjM5M2FjM2IzNTI1M2UifQ==');
    /*echo '<br>';
    echo $this->desencriptar('eyJpdiI6InVcL1VBZGpwTHF5K2VkMXMwUzhtSmNnPT0iLCJ2YWx1ZSI6IjlsZ09qZm9DWUw0YWh5d1BkaVV1dkZ4aE1CYjNhZDREUTRVMU8rTVRhXC9QRkw2XC9FSkhpY2x1N2hPcldSeXoxclhSc0ZhV2MxKzM4UVpqSDY3R1NZZmJzSzlpUTNZY0lsTmhscnJLWFppWGt6VkdxRGZBSWFrS1VudCsrbGEzMkthU2NOSU10ak85MEF4VHBsZFNodXNUT1M3TUlHSkV2ZFFMWGJVSWF0RXBRTCtYa0IzZVpzbTNNRDFKaE1Ib3E1RUhXVWJRYk1LNzlpV2lwbmdSQWlnaDdua3RpdVRNRFY0VGJWd1ZTK0ptYzgxREZld0lRYXp2U3JSMElzblpOVHJMSGZva2NQVHNaYU83ZkVMdkpNUWxxU1NuNVZ6eDlOZzlBekVjTTVuTFFnR1FBa2dsTjJcL1lkUytWeThZTDc1U2VWOWpnVjh4YUFkblhYTlRIUFA4dz09IiwibWFjIjoiYjU1MTk3N2MwZWM2YTBmZjlmZGUwOTI4ZWE1MjE0MzI0MTlkZmQxMzgxM2YyYWZmMTA2ZTM3YzYyNGM3ZTY2OCJ9');*/
/*$usuario = Usuario::select('usuario.correo')
                ->join('detalleusuario', 'detalleusuario.idcuenta', '=', 'usuario.id');
    dd($usuario);*/

}
}
