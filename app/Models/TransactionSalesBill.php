<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TransactionSalesBill extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('your_model_logs');
    }

    protected $table = 'transaction_sales_bills';

    protected $fillable = ['customer_id', 'date_sold', 'total_price', 'created_by', 'updated_by'];

    public function customer(){
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function items(){
        return $this->hasMany(TransactionSalesItem::class, 'tsb_id', 'id');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
