<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Material extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Material | Settings Module');
    }

    protected $table = 'materials';

    protected $fillable = ['name', 'created_by'];

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
