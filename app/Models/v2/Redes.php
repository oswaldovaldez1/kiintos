<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $id_empresa
 * @property int $id_redes
 * @property string $url
 * @property Empresa $empresa
 */
class Redes extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['id_empresa', 'id_redes', 'url'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo('App\Empresa', 'id_empresa', 'id_empresa');
    }
}
