<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Road extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roads';
    public $timestamps = false;

    public function area()
    {
        return $this->belongsTo('App\Area');
    }
}
