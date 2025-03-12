<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = ['name', 'created_by'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
