<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryLotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('inventory_lots')) {
            return;
        }

        // lô nhập kho
        Schema::create('inventory_lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->integer('quantity_packages')->default(0)->comment("Số lượng gói/hộp nhập");
            $table->dateTime("received_at")->comment("Thời điểm nhập kho");
            $table->dateTime("expired_at")->comment("Hạn sử dụng của lô hàng = received_at + shelf_life_closed_days");
            $table->string("supplier_note")->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_lots');
    }
}
