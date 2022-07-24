<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $timestamps = false;
    protected $fillable = ['category_id', 'id'];
    public function assets()
    {
        return $this->hasMany('App\Asset');
    }
}
