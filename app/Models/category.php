<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class category extends Model
{
    protected $fillable =['name'];

    public function productsManyData()
    {
        return $this->hasMany('App\Models\product', 'category_id');
    }
}
