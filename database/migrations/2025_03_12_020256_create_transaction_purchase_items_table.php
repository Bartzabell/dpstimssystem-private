<?php

use App\Models\InventoryStock;
use App\Models\TransactionPurchaseBill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TransactionPurchaseBill::class, 'tpb_id')->nullable();
            $table->foreignIdFor(InventoryStock::class, 'stock_id')->nullable();
            $table->integer('item_qty')->default(0);
            $table->string('per_piece')->nullable();
            $table->integer('total_price')->nullable();
            $table->string('bill_no')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('NO ACTION');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_purchase_items');
    }
};
