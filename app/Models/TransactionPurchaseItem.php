<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPurchaseItem extends Model
{
    protected $table = 'transaction_purchase_items';

    protected $fillable = ['tpb_id', 'stock_id', 'item_qty', 'per_piece', 'total_price', 'bill_no',
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
