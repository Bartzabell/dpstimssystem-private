<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Discount extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Discount | Settings Module');
    }

    protected $table = 'discounts';

    protected $fillable = ['name', 'type', 'amount', 'created_by'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
