<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_empresa
 * @property string $direccion
 * @property string $horario_atencion
 * @property string $servicios
 * @property string $tellocal
 * @property string $url
 * @property string $ubicacion
 * @property Usuario $usuario
 * @property Rede[] $redes
 */
class Empresa extends Model
{
     /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'id_empresa';
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'empresa';

    /**
     * @var array
     */
    protected $fillable = ['id_empresa', 'direccion', 'horario_atencion', 'servicios', 'tellocal', 'url', 'ubicacion'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'id_empresa');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function redes()
    {
        return $this->hasMany('App\Rede', 'id_empresa', 'id_empresa');
    }
}
