<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_cliente
 * @property boolean $entrada
 * @property string $descripcion
 * @property string $cantidad
 * @property string $status
 * @property int $id_c2
 * @property string $fecha
 * @property string $nombre
 * @property string $tipot
 * @property string $tkey
 * @property Usuario $usuario
 */
class Transaccione extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['id_cliente', 'entrada', 'descripcion', 'cantidad', 'status', 'id_c2', 'fecha', 'nombre', 'tipot', 'tkey'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'id_cliente');
    }
}
