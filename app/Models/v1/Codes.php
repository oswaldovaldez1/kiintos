<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codes
 * @property boolean $status
 * @property string $fecha
 */
class Codes extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'codigos';

    /**
     * @var array
     */
    protected $fillable = ['codes', 'status', 'fecha'];

}
