<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionPurchaseBill extends Model
{
    protected $table = 'transaction_purchase_bills';

    protected $fillable = ['supplier_id', 'date_purchased', 'created_by', 'updated_by'];

    public function supplier(){
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function items(){
        return $this->hasMany(TransactionPurchaseItem::class, 'tpb_id', 'id');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(){
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }
}
