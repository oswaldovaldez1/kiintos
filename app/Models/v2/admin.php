<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $contexto
 */
class admin extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'administrador';

    /**
     * @var array
     */
    protected $fillable = ['contexto'];

}
