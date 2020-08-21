<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $detalle
 */
class Log extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'log';

    /**
     * @var array
     */
    protected $fillable = ['detalle'];

}
