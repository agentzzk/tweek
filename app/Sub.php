<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sub extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'avatar'
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'subs';
    protected $primaryKey = 'id';

    public function users() {
        return $this->belongsToMany('App\User');
    }
}
