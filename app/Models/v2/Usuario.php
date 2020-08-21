<?php

namespace App\Models;



use Illuminate\Database\Eloquent\Model;

/**
 * @property string $correo
 * @property int $id
 * @property string $passwd
 * @property string $clave
 * @property boolean $esempresa
 * @property string $token
 * @property string $crypt
 * @property string $verificacion
 * @property boolean $activo
 * @property string $evento
 * @property string $registro
 * @property int $semanas
 * @property Detalleusuario[] $detalleusuarios
 * @property Empresa $empresa
 * @property Saldo[] $saldos
 * @property Transaccione[] $transacciones
 */
class Usuario extends Model
{
    
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'usuario';

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     * 
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['id', 'passwd', 'clave', 'esempresa', 'token', 'crypt', 'verificacion', 'activo'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalleusuarios()
    {
        return $this->hasMany('App\Detalleusuario', 'idcuenta');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function empresa()
    {
        return $this->hasOne('App\Empresa', 'id_empresa');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saldos()
    {
        return $this->hasMany('App\Saldo', 'idcliente');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transacciones()
    {
        return $this->hasMany('App\Transaccione', 'id_cliente');
    }
}
