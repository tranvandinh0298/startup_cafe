<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('ingredients')) {
            return;
        }

        // bảng nguyên liệu gốc
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment("Tên nguyên liệu. vd: Đường, Muối, Bột ngọt...");
            $table->enum('base_unit', INGREDIENT_BASE_UNIT)->comment("Đơn vị cơ bản của nguyên liệu. vd: gram, ml, cái...");
            $table->integer('package_size')->default(0)->comment("Kích thước gói đóng gói. vd: 500 (gram), 1000 (ml)...");
            $table->integer("shelf_life_closed_days")->nullable()->comment("Hạn sử dụng khi chưa mở gói, tính theo ngày");
            $table->integer("shelf_life_opened_hours")->nullable()->comment("Hạn sử dụng khi đã mở gói, tính theo giờ");
            $table->integer('reorder_threshold')->default(0)->comment("Ngưỡng đặt hàng lại, theo số hộp");
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
        Schema::dropIfExists('ingredients');
    }
}
