<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('inventory_actions')) {
            return;
        }

        // bảng hành động kho
        Schema::create('inventory_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->enum('action_type', INVENTORY_ACTION_TYPES)->comment("Loại hành động kho: nhập kho, xuất kho, điều chỉnh kho");
            $table->foreignId('inventory_lot_id')->constrained('inventory_lots')->onDelete('cascade');
            $table->foreignId('inventory_batch_id')->nullable()->constrained('inventory_batches')->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('cascade')->comment("Đơn hàng liên quan nếu có");
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->onDelete('cascade')->comment("Mục đơn hàng liên quan nếu có");
            $table->integer('quantity_packages')->default(0)->comment("Số lượng bao gói thay đổi");
            $table->integer('quantity_base_units')->default(0)->comment("Số lượng đơn vị cơ sở thay đổi");
            $table->string('reason')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment("Người thực hiện hành động");
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
        Schema::dropIfExists('inventory_actions');
    }
}
