<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $nombre
 */
class Catredes extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['nombre'];

}
