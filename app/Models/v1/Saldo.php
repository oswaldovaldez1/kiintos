<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $idcliente
 * @property string $saldo
 * @property Usuario $usuario
 */
class Saldo extends Model
{
    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'idcliente';
    /**
     * @var array
     */
    protected $fillable = ['idcliente', 'saldo'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'idcliente');
    }
}
