<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $idcuenta
 * @property string $nombre
 * @property string $apellidos
 * @property string $telefono
 * @property string $f_nacimiento
 * @property boolean $activa
 * @property string $perfil
 * @property string $tipoperfil
 * @property string $ext
 * @property string $frase
 * @property string $institucion
 * @property Usuario $usuario
 */
class Detalleusuario extends Model
{
    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'idcuenta';
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'detalleusuario';

    /**
     * @var array
     */
    protected $fillable = ['idcuenta', 'nombre', 'apellidos', 'telefono', 'f_nacimiento', 'activa', 'perfil', 'tipoperfil', 'ext', 'frase', 'institucion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'idcuenta');
    }
}
