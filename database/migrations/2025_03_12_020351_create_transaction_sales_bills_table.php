<?php

use App\Models\Customer;
use App\Models\Discount;
use App\Models\Warehouse;
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
        Schema::create('transaction_sales_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class, 'customer_id')->nullable();
            $table->foreignIdFor(Warehouse::class, 'warehouse_id')->nullable();
            $table->datetime('date_sold')->nullable();
            $table->foreignIdFor(Discount::class, 'discount_id')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('dispatch_status')->nullable();
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
        Schema::dropIfExists('transaction_sales_bills');
    }
};
