<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = ['name', 'phone_no', 'street', 'municipality', 'city', 'email', 'tin_no', 'created_by', 'updated_by'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
