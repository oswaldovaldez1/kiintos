<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $descripcion
 * @property string $mensaje
 * @property string $tevento
 * @property string $tipo
 * @property float $valor
 */
class Recompensas extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'eventos';

    /**
     * @var array
     */
    protected $fillable = ['descripcion', 'tevento', 'tipo', 'valor'];

}
