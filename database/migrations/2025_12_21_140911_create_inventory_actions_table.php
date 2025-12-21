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

        Schema::create('inventory_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->enum('action_type', INVENTORY_ACTION_TYPES)->comment("Loại hành động kho: nhập kho, xuất kho, điều chỉnh kho");
            $table->foreignId('inventory_lot_id')->nullable()->constrained('inventory_lots')->onDelete('set null');
            $table->foreignId('ineventory_batch_id')->nullable()->constrained('inventory_batches')->onDelete('set null');
            $table->integer('quantity_packages')->default(0)->comment("Số lượng bao gói thay đổi");
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
