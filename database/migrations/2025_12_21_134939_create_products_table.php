<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('products')) {
            return;
        }

        // bảng sản phẩm
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment("Tên sản phẩm");
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('selling_price')->default(0)->comment("Giá bán");
            $table->enum('status', RECORD_STATUS)->default(RECORD_STATUS_DEFAULT)->comment("Trạng thái sản phẩm");
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
        Schema::dropIfExists('products');
    }
}
