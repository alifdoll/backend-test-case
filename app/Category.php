<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    public $timestamps = false;
    protected $fillable = ['id'];
    public function products()
    {
        return $this->hasMany('App\Product');
    }

    public static function productsSorted()
    {
        $cat = DB::table('categories')->select(DB::raw('categories.id, categories.name, count(products.id) as jum'))
            ->leftJoin('products', 'products.category_id', '=', 'categories.id')
            ->groupBy('categories.id')
            ->orderBy('jum', 'desc')->get();
        return $cat;
        // return $this->hasMany('App\Product')->orderBy('category_id');
    }
}
