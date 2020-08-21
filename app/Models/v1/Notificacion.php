<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $tipo
 * @property string $texto
 * @property string $status
 * @property string $titulo
 * @property string $fecha
 * @property float $cantidad
 * @property int $idnot
 */
class Notificacion extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'notificacion';

    /**
     * @var array
     */
    protected $fillable = ['tipo', 'texto', 'status', 'titulo', 'fecha', 'cantidad', 'idnot'];

}
