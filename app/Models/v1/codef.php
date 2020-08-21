<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $codigo
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 */
class codef extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'codigosf';

    /**
     * @var array
     */
    protected $fillable = ['codigo', 'status', 'created_at', 'updated_at'];

}
