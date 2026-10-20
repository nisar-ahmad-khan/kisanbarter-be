<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'image_path'];

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }
}
