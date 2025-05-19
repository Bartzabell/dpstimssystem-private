<?php

use App\Models\Category;
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
        Schema::create('inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('item_code')->nullable();
            // $table->decimal('item_qty', 10, 2)->default(0);
            $table->string('category')->nullable();
            $table->string('type')->nullable();
            $table->string('material')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('uom')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('min_stock', 10, 2)->nullable();
            $table->decimal('max_stock', 10, 2)->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('inventory_stocks');
    }
};
