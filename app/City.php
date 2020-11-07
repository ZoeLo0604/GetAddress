<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cities';
    public $timestamps = false;

    public function areas()
    {
        return $this->hasMany('App\Area');
    }
}
