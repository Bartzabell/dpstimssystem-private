<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TransactionSalesItem extends Model
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

    protected $table = 'transaction_sales_items';

    protected $fillable = ['tsb_id', 'stock_id', 'item_qty', 'per_piece', 'total_price', 'bill_no',
    'created_by', 'updated_by'];

    public function bill(){
        return $this->belongsTo(TransactionSalesBill::class, 'tsb_id', 'id');
    }

    public function stock(){
        return $this->belongsTo(InventoryStock::class, 'stock_id', 'id');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
