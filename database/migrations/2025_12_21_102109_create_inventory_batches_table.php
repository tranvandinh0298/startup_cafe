<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('inventory_batches')) {
            return;
        }

        // Mỗi hộp/chai đã mở thuộc về lô nhập kho nào
        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->foreignId('inventory_lot_id')->constrained('inventory_lots')->onDelete('cascade');
            $table->integer('initial_quantity_base')->default(0)->comment("Số lượng đơn vị cơ sở ban đầu khi mở bao bì");
            $table->integer('remaining_quantity_base')->default(0)->comment("Số lượng đơn vị cơ sở còn lại trong bao bì");
            $table->dateTime('opened_at')->comment('Thời điểm mở bao bì');
            $table->dateTime('expired_at')->comment("Hạn sử dụng sau khi mở bao bì = shelf_life_opened_hours + opened_at");
            $table->enum("status", PACKAGE_STATUS)->comment("Trạng thái của lô hàng");
            $table->foreignId('opened_by')->nullable()->constrained('users')->onDelete('set null')->comment("Người mở bao bì");
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
        Schema::dropIfExists('inventory_batches');
    }
}
