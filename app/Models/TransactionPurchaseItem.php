<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TransactionPurchaseItem extends Model
{
    use LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'description'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('Items | Purchase Module');
    }

    protected $table = 'transaction_purchase_items';

    protected $fillable = ['tpb_id', 'stock_id', 'item_qty', 'item_price',
    'created_by', 'updated_by'];

    public function bill(){
        return $this->belongsTo(TransactionPurchaseBill::class, 'tpb_id', 'id');
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
